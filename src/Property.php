<?php

namespace Attributes\Validation;

use ReflectionProperty;

class Property
{
    private ReflectionProperty $property;

    private mixed $value;

    private array $typeHintSuggestions = [];

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

    public function addTypeHintSuggestions(array $suggestions): void
    {
        $this->typeHintSuggestions = array_merge($this->typeHintSuggestions, $suggestions);
    }

    public function getTypeHintSuggestions(): array
    {
        return $this->typeHintSuggestions;
    }

    public function getFirstTypeHintSuggestion(): ?string
    {
        return $this->typeHintSuggestions[0] ?? null;
    }
}
