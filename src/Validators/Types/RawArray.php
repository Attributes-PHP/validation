<?php

/**
 * Holds logic to validate that a given value is an array
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Property;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

final class RawArray implements BaseType
{
    /**
     * Validates that a given property value is valid array
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid array
     */
    public function validate(Property $property, Context $context): void
    {
        if ($context->getGlobal('option.strict')) {
            v::arrayType()->assert($property->getValue());

            return;
        }

        $value = $property->getValue();
        v::arrayVal()->assert($value);
        $property->setValue((array) $value);
    }
}
