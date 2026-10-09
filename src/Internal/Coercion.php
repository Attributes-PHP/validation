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
            if ($mask & Masks::FLOAT) {
                return $value;
            }

            if (($mask & Masks::INT) && self::isIntegralFloat($value)) {
                return (int) $value;
            }

            if ($mask & Masks::STRING) {
                return (string) $value;
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

            $number = self::parseNumeric($value);

            if (($mask & Masks::INT) && $number !== null && $number['isIntegral']) {
                return (int) $number['value'];
            }

            if (($mask & Masks::FLOAT) && $number !== null) {
                return (float) $number['value'];
            }

            if ($mask & Masks::STRING) {
                return $value;
            }

            return null;
        }

        return null;
    }

    /**
     * Whether a float holds an integral value within the integer range.
     */
    private static function isIntegralFloat(float $value): bool
    {
        if (is_infinite($value) || is_nan($value)) {
            return false;
        }

        if ($value > PHP_INT_MAX || $value < PHP_INT_MIN) {
            return false;
        }

        return (float) (int) $value === $value;
    }

    /**
     * Parses a numeric string, reporting whether the number is integral.
     */
    private static function parseNumeric(string $value): ?array
    {
        // is_numeric accepts surrounding whitespace, like the engine
        if (! is_numeric($value)) {
            return null;
        }

        $float = (float) $value;

        return [
            'value' => $float,
            'isIntegral' => self::isIntegralFloat($float),
        ];
    }
}
