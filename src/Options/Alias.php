<?php

declare(strict_types=1);

namespace Attributes\Validation\Options;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Alias
{
    private string $alias;

    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }
}
