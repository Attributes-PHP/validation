<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

/**
 * The engine MAY_BE_* type masks: the Type enum case values are these
 * masks, so arms built from attribute arguments compare against them
 * without a translation table.
 *
 * @internal
 */
final class Masks
{
    public const NULL = 2;

    public const FALSE = 4;

    public const TRUE = 8;

    public const BOOL = 12;

    public const INT = 16;

    public const FLOAT = 32;

    public const STRING = 64;

    public const ARRAY = 128;

    public const OBJECT = 256;

    public const DICT = 268435456;

    public const ALL_BITS = 268435964;

    /**
     * The basic type names in mask order, used for "Must be" messages.
     */
    public const BASIC_NAMES = [
        [self::BOOL, 'boolean'],
        [self::INT, 'integer'],
        [self::FLOAT, 'float'],
        [self::STRING, 'string'],
        [self::ARRAY, 'array'],
        [self::OBJECT, 'object'],
        [self::NULL, 'null'],
    ];

    /**
     * The engine type mask of a runtime value.
     */
    public static function of(mixed $value): int
    {
        return match (true) {
            $value === null => self::NULL,
            $value === false => self::FALSE,
            $value === true => self::TRUE,
            is_int($value) => self::INT,
            is_float($value) => self::FLOAT,
            is_string($value) => self::STRING,
            is_array($value) => self::ARRAY,
            is_object($value) => self::OBJECT,
            default => 0,
        };
    }

    /**
     * The mask of a builtin type-hint name.
     */
    public static function forName(string $name): int
    {
        return match ($name) {
            'bool' => self::BOOL,
            'int' => self::INT,
            'float' => self::FLOAT,
            'string' => self::STRING,
            'array' => self::ARRAY,
            'object' => self::OBJECT,
            'null' => self::NULL,
            'false' => self::FALSE,
            'true' => self::TRUE,
            default => 0,
        };
    }
}
