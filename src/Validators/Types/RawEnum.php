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

final class RawEnum implements BaseType
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
        $typeHint = $context->get('property.typeHint');
        $value = $property->getValue();
        if ($value instanceof $typeHint) {
            return;
        }

        $reflectionEnum = new ReflectionEnum($typeHint);
        $validOptions = [];
        $backingType = $reflectionEnum->getBackingType();
        foreach ($typeHint::cases() as $case) {
            $validOptions[] = $backingType ? $case->value : $case->name;
        }

        $isStrict = $context->get('option.strict');
        $isIntEnum = $backingType && $backingType->getName() == 'int';
        // Int enum's do fail equals validation with some invalid data e.g. ['this is an array of strings']
        $isStrict = $isIntEnum || is_bool($value) ? true : $isStrict;

        v::in($validOptions, compareIdentical: $isStrict)->assert($value);

        $value = $isIntEnum ? (int) $value : (string) $value;
        $value = $backingType ? $typeHint::from($value) : $reflectionEnum->getCase($value)->getValue();
        $property->setValue($value);
    }
}
