<?php

/**
 * Holds logic for nested error logic
 *
 * ```php
 * class Profile {
 *     private string $name;
 *     private int $age;
 * }
 * class User {
 *     private Profile $profile;
 * }
 *
 * // Output of getErrors()
 * [
 *      'profile' => [
 *          'age' => 'Invalid age',
 *      ]
 * ]
 * ```
 */

namespace Attributes\Validation;

use Exception;

class ErrorInfo
{
    private array $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Adds a validation error
     *
     * @param  Exception  $error  - The validation error
     * @param  string  $propertyName  - The property in question
     */
    public function addError(Exception $error, string $propertyName): void
    {
        $this->addErrorMessage($error->getMessage(), $propertyName);
    }

    /**
     * Adds a validation error message
     *
     * @param  string  $error  - The validation error
     * @param  string  $propertyName  - The property in question
     */
    public function addErrorMessage(string $error, string $propertyName): void
    {
        if (! isset($this->errors[$propertyName])) {
            $this->errors[$propertyName] = [];
        }

        $this->errors[$propertyName][] = $error;
    }
}
