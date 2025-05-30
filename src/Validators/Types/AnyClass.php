<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid class or has enough properties to build that class
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ContinueValidationException;
use Attributes\Validation\Exceptions\StopValidationException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validator;
use ReflectionException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

final class AnyClass implements BaseType
{
    /**
     * Validates that a given property value is valid array
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid array
     * @throws ContextPropertyException
     * @throws ReflectionException
     * @throws ContinueValidationException - When validation should keep running even when an error is encountered
     * @throws StopValidationException - When we should stop when the first error is encountered
     */
    public function validate(Property $property, Context $context): void
    {
        $value = $property->getValue();
        $typeHint = $context->get('property.typeHint');
        if ($value instanceof $typeHint) {
            return;
        }

        v::arrayVal()->assert($value);
        $recursionLevel = $context->getOptional('internal.recursionLevel', 0);
        $context->set('internal.recursionLevel', $recursionLevel + 1, override: true);
        $validator = new Validator(context: $context);
        try {
            $validModel = $validator->validate((array) $value, $typeHint);
            $property->setValue($validModel);
        } catch (ValidationException) {
            if ($context->get('option.stopFirstError')) {
                throw new StopValidationException('Stop validation');
            }
            throw new ContinueValidationException('Class validation failed');
        } finally {
            $context->set('internal.recursionLevel', $recursionLevel, override: true);
        }
    }
}
