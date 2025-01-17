<?php

namespace Attributes\Validation\Config;

use Attributes\Validation\Config\Options\From;

class Config
{
    private array $options;

    public function __construct(From $from = From::JSON, ...$additionalOptions) {}
}
