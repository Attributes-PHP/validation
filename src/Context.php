<?php

declare(strict_types=1);

namespace Attributes\Validation;

use Attributes\Validation\Exceptions\ContextPropertyException;

class Context
{
    public array $global = [];

    public array $local = [];

    public function setGlobal(string $propertyName, mixed $value, bool $override = false): void
    {
        if (! $override && $this->hasGlobal($propertyName)) {
            return;
        }

        $this->global[$propertyName] = $value;
    }

    /**
     * @throws ContextPropertyException
     */
    public function getGlobal(string $propertyName): mixed
    {
        if (! $this->hasGlobal($propertyName)) {
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
    public function getOptionalGlobal(string $propertyName, mixed $defaultValue = null): mixed
    {
        if ($this->hasGlobal($propertyName)) {
            return $this->getGlobal($propertyName);
        }

        return $defaultValue;
    }

    public function hasGlobal(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->global);
    }

    public function setLocal(string $propertyName, mixed $value, bool $override = false): void
    {
        if (! $override && $this->hasGlobal($propertyName)) {
            return;
        }

        $this->local[$propertyName] = $value;
    }

    /**
     * @throws ContextPropertyException
     */
    public function getLocal(string $propertyName): mixed
    {
        if (! $this->hasLocal($propertyName)) {
            throw new ContextPropertyException('Property '.$propertyName.' does not exist.');
        }

        $value = $this->local[$propertyName];
        if (class_exists($propertyName) && ! ($value instanceof $propertyName)) {
            throw new ContextPropertyException('Invalid property type: '.$propertyName);
        }

        return $value;
    }

    /**
     * @throws ContextPropertyException
     */
    public function getOptionalLocal(string $propertyName, mixed $defaultValue = null): mixed
    {
        if ($this->hasLocal($propertyName)) {
            return $this->getLocal($propertyName);
        }

        return $defaultValue;
    }

    public function hasLocal(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->local);
    }

    public function resetLocal(): void
    {
        $this->local = [];
    }

    public function getAll(): array
    {
        return [
            'locals' => $this->local,
            'globals' => $this->global,
        ];
    }
}
