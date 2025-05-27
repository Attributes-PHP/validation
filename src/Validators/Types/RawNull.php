<?php

/**
 * Holds logic to check if a given value is a valid null
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\TypeHintValidator;
use ReflectionNamedType;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

final class RawNull implements BaseType
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
        $value = $property->getValue();
        if (is_null($value)) {
            return;
        }

        if (! $context->get('option.strict') && ! v::notOptional()->validate($value)) {
            $property->setValue(null);

            return;
        }

        $reflectionNamedType = $context->get(ReflectionNamedType::class);
        $typeHintValidator = $context->get(TypeHintValidator::class);
        $validator = $typeHintValidator->getTypeValidator($reflectionNamedType, ignoreNull: true);
        $validator->validate($property, $context);
    }
}
