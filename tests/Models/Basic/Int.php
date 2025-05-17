<?php

namespace Attributes\Validation\Tests\Models\Basic;

class Integer
{
    public int $value;
}

class OptionalInt
{
    public ?int $value;
}

class DefaultInt
{
    public int $value = 10;
}

class DefaultOptionalInt
{
    public ?int $value = null;
}
