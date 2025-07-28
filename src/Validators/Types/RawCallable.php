<?php

/**
 * Holds logic to check if a given value is a valid callable
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Property;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

final class RawCallable implements BaseType
{
    /**
     * Validates that a given property value is valid integer
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid
     */
    public function validate(Property $property, Context $context): void
    {
        $value = $property->getValue();
        v::callableType()->assert($value);
        $property->setValue($value);
    }
}
