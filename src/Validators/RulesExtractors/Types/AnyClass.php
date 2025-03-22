<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid class or has enough properties to build that class
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\PropertyValidator;
use ReflectionClass;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class AnyClass implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if is a valid class or has enough properties to build that class
     *
     * @param  Context  $context  - Validation context
     *
     * @throws ValidationException - If type-hint is not a valid class
     */
    public function extract(Context $context): Validatable
    {
        $typeHint = $context->getLocal('property.typeHint');
        $propertiesRules = new Rules\AllOf(
            new Rules\ArrayVal,
            $this->extractClassPropertiesRules($context),
        );

        return new Rules\AnyOf(new Rules\Instance($typeHint), $propertiesRules);
    }

    /**
     * @throws ValidationException
     * @throws ContextPropertyException - When unable to find context properties
     */
    private function extractClassPropertiesRules(Context $context): Rules\KeySet
    {
        $typeHint = $context->getLocal('property.typeHint');
        if (! class_exists($typeHint)) {
            throw new ValidationException("Unable to locate class '$typeHint'");
        }

        $rules = [];
        $reflectionClass = new ReflectionClass($typeHint);
        $rulesExtractor = $context->getLocal(PropertyValidator::class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $property = new Property($reflectionProperty, null);

            foreach ($rulesExtractor->getRulesFromProperty($property, $context) as $rule) {
                $rules[] = new Rules\Key($property->getName(), $rule);
            }
        }

        return new Rules\KeySet(...$rules);
    }
}
