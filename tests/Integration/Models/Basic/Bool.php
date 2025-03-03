<?php

namespace Attributes\Validation\Tests\Integration\Models\Basic;

class Boolean
{
    public bool $value;
}

class OptionalBool
{
    public ?bool $value;
}

class DefaultBool
{
    public bool $value = false;
}

class DefaultOptionalBool
{
    public ?bool $value = null;
}
