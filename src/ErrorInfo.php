<?php

declare(strict_types=1);

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

use Attributes\Validation\Exceptions\StopValidationException;
use Attributes\Validation\Exceptions\ValidationException;
use Exception;
use Respect\Validation\Exceptions\NestedValidationException as RespectNestedValidationException;

class ErrorInfo
{
    private Context $context;

    private array $errors = [];

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

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
     * @param  Exception|string  $error  - The validation error
     *
     * @throws ValidationException - When option.stopFirstError is true
     * @throws Exceptions\ContextPropertyException
     */
    public function addError(Exception|string $error): void
    {
        $propertyPath = $this->context->getOptional('internal.currentProperty', []);
        $errors = &$this->errors;
        foreach ($propertyPath as $property) {
            if (! isset($errors[$property])) {
                $errors[$property] = [];
            }

            $errors = &$errors[$property];
        }
        if ($error instanceof RespectNestedValidationException) {
            $errors += array_values($error->getMessages());
        } else {
            $errors[] = is_string($error) ? $error : $error->getMessage();
        }

        if ($this->context->get('option.stopFirstError')) {
            if (! is_string($error)) {
                throw new StopValidationException('Invalid data', $this, previous: $error);
            }
            throw new StopValidationException('Invalid data', $this);
        }
    }
}
