<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\Rules as TypeRules;
use Attributes\Validation\Validators\RulesExtractors\Types as TypeExtractors;
use DateTime;
use DateTimeInterface;
use Generator;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Respect\Validation\Rules\Core\Simple;

class RespectTypeHintRulesExtractor implements PropertyRulesExtractor, RulesContainer
{
    private array $typeHintRules;

    private bool $strict;

    public function __construct(array $typeHintRules = [], bool $strict = false)
    {
        $this->strict = $strict;
        $this->typeHintRules = array_merge($this->getDefaultRules(), $typeHintRules);
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
            $typeHintName = $propertyType->getName();
            $typeName = isset($this->typeHintRules[$typeHintName]) ? $typeHintName : 'default';
            $typeName = $propertyType->allowsNull() ? 'null' : $typeName;
            yield $this->typeHintRules[$typeName]->extract($this->strict, $typeHintName);
        } elseif ($propertyType instanceof ReflectionUnionType || $propertyType instanceof ReflectionIntersectionType) {
            yield $this->getTypeRuleFromReflectionProperty($propertyType);
        } else {
            throw new ValidationException("Unsupported type {$propertyType->getName()}");
        }
    }

    /**
     * Retrieves the expected rule according to the given type
     */
    private function getTypeRuleFromReflectionProperty(ReflectionUnionType|ReflectionIntersectionType $propertyType): TypeRules\InternalType
    {
        $rules = [];
        $mapping = [];

        foreach ($propertyType->getTypes() as $type) {
            $typeHintName = $type->getName();
            $typeHint = isset($this->typeHintRules[$typeHintName]) ? $typeHintName : 'default';
            $typeHint = $type->allowsNull() ? 'null' : $typeHint;
            $rule = $this->typeHintRules[$typeHint]->extract($this->strict, $typeHintName);
            $rules[] = $rule;
            $mapping[$rule->getName()] = $type->getName();
        }

        return is_a($propertyType, ReflectionUnionType::class) ? new TypeRules\Union($mapping, ...$rules) : new TypeRules\Intersection($mapping, ...$rules);
    }

    /**
     * Retrieves default type hint rules extractors according to their type hint
     */
    public function getDefaultRules(): array
    {
        return [
            'bool' => new TypeExtractors\RawBool,
            'int' => new TypeExtractors\RawInt,
            'float' => new TypeExtractors\RawFloat,
            'string' => new TypeExtractors\RawString,
            'array' => new TypeExtractors\RawArray,
            'object' => new TypeExtractors\RawObject,
            'null' => new TypeExtractors\RawNull($this),
            DateTime::class => new TypeExtractors\DateTime,
            DateTimeInterface::class => new TypeExtractors\DateTime,
            'default' => new TypeExtractors\AnyClass($this),
        ];
    }

    public function getRules(): array
    {
        return $this->typeHintRules;
    }
}
