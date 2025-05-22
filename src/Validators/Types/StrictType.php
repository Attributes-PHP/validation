<?php

/**
 * Holds logic to check if a given value is from a given instance type-hint
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

final class StrictType implements BaseType
{
    /**
     * Validates that a given property value is from a given instance type
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid
     * @throws ContextPropertyException
     */
    public function validate(Property $property, Context $context): void
    {
        $propertyTypeHint = $context->getLocal('property.typeHint');
        v::instance($propertyTypeHint)->assert($property->getValue());
    }
}
