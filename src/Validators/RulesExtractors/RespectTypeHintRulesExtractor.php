<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\Rules as TypeRules;
use DateTime;
use DateTimeInterface;
use Generator;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Rules\Core\Simple;

class RespectTypeHintRulesExtractor implements PropertyRulesExtractor
{
    private array $typeHintRules;

    public function __construct(array $typeHintRules = [], bool $strict = false)
    {
        $this->typeHintRules = array_merge($this->getDefaultRules($strict), $typeHintRules);
    }

    /**
     * Yields each validation rule of a given property
     *
     * @param  Property  $property  - Property to yield the rules from
     * @return Generator<Simple>
     *
     * @throws ValidationException
     */
    public function getRulesFromProperty(Property $property): Generator
    {
        $reflectionProperty = $property->getReflection();
        if (! $reflectionProperty->hasType()) {
            return;
        }

        $propertyType = $reflectionProperty->getType();
        if ($propertyType instanceof ReflectionNamedType) {
            $typeName = isset($this->typeHintRules[$propertyType->getName()]) ? $propertyType->getName() : 'object';
            yield $this->typeHintRules[$typeName];
        } elseif ($propertyType instanceof ReflectionUnionType || $propertyType instanceof ReflectionIntersectionType) {
            yield from $this->getTypeRuleFromReflectionProperty($propertyType);
        } else {
            throw new ValidationException("Unsupported type {$propertyType->getName()}");
        }
    }

    /**
     * Retrieves the expected rule according to the given type
     *
     * @return Generator<TypeRules\InternalType>
     */
    private function getTypeRuleFromReflectionProperty(ReflectionUnionType|ReflectionIntersectionType $propertyType): Generator
    {
        $rules = [];
        $mapping = [];

        foreach ($propertyType->getTypes() as $type) {
            $typeHint = isset($this->typeHintRules[$type->getName()]) ? $type->getName() : 'object';
            $rule = $this->typeHintRules[$typeHint];
            $rules[] = $rule;
            $mapping[$rule->getName()] = $type->getName();
        }

        yield is_a($propertyType, ReflectionUnionType::class) ? new TypeRules\Union($mapping, ...$rules) : new TypeRules\Intersection($mapping, ...$rules);
    }

    /**
     * Retrieves default type hint rules. If strict, retrieves strict type checking otherwise
     * does a loose check.
     */
    private function getDefaultRules(bool $strict): array
    {
        if ($strict) {
            return [
                'bool' => new Rules\BoolType,
                'int' => new Rules\IntType,
                'float' => new Rules\FloatType,
                'string' => new Rules\StringType,
                'array' => new Rules\ArrayType,
                'object' => new Rules\AnyOf(new Rules\ObjectType, new Rules\ArrayType),
                DateTime::class => new Rules\DateTime,
                DateTimeInterface::class => new Rules\DateTime,
            ];
        }

        return [
            'bool' => new Rules\BoolVal,
            'int' => new Rules\IntVal,
            'float' => new Rules\FloatVal,
            'string' => new Rules\StringVal,
            'array' => new Rules\ArrayVal,
            'object' => new Rules\AnyOf(new Rules\ObjectType, new Rules\ArrayVal),
            DateTime::class => new Rules\DateTime,
            DateTimeInterface::class => new Rules\DateTime,
        ];
    }
}
