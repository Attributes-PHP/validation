<?php

declare(strict_types=1);

namespace Attributes\Validation;

use Attributes\Validation\Exceptions\ContextPropertyException;

class Context
{
    public array $global = [];
    
    private array $stacks = [];

    public function set(string $propertyName, mixed $value, bool $override = false): void
    {
        if (! $override && $this->has($propertyName)) {
            return;
        }

        $this->global[$propertyName] = $value;
    }

    public function get(string $propertyName): mixed
    {
        if (! $this->has($propertyName)) {
            throw new ContextPropertyException('Property '.$propertyName.' does not exist.');
        }

        $value = $this->global[$propertyName];
        if (class_exists($propertyName) && ! ($value instanceof $propertyName)) {
            throw new ContextPropertyException('Invalid property type: '.$propertyName);
        }

        return $value;
    }

    public function getOptional(string $propertyName, mixed $defaultValue = null): mixed
    {
        return $this->global[$propertyName] ?? $defaultValue;
    }

    public function has(string $propertyName): bool
    {
        return isset($this->global[$propertyName]);
    }

    public function push(string $propertyName, mixed $value): void
    {
        if (!isset($this->stacks[$propertyName])) {
            $this->stacks[$propertyName] = [];
        }

        $this->stacks[$propertyName][] = $value;
    }

    public function pop(string $propertyName): mixed
    {
        if (empty($this->stacks[$propertyName])) {
            return null;
        }

        return array_pop($this->stacks[$propertyName]);
    }

    public function getAll(): array
    {
        return $this->global;
    }

    public function getStack(string $propertyName): array
    {
        return $this->stacks[$propertyName] ?? [];
    }

    public function hasStack(string $propertyName): bool
    {
        return !empty($this->stacks[$propertyName]);
    }
}
