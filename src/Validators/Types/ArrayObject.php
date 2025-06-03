<?php

/**
 * Holds logic to validate an ArrayObject type
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\TypeHintValidator;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validator as v;

final class ArrayObject implements BaseType
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
     */
    public function validate(Property $property, Context $context): void
    {
        $allValues = $property->getValue();
        $typeHint = $context->get('property.typeHint');
        if ($allValues instanceof $typeHint) {
            return;
        }

        v::arrayVal()->assert($allValues);

        if (! property_exists($typeHint, 'type')) {
            $property->setValue(new $typeHint((array) $allValues));

            return;
        }

        $this->assertLengthRules($property);

        $eachRules = $this->getEachRules($property);

        $reflectionProperty = new ReflectionProperty($typeHint, 'type');
        $typeHintValidator = $context->get(TypeHintValidator::class);
        $typeProperty = new Property($reflectionProperty, null);
        $arrayObject = new $typeHint;
        foreach ($allValues as $value) {
            $typeProperty->setValue($value);
            $typeHintValidator->validate($typeProperty, $context);
            $value = $typeProperty->getValue();
            foreach ($eachRules as $rule) {
                $rule->assert($value);
            }
            $reflectionProperty->setValue($arrayObject, $value);  // This is to cast a value if necessary
            $arrayObject[] = $reflectionProperty->getValue($arrayObject);
        }

        $property->setValue($arrayObject);
    }

    /**
     * Retrieves all associated Rules\Each to speed up validation
     *
     * @return array<Rules\Each>
     */
    private function getEachRules(Property $property): array
    {
        $reflection = $property->getReflection();
        $allEachAttributes = $reflection->getAttributes(Rules\Each::class);
        if (! $allEachAttributes) {
            return [];
        }

        $eachRules = [];
        foreach ($allEachAttributes as $attribute) {
            $eachRules[] = $attribute->getArguments()[0];
        }

        return $eachRules;
    }

    /**
     * Ensures that the length of the array matches the correct boundaries to avoid spending unnecessary CPU cycles
     *
     * @throws ReflectionException
     * @throws RespectValidationException
     */
    private function assertLengthRules(Property $property): void
    {
        $reflection = $property->getReflection();
        $allLengthAttributes = $reflection->getAttributes(Rules\Length::class);
        if (! $allLengthAttributes) {
            return;
        }

        $reflectionClass = new ReflectionClass(Rules\Length::class);
        foreach ($allLengthAttributes as $attribute) {
            $rule = $reflectionClass->newInstanceArgs($attribute->getArguments());
            $rule->assert($property->getValue());
        }
    }
}
