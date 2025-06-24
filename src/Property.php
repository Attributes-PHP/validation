<?php

declare(strict_types=1);

namespace Attributes\Validation;

use ReflectionParameter;
use ReflectionProperty;

class Property
{
    private ReflectionProperty|ReflectionParameter $property;

    private mixed $value;

    public function __construct(ReflectionProperty|ReflectionParameter $property, mixed $value)
    {
        $this->property = $property;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->property->getName();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getReflection(): ReflectionProperty|ReflectionParameter
    {
        return $this->property;
    }
}
