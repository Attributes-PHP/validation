<?php

namespace Attributes\Validation\Tests\Integration\Models\Basic;

enum RawEnum
{
    case ADMIN;
    case GUEST;
}

class Enum
{
    public RawEnum $value;
}

class OptionalEnum
{
    public ?RawEnum $value;
}

class DefaultEnum
{
    public RawEnum $value = RawEnum::GUEST;
}

class DefaultOptionalEnum
{
    public ?RawEnum $value = null;
}

enum RawIntEnum: int
{
    case ADMIN = 0;
    case GUEST = 1;
}

class IntEnum
{
    public RawIntEnum $value;
}

class OptionalIntEnum
{
    public ?RawIntEnum $value;
}

class DefaultIntEnum
{
    public RawIntEnum $value = RawIntEnum::GUEST;
}

class DefaultOptionalIntEnum
{
    public ?RawIntEnum $value = null;
}

enum RawStrEnum: string
{
    case ADMIN = 'admin';
    case GUEST = 'guest';
}

class StrEnum
{
    public RawStrEnum $value;
}

class OptionalStrEnum
{
    public ?RawStrEnum $value;
}

class DefaultStrEnum
{
    public RawStrEnum $value = RawStrEnum::GUEST;
}

class DefaultOptionalStrEnum
{
    public ?RawStrEnum $value = null;
}
