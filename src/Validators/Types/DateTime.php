<?php

/**
 * Holds logic to validate that a given value is a valid datetime
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use DateTimeInterface;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validator as v;

class DateTime implements BaseType
{
    /**
     * Validates that a given property value is valid object
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid
     * @throws ContextPropertyException
     */
    public function validate(Property $property, Context $context): void
    {
        $reflection = $property->getReflection();
        $datetimeAttributes = $reflection->getAttributes(Rules\DateTime::class);
        if (count($datetimeAttributes) > 0) {
            return;
        }

        $format = $context->getOptionalGlobal('datetime.format', DateTimeInterface::ATOM);
        v::anyOf(v::dateTime($format), v::instance(DateTimeInterface::class))->assert($property->getValue());
    }
}
