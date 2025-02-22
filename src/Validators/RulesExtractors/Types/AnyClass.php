<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid class or has enough properties to build that class
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\RulesExtractors\PropertyRulesExtractor;
use ReflectionClass;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class AnyClass implements TypeRespectExtractor
{
    private PropertyRulesExtractor $rulesExtractor;

    public function __construct(PropertyRulesExtractor $rulesExtractor)
    {
        $this->rulesExtractor = $rulesExtractor;
    }

    /**
     * Retrieves the validation rules to check if is a valid class or has enough properties to build that class
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  string  $typeHint  - The exact type-hint. Useful for more complex ones e.g. classes
     *
     * @throws ValidationException - If type-hint is not a valid class
     */
    public function extract(bool $strict, string $typeHint): Validatable
    {
        $propertiesRules = new Rules\AllOf(
            new Rules\ArrayVal,
            $this->extractClassPropertiesRules($typeHint),
        );

        return new Rules\AnyOf(new Rules\Instance($typeHint), $propertiesRules);
    }

    /**
     * @throws ValidationException
     */
    private function extractClassPropertiesRules(string $typeHint): Rules\KeySet
    {
        if (! class_exists($typeHint)) {
            throw new ValidationException("Unable to locate class '$typeHint'");
        }

        $rules = [];
        $reflectionClass = new ReflectionClass($typeHint);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $property = new Property($reflectionProperty, null);

            foreach ($this->rulesExtractor->getRulesFromProperty($property) as $rule) {
                $rules[] = new Rules\Key($property->getName(), $rule);
            }
        }

        return new Rules\KeySet(...$rules);
    }
}
