<?php

namespace Attributes\Validation\Tests\Models\Basic;

class FloatPoint
{
    public float $value;
}

class OptionalFloat
{
    public ?float $value;
}

class DefaultFloat
{
    public float $value = 10.5;
}

class DefaultOptionalFloat
{
    public ?float $value = null;
}
