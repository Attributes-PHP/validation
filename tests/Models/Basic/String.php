<?php

namespace Attributes\Validation\Tests\Models\Basic;

class Str
{
    public string $value;
}

class OptionalStr
{
    public ?string $value;
}

class DefaultStr
{
    public string $value = 'My value';
}

class DefaultOptionalStr
{
    public ?string $value = null;
}
