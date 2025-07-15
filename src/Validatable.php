<?php

/**
 * Holds interface for validating data into base models
 */

declare(strict_types=1);

namespace Attributes\Validation;

use ArrayAccess;
use Attributes\Validation\Exceptions\ValidationException;

interface Validatable
{
    /**
     * @param  array  $data  - Data to be validated
     * @param  object  $model  - Model to validate against
     *
     * @throws ValidationException - If the validation fails
     */
    public function validate(array $data, object $model): object;

    /**
     * @param  array|ArrayAccess  $data  - Data to be validated
     * @param  callable  $call  - Callable to be validated
     *
     * @returns array - Arguments in a sequence order for the given function
     *
     * @throws ValidationException - If the validation fails
     */
    public function validateCallable(array|ArrayAccess $data, callable $call): array;
}
