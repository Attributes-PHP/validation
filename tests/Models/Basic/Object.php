<?php

namespace Attributes\Validation\Tests\Models\Basic;

class Obj
{
    public object $value;
}

class OptionalObj
{
    public ?object $value;
}

class DefaultObj
{
    public object $value;

    public function __construct()
    {
        $this->value = (object) [12345];
    }
}

class DefaultOptionalObj
{
    public ?object $value = null;
}
