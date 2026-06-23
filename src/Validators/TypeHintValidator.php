<?php

declare(strict_types=1);

namespace AttributesValidationValidators;

use ArrayObject;
use AttributesValidationCacheReflectionCache;
use AttributesValidationContext;
use AttributesValidationExceptionsContextPropertyException;
use AttributesValidationExceptionsValidationException;
use AttributesValidationProperty;
use AttributesValidationValidatorsTypes as TypeValidators;
use DateTime;
use DateTimeInterface;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use RespectValidationExceptionsValidationException as RespectValidationException;

class TypeHintValidator implements PropertyValidator
{
    private array $typeHintRules;
    private static array $typeValidatorCache = [];

    private array $typeAliases = [
        'bool' => 'bool',
        'int' => 'int',
        'integer' => 'int',
        'long' => 'int',
        'float' => 'float',
        'double' => 'float',
        'real' => 'float',
        'string' => 'string',
        'array' => 'array',
        'object' => 'object',
        'enum' => 'enum',
        'null' => 'null',
        'mixed' => 'mixed',
        'interface' => 'interface',
        'callable' => 'callable',
        'default' => 'default',
        DateTime::class => DateTime::class,
        DateTimeInterface::class => DateTime::class,
    ];

    public function __construct(array $typeHintRules = [], array $typeAliases = [])
    {
        $this->typeHintRules = array_merge($this->getDefaultRules(), $typeHintRules);
        $this->typeAliases = array_merge($this->typeAliases, $typeAliases);
    }

    /**
     * Yields each validation rule of a given property
     *
     * @param  Property  $property  - Property to yield the rules from
     *
     * @throws ValidationException
     * @throws ContextPropertyException
     */
    public function validate(Property $property, Context $context): void
    {
        $reflectionProperty = $property->getReflection();
        if (! $reflectionProperty->hasType()) {
            return;
        }

        $context->set(self::class, $this, override: true);
        $propertyType = $reflectionProperty->getType();
        
        // Cache the property type reflection
        $context->set(ReflectionType::class, $propertyType, override: true);
        
        if ($propertyType instanceof ReflectionNamedType) {
            $this->validateByType($propertyType, $property, $context);
        } elseif ($propertyType instanceof ReflectionUnionType) {
            $this->validateUnionOptimized($propertyType, $property, $context);
        } elseif ($propertyType instanceof ReflectionIntersectionType) {
            foreach ($propertyType->getTypes() as $type) {
                $this->validateByType($type, $property, $context);
            }
        } else {
            throw new ValidationException("Unsupported type {$propertyType->getName()}");
        }
    }

    /**
     * Optimized union type validation with fast path for common types
     */
    private function validateUnionOptimized(ReflectionUnionType $propertyType, Property $property, Context $context): void
    {
        $value = $property->getValue();
        $valueType = gettype($value);
        $allTypes = $propertyType->getTypes();
        
        // Fast path: if value is null and union allows null
        if ($value === null) {
            foreach ($allTypes as $type) {
                if ($type->allowsNull()) {
                    return;
                }
            }
        }
        
        // Fast path: match by PHP type name
        if (isset($this->typeAliases[$valueType])) {
            $resolvedValueType = $this->typeAliases[$valueType];
            foreach ($allTypes as $type) {
                $typeName = $type->getName();
                if ($typeName === $resolvedValueType || $typeName === $valueType) {
                    try {
                        $this->validateByType($type, $property, $context);
                        return;
                    } catch (RespectValidationException) {
                        // Type matched but validation failed, continue to next type
                        continue;
                    }
                }
            }
        }

        // Try each type in the union
        foreach ($allTypes as $type) {
            try {
                $this->validateByType($type, $property, $context);
                return;
            } catch (RespectValidationException) {
                continue;
            }
        }

        throw new ValidationException('Invalid property '.$property->getName());
    }

    private function validateByType(ReflectionNamedType|ReflectionType $type, Property $property, Context $context): void
    {
        $typeHintValidator = $this->getTypeValidatorCached($type);
        $context->set(ReflectionNamedType::class, $type, override: true);
        $context->set('property.typeHint', $type->getName(), override: true);
        $typeHintValidator->validate($property, $context);
    }

    /**
     * Retrieves default type hint rules extractors according to their type hint
     */
    private function getDefaultRules(): array
    {
        return [
            'bool' => new TypeValidatorsRawBool,
            'int' => new TypeValidatorsRawInt,
            'float' => new TypeValidatorsRawFloat,
            'string' => new TypeValidatorsRawString,
            'array' => new TypeValidatorsRawArray,
            'object' => new TypeValidatorsRawObject,
            'enum' => new TypeValidatorsRawEnum,
            'null' => new TypeValidatorsRawNull,
            'mixed' => new TypeValidatorsRawMixed,
            'callable' => new TypeValidatorsRawCallable,
            DateTime::class => new TypeValidatorsDateTime,
            'interface' => new TypeValidatorsStrictType,
            ArrayObject::class => new TypeValidatorsArrayObject,
            'default' => new TypeValidatorsAnyClass,
        ];
    }

    /**
     * Retrieves the type-hint validator according to the given property type with caching
     */
    public function getTypeValidator(ReflectionNamedType|ReflectionType $propertyType, bool $ignoreNull = false): TypeValidatorsBaseType
    {
        if ($propertyType->allowsNull() && ! $ignoreNull) {
            return $this->typeHintRules['null'];
        }

        $typeHintName = $propertyType->getName();
        $typeName = isset($this->typeHintRules[$typeHintName]) ? $typeHintName : 'default';
        if ($typeName == 'default') {
            if (is_subclass_of($typeHintName, ArrayObject::class)) {
                return $this->typeHintRules[ArrayObject::class];
            }

            if (enum_exists($typeHintName)) {
                return $this->typeHintRules['enum'];
            }
            if (interface_exists($typeHintName)) {
                return $this->typeHintRules['interface'];
            }
        }

        return $this->typeHintRules[$typeName];
    }

    /**
     * Get type validator with caching for repeated calls
     */
    private function getTypeValidatorCached(ReflectionNamedType|ReflectionType $propertyType): TypeValidatorsBaseType
    {
        $cacheKey = $propertyType->getName() . ($propertyType->allowsNull() ? ':nullable' : '');
        
        if (!isset(self::$typeValidatorCache[$cacheKey])) {
            self::$typeValidatorCache[$cacheKey] = $this->getTypeValidator($propertyType);
        }
        
        return self::$typeValidatorCache[$cacheKey];
    }

    /**
     * Clear the type validator cache
     */
    public static function clearCache(): void
    {
        self::$typeValidatorCache = [];
    }
}
