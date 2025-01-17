<?php

namespace Attributes\Validation\Validators;

use Attributes\Validation\Property;
use Exception;

class PropertyValidationResult implements ValidationResult
{
    private array $errors = [];

    private Property $property;

    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return ! empty($this->errors);
    }

    /**
     * Adds a validation error
     *
     * @param  Exception  $error  - The validation error
     */
    public function addError(Exception $error): void
    {
        $propertyName = $this->property->getName();
        if (! isset($this->errors[$propertyName])) {
            $this->errors[$propertyName] = [];
        }

        $this->errors[$propertyName][] = $error->getMessage();
    }
}
