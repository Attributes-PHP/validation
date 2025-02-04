<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid class or has enough properties to build that class
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\MaxRecursionLimitException;
use Attributes\Validation\Exceptions\ValidationException;
use ReflectionClass;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class AnyClass implements TypeRespectExtractor
{
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
            $this->extractPropertyRules($typeHint),
        );

        return new Rules\AnyOf(new Rules\Instance($typeHint), $propertiesRules);
    }

    private function extractPropertyRules(string $typeHint, int $depth = 0, string $originalTypeHint = ''): Rules\KeySet
    {
        if (! class_exists($typeHint)) {
            throw new ValidationException("Unable to locate class '$typeHint'");
        }

        if ($depth >= 35) {
            throw new MaxRecursionLimitException("Reached maximum number of recursions in model '$originalTypeHint'");
        }

        $reflectionClass = new ReflectionClass($typeHint);
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
        }
        $rules = [];

        return new Rules\KeySet(...$rules);
    }
}
