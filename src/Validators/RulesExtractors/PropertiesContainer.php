<?php

namespace Attributes\Validation\Validators\RulesExtractors;

interface PropertiesContainer
{
    /**
     * @param  string  $propertyName  - The property name, used to refer to this property
     * @param  mixed  $value  - The value of that given property
     */
    public function setProperty(string $propertyName, mixed $value): void;

    /**
     * Retrieves a given property value
     *
     * @param  string  $propertyName  - The property to be retrieved
     * @return mixed - The property value
     */
    public function getProperty(string $propertyName): mixed;

    /**
     * Retrieves a given property value or the specified default value
     *
     * @param  string  $propertyName  - The property to be retrieved
     * @param  mixed|null  $defaultValue  - The default value to return if not set
     */
    public function getOptionalProperty(string $propertyName, mixed $defaultValue = null): mixed;

    /**
     * Checks if a given property exists
     */
    public function hasProperty(string $propertyName): bool;
}
