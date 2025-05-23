<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid class or has enough properties to build that class
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\RulesExtractors\PropertyRulesExtractor;
use ReflectionClass;
use ReflectionException;
use Respect\Validation\Exceptions\ComponentException;
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
     * @throws ContextPropertyException - When unable to find context properties
     * @throws ComponentException
     * @throws ReflectionException
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
     * @throws ContextPropertyException - When unable to find context properties
     * @throws ComponentException
     * @throws ReflectionException
     */
    private function extractClassPropertiesRules(Context $context): Validatable
    {
        $typeHint = $context->getLocal('property.typeHint');
        $rules = [];
        $model = new $typeHint;
        $reflectionClass = new ReflectionClass($typeHint);
        $rulesExtractor = $context->getGlobal(PropertyRulesExtractor::class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $property = new Property($reflectionProperty, null, $typeHint);

            foreach ($rulesExtractor->getRulesFromProperty($property, $context) as $rule) {
                $isRequired = $this->isRequired($property, $model);
                $rules[] = new Rules\Key($property->getName(), $rule, $isRequired);
            }
        }

        $ignoreExtraKeys = $context->getOptionalGlobal('option.ignoreExtraKeys', true);

        return $ignoreExtraKeys ? new Rules\AllOf(...$rules) : new Rules\KeySet(...$rules);
    }

    private function isRequired(Property $property, object $model): bool
    {
        $reflectionProperty = $property->getReflection();
        if ($reflectionProperty->hasDefaultValue()) {
            return false;
        }

        return ! $reflectionProperty->isInitialized($model);
    }
}
