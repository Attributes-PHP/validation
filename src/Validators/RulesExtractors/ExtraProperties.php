<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Exceptions\ValidationException;

class ExtraProperties implements PropertiesContainer
{
    public array $properties = [];

    public function setProperty(string $propertyName, mixed $value): void
    {
        $this->properties[$propertyName] = $value;
    }

    /**
     * @throws ValidationException
     */
    public function getProperty(string $propertyName): mixed
    {
        return $this->properties[$propertyName] ?? throw new ValidationException('Property '.$propertyName.' does not exist.');
    }

    /**
     * @throws ValidationException
     */
    public function getOptionalProperty(string $propertyName, mixed $defaultValue = null): mixed
    {
        if ($this->hasProperty($propertyName)) {
            return $this->getProperty($propertyName);
        }

        return $defaultValue;
    }

    public function hasProperty(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->properties);
    }
}
