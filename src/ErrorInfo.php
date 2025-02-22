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

use Attributes\Validation\Exceptions\BaseException;

class ErrorInfo
{
    private ?string $propertyName;

    private array $errors = [];

    public function __construct(?string $propertyName = null)
    {
        $this->propertyName = $propertyName;
    }

    public function getErrors(): array
    {
        if (is_null($this->propertyName)) {
            return $this->errors;
        }

        return [$this->propertyName => $this->errors];
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Adds a validation error
     *
     * @param  BaseException  $error  - The validation error
     * @param  ?string  $propertyName  - The property in question
     */
    public function addError(BaseException $error, ?string $propertyName = null): void
    {
        $info = $error->getInfo();
        if (is_null($info) || ! $info->hasErrors()) {
            $this->addErrorMessage($error->getMessage(), $propertyName);

            return;
        }

        if (is_null($propertyName)) {
            $this->errors = array_merge($info->getErrors(), $this->errors);

            return;
        }

        $this->errors[$propertyName] = array_merge($info->getErrors(), $this->errors[$propertyName] ?? []);
    }

    /**
     * Adds a validation error message
     *
     * @param  string  $error  - The validation error
     * @param  ?string  $propertyName  - The property in question
     */
    public function addErrorMessage(string $error, ?string $propertyName = null): void
    {
        if (is_null($propertyName)) {
            $this->errors[] = $error;

            return;
        }

        if (! isset($this->errors[$propertyName])) {
            $this->errors[$propertyName] = [];
        }

        $this->errors[$propertyName][] = $error;
    }
}
