<?php

/**
 * Holds logic to check if a given value belongs to a given enumeration
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use ReflectionEnum;
use ReflectionException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

class RawEnum implements BaseType
{
    /**
     * Validates that a given property value belongs to a given enumeration
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid
     * @throws ContextPropertyException
     * @throws ReflectionException
     */
    public function validate(Property $property, Context $context): void
    {
        $typeHint = $context->getLocal('property.typeHint');
        $reflectionEnum = new ReflectionEnum($typeHint);
        $validOptions = [];
        foreach ($typeHint::cases() as $case) {
            $validOptions[] = $reflectionEnum->getBackingType() ? $case->value : $case->name;
        }

        $isStrict = $context->getGlobal('option.strict');
        // Int enum's do fail equals validation with some invalid data e.g. ['this is an array of strings']
        $isStrict = $reflectionEnum->getBackingType() && $reflectionEnum->getBackingType()->getName() == 'int' ? true : $isStrict;

        v::anyOf(v::instance($typeHint), v::in($validOptions, compareIdentical: $isStrict))->assert($property->getValue());
    }
}
