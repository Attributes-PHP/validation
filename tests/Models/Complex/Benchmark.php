<?php

namespace Attributes\Validation\Tests\Models\Complex;

class BasicUnion
{
    public bool|int $boolInt;

    public int|float $intFloat;

    public float|array $floatArray;

    public object|int $objectInt;
}

class MultipleBasic
{
    public bool $bool;

    public int $int;

    public float $float;

    public array $array;

    public object $object;
}
