<?php

/**
 * Holds interface for validating data into base models
 */

declare(strict_types=1);

namespace Attributes\Validation;

use ArrayObject;
use Attributes\Validation\Exceptions\ValidationException;

interface Validatable
{
    /**
     * @param  array|ArrayObject  $data  - Data to be validated
     * @param  string|object  $model  - Model to validate against
     *
     * @throws ValidationException - If the validation fails
     */
    public function validate(array|ArrayObject $data, string|object $model): object;

    /**
     * @param  array|ArrayObject  $data  - Data to be validated
     * @param  callable  $call  - Callable to be validated
     *
     * @returns array - Arguments in a sequence order for the given function
     *
     * @throws ValidationException - If the validation fails
     */
    public function validateCallable(array|ArrayObject $data, callable $call): array;
}
