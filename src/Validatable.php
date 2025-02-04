<?php

/**
 * Holds interface for validating data into base models
 */

namespace Attributes\Validation;

use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Exceptions\ValidationException;

interface Validatable
{
    /**
     * @param  array  $data  - Data to be validated
     * @param  object  $model  - Model to validate against
     *
     * @throws ValidationException - If the validation fails
     * @throws TransformException - if the transformation of the data fails
     */
    public function validate(array $data, object $model): object;
}
