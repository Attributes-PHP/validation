<?php

namespace Attributes\Validation;

use ReflectionProperty;

class Property
{
    private ReflectionProperty $property;

    private mixed $value;

    public function __construct(ReflectionProperty $property, mixed $value)
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

    public function getReflection(): ReflectionProperty
    {
        return $this->property;
    }
}
