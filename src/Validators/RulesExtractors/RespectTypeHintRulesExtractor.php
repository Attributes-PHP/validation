<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\Rules as TypeRules;
use Attributes\Validation\Validators\RulesExtractors\Types as TypeExtractors;
use Attributes\Validation\Validators\RulesExtractors\Types\TypeRespectExtractor;
use DateTime;
use DateTimeInterface;
use Generator;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Respect\Validation\Rules\Core\Simple;

class RespectTypeHintRulesExtractor implements PropertyRulesExtractor
{
    private array $typeHintRules;

    public function __construct(array $typeHintRules = [])
    {
        $this->typeHintRules = array_merge($this->getDefaultRules(), $typeHintRules);
    }

    /**
     * Yields each validation rule of a given property
     *
     * @param  Property  $property  - Property to yield the rules from
     * @return Generator<Simple>
     *
     * @throws ValidationException
     * @throws ContextPropertyException
     */
    public function getRulesFromProperty(Property $property, Context $context): Generator
    {
        $reflectionProperty = $property->getReflection();
        if (! $reflectionProperty->hasType()) {
            return;
        }

        $context->setLocal(self::class, $this, override: true);
        $propertyType = $reflectionProperty->getType();
        if ($propertyType instanceof ReflectionNamedType) {
            $typeExtractor = $this->getTypeExtractor($propertyType);
            $context->setLocal('property.typeHint', $propertyType->getName(), override: true);
            yield $typeExtractor->extract($context);
        } elseif ($propertyType instanceof ReflectionUnionType || $propertyType instanceof ReflectionIntersectionType) {
            yield $this->getTypeRuleFromReflectionProperty($propertyType, $context);
        } else {
            throw new ValidationException("Unsupported type {$propertyType->getName()}");
        }
    }

    /**
     * Retrieves the expected rule according to the given type
     *
     * @throws ValidationException
     * @throws ContextPropertyException
     */
    private function getTypeRuleFromReflectionProperty(ReflectionUnionType|ReflectionIntersectionType $propertyType, Context $context): TypeRules\InternalType
    {
        $rules = [];
        $mapping = [];

        foreach ($propertyType->getTypes() as $type) {
            $typeExtractor = $this->getTypeExtractor($type);
            $context->setLocal('property.typeHint', $type->getName(), override: true);
            $rule = $typeExtractor->extract($context);
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
            'enum' => new TypeExtractors\RawEnum,
            'null' => new TypeExtractors\RawNull,
            DateTime::class => new TypeExtractors\DateTime,
            DateTimeInterface::class => new TypeExtractors\DateTime,
            'default' => new TypeExtractors\AnyClass,
        ];
    }

    /**
     * Retrieves the type-hint extractor according to the given property type
     */
    private function getTypeExtractor(ReflectionNamedType $propertyType): TypeRespectExtractor
    {
        if ($propertyType->allowsNull()) {
            return $this->typeHintRules['null'];
        }

        $typeHintName = $propertyType->getName();
        $typeHintName = enum_exists($typeHintName) ? 'enum' : $typeHintName;
        $typeName = isset($this->typeHintRules[$typeHintName]) ? $typeHintName : 'default';

        return $this->typeHintRules[$typeName];
    }

    public function getRules(): array
    {
        return $this->typeHintRules;
    }
}
