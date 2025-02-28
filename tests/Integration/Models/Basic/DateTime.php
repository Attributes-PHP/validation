<?php

namespace Attributes\Validation\Tests\Integration\Models\Basic;

use DateTime as BaseDateTime;

class DateTime
{
    public BaseDateTime $value;
}

class OptionalDateTime
{
    public ?BaseDateTime $value;
}

class DefaultDateTime
{
    public BaseDateTime $value;

    public function __construct()
    {
        $this->value = new BaseDateTime('2025-01-01 00:00:00');
    }
}

class DefaultOptionalDateTime
{
    public ?BaseDateTime $value = null;
}
