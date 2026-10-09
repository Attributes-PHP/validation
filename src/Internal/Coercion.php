<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

/**
 * Loose-mode scalar coercion, mirroring the engine's weak type rules.
 *
 * @internal
 */
final class Coercion
{
    /**
     * Coerces a value to bool using the engine's boolean conversion rules.
     * Returns null when the value cannot be coerced.
     */
    public static function coerceBool(mixed $value): ?bool
    {
        if (is_int($value)) {
            if ($value === 0) {
                return false;
            }

            if ($value === 1) {
                return true;
            }

            return null;
        }

        if (is_float($value)) {
            if (abs($value - 1.0) <= PHP_FLOAT_EPSILON) {
                return true;
            }

            if (abs($value) <= PHP_FLOAT_EPSILON) {
                return false;
            }

            return null;
        }

        if (is_string($value)) {
            return match (strlen($value)) {
                1 => match (strtolower($value[0])) {
                    '1', 't', 'y' => true,
                    '0', 'f', 'n' => false,
                    default => null,
                },
                2 => strtolower($value) === 'on' ? true : (strtolower($value) === 'no' ? false : null),
                3 => strtolower($value) === 'yes' ? true : (strtolower($value) === 'off' ? false : null),
                4 => strtolower($value) === 'true' ? true : null,
                5 => strtolower($value) === 'false' ? false : null,
                default => null,
            };
        }

        return null;
    }

    /**
     * Verifies a value against a basic type mask and coerces it to the
     * first acceptable type, mirroring zend_verify_scalar_type_hint().
     *
     * Returns the coerced value, or null when the value cannot satisfy the
     * mask. Integer is preferred over float, float over string.
     */
    public static function verifyScalar(mixed $value, int $mask, bool $strict): mixed
    {
        if ($strict) {
            if (is_int($value)) {
                if ($mask & Masks::INT) {
                    return $value;
                }

                if ($mask & Masks::FLOAT) {
                    return (float) $value;
                }

                return null;
            }

            return (Masks::of($value) & $mask) !== 0 ? $value : null;
        }

        if (is_int($value)) {
            if ($mask & Masks::INT) {
                return $value;
            }

            if ($mask & Masks::FLOAT) {
                return (float) $value;
            }

            if ($mask & Masks::STRING) {
                return (string) $value;
            }

            return null;
        }

        if (is_float($value)) {
            if (($mask & Masks::INT) && self::inIntRange($value)) {
                return Weak::int($value);
            }

            if ($mask & Masks::FLOAT) {
                return $value;
            }

            if ($mask & Masks::STRING) {
                return Weak::string($value);
            }

            return null;
        }

        if (is_bool($value)) {
            if ($mask & Masks::INT) {
                return (int) $value;
            }

            if ($mask & Masks::FLOAT) {
                return (float) $value;
            }

            if ($mask & Masks::STRING) {
                return $value ? '1' : '';
            }

            return null;
        }

        if (is_string($value)) {
            // Integer round-trip fast path when int is the only scalar
            // target: a matching string parses to the same integer the
            // slow path would produce
            if ($mask === Masks::INT && $value === (string) (int) $value) {
                return (int) $value;
            }

            if (! is_numeric($value)) {
                return ($mask & Masks::STRING) !== 0 ? $value : null;
            }

            $float = (float) $value;

            if (($mask & Masks::INT) && self::isIntegralFloat($float) && self::inIntRange($float)) {
                return (int) $float;
            }

            if ($mask & Masks::FLOAT) {
                return Weak::float($value);
            }

            // A float-string still converts to int, with the engine's
            // precision-loss deprecation
            if (($mask & Masks::INT) && self::inIntRange($float)) {
                return Weak::int($value);
            }

            return ($mask & Masks::STRING) !== 0 ? $value : null;
        }

        return null;
    }

    /**
     * Whether a float holds an integral value.
     */
    private static function isIntegralFloat(float $value): bool
    {
        return ! is_infinite($value) && ! is_nan($value) && (float) (int) $value === $value;
    }

    /**
     * Whether a float falls inside the range the engine converts to
     * int: [-2^63, 2^63). NAN, INF and out-of-range values are rejected.
     */
    private static function inIntRange(float $value): bool
    {
        return $value >= -9.223372036854776E+18 && $value < 9.223372036854776E+18;
    }
}
