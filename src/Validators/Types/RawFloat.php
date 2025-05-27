<?php

/**
 * Holds logic to check if a given value is a valid float
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

final class RawFloat implements BaseType
{
    /**
     * Validates that a given property value is valid float
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid
     * @throws ContextPropertyException
     */
    public function validate(Property $property, Context $context): void
    {
        if ($context->get('option.strict')) {
            v::floatType()->finite()->assert($property->getValue());

            return;
        }

        $value = $property->getValue();
        v::finite()->floatVal()->not(v::boolType())->assert($value);
        $property->setValue($value + 0);
    }
}
