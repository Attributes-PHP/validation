<?php

declare(strict_types=1);

namespace Attributes\Validation;

use Attributes\Validation\Exceptions\ContextPropertyException;

class Context
{
    public array $global = [];

    public function set(string $propertyName, mixed $value, bool $override = false): void
    {
        if (! $override && $this->has($propertyName)) {
            return;
        }

        $this->global[$propertyName] = $value;
    }

    /**
     * @throws ContextPropertyException
     */
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

    /**
     * @throws ContextPropertyException
     */
    public function getOptional(string $propertyName, mixed $defaultValue = null): mixed
    {
        if ($this->has($propertyName)) {
            return $this->get($propertyName);
        }

        return $defaultValue;
    }

    public function has(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->global);
    }

    public function push(string $propertyName, mixed $value): void
    {
        if (! $this->has($propertyName)) {
            $this->global[$propertyName] = [];
        }

        $this->global[$propertyName][] = $value;
    }

    public function pop(string $propertyName): mixed
    {
        if (! $this->has($propertyName)) {
            return null;
        }

        return array_pop($this->global[$propertyName]);
    }

    public function getAll(): array
    {
        return $this->global;
    }
}
