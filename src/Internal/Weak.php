<?php

/*
 * This file must not declare strict_types: the engine performs the weak
 * scalar conversions at these return-type boundaries, reproducing the
 * ext's zend_verify_scalar_type_hint conversions exactly, including the
 * "Implicit conversion from float ... to int loses precision"
 * deprecations for fractional values.
 */

namespace Attributes\Validation\Internal;

/**
 * Weak scalar conversion helpers.
 *
 * @internal
 */
final class Weak
{
    public static function int(mixed $value): int
    {
        return $value;
    }

    public static function float(mixed $value): float
    {
        return $value;
    }

    public static function string(mixed $value): string
    {
        return $value;
    }
}
