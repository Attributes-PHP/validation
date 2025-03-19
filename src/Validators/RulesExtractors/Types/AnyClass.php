<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid class or has enough properties to build that class
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\RulesExtractors\PropertiesContainer;
use Attributes\Validation\Validators\RulesExtractors\PropertyRulesExtractor;
use ReflectionClass;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class AnyClass implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if is a valid class or has enough properties to build that class
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  PropertiesContainer  $propertiesContainer  - Additional properties which could influence the validation rules
     *
     * @throws ValidationException - If type-hint is not a valid class
     */
    public function extract(bool $strict, PropertiesContainer $propertiesContainer): Validatable
    {
        $typeHint = $propertiesContainer->getProperty('typeHint');
        $rulesExtractor = $propertiesContainer->getProperty('mainRulesExtractor');
        $propertiesRules = new Rules\AllOf(
            new Rules\ArrayVal,
            $this->extractClassPropertiesRules($typeHint, $rulesExtractor),
        );

        return new Rules\AnyOf(new Rules\Instance($typeHint), $propertiesRules);
    }

    /**
     * @throws ValidationException
     */
    private function extractClassPropertiesRules(string $typeHint, PropertyRulesExtractor $rulesExtractor): Rules\KeySet
    {
        if (! class_exists($typeHint)) {
            throw new ValidationException("Unable to locate class '$typeHint'");
        }

        $rules = [];
        $reflectionClass = new ReflectionClass($typeHint);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $property = new Property($reflectionProperty, null);

            foreach ($rulesExtractor->getRulesFromProperty($property) as $rule) {
                $rules[] = new Rules\Key($property->getName(), $rule);
            }
        }

        return new Rules\KeySet(...$rules);
    }
}
