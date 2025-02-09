<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid class or has enough properties to build that class
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\MaxRecursionLimitException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Validators\Rules as TypeRules;
use Attributes\Validation\Validators\RulesExtractors\RulesContainer;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class AnyClass implements TypeRespectExtractor
{
    private RulesContainer $rulesContainer;

    public function __construct(RulesContainer $rulesContainer)
    {
        $this->rulesContainer = $rulesContainer;
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
            $this->extractClassPropertiesRules($strict, $typeHint),
        );

        return new Rules\AnyOf(new Rules\Instance($typeHint), $propertiesRules);
    }

    /**
     * @throws ValidationException
     * @throws MaxRecursionLimitException
     * @throws ComponentException
     */
    private function extractClassPropertiesRules(bool $strict, string $typeHint, int $depth = 0, string $originalTypeHint = ''): Rules\KeySet
    {
        if (! class_exists($typeHint)) {
            throw new ValidationException("Unable to locate class '$typeHint'");
        }

        if ($depth >= 35) {
            throw new MaxRecursionLimitException("Reached maximum number of recursions in model '$originalTypeHint'");
        }

        $rules = [];
        $reflectionClass = new ReflectionClass($typeHint);
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();

            if (! $property->hasType()) {
                $rules[] = new Rules\Key($propertyName);

                continue;
            }

            $propertyType = $property->getType();
            if ($propertyType instanceof ReflectionNamedType) {
                $rules[] = $this->extractRule($propertyType, $strict, $propertyName);
            } elseif ($propertyType instanceof ReflectionUnionType || $propertyType instanceof ReflectionIntersectionType) {
                $rules[] = $this->extractRuleFromUnionOrIntersection($propertyType, $strict, $propertyName);
            } else {
                throw new ValidationException("Unsupported type {$propertyType->getName()}");
            }
        }

        return new Rules\KeySet(...$rules);
    }

    private function extractRule(ReflectionNamedType $propertyType, bool $strict, string $propertyName): Rules\Key
    {
        $typeHintName = $propertyType->getName();
        $typeHintRules = $this->rulesContainer->getRules();
        $typeExtractor = $typeHintRules[$typeHintName] ?? $this;
        $extractedRules = $typeExtractor->extract($strict, $typeHintName);

        return new Rules\Key($propertyName, $extractedRules);
    }

    private function extractRuleFromUnionOrIntersection(ReflectionUnionType|ReflectionIntersectionType $propertyType, bool $strict, string $propertyName): Rules\Key
    {
        $rules = [];
        $mapping = [];
        foreach ($propertyType->getTypes() as $type) {
            $typeHintName = $type->getName();
            $typeHintRules = $this->rulesContainer->getRules();
            $typeExtractor = $typeHintRules[$typeHintName] ?? $this;
            $rule = $typeExtractor->extract($strict, $typeHintName);
            $rules[] = $rule;
            $mapping[$rule->getName()] = $typeHintName;
        }

        $rule = is_a($propertyType, ReflectionUnionType::class) ? new TypeRules\Union($mapping, ...$rules) : new TypeRules\Intersection($mapping, ...$rules);

        return new Rules\Key($propertyName, $rule);
    }
}
