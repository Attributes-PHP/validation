<?php

namespace Attributes\Validation\Tests\Models\Basic;

use Attributes\Validation\Cache\Model;

class Arr implements Model
{
    public array $value;
}

class OptionalArr
{
    public ?array $value;
}

class DefaultArr
{
    public array $value = [12345];
}

class DefaultOptionalArr
{
    public ?array $value = null;
}
