<?php

/**
 * Holds logic to validate a boolean value
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

final class RawBool implements BaseType
{
    /**
     * Validates that a given property value is valid boolean
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid
     * @throws ContextPropertyException
     */
    public function validate(Property $property, Context $context): void
    {
        if ($context->getGlobal('option.strict')) {
            v::boolType()->assert($property->getValue());

            return;
        }

        $value = $property->getValue();
        v::boolVal()->notOptional()->assert($value);
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $property->setValue($value);
    }
}
