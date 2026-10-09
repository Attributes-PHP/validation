<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Stringable;

/**
 * Value inspection helpers shared by the field attributes and the validators.
 *
 * @internal
 */
final class Value
{
    /**
     * The type name of a value as the engine renders it in error messages.
     */
    public static function typeName(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return 'bool';
        }

        if (is_int($value)) {
            return 'int';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_string($value)) {
            return 'string';
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_object($value)) {
            return $value::class;
        }

        return 'mixed';
    }

    /**
     * Whether a string names a loadable class, interface or enum.
     */
    public static function isResolvableClassName(string $name): bool
    {
        return class_exists($name) || interface_exists($name) || enum_exists($name);
    }

    /**
     * Renders a value for inclusion in an error message template (the
     * {value} placeholder): strings are quoted, arrays flatten to "array",
     * objects use __toString() when available, else the class name.
     */
    public static function toString(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return "'".$value."'";
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_object($value)) {
            if ($value instanceof Stringable) {
                return "'".$value->__toString()."'";
            }

            return $value::class;
        }

        return 'unknown';
    }
}
