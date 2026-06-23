<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators;

use ArrayObject;
use Attributes\Validation\Cache\ReflectionCache;
use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\Types as TypeValidators;
use DateTime;
use DateTimeInterface;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;

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

    public function validate(Property $property, Context $context): void
    {
        $reflectionProperty = $property->getReflection();
        if (! $reflectionProperty->hasType()) {
            return;
        }

        $context->set(self::class, $this, override: true);
        $propertyType = $reflectionProperty->getType();
        
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

    private function validateUnionOptimized(ReflectionUnionType $propertyType, Property $property, Context $context): void
    {
        $value = $property->getValue();
        $valueType = gettype($value);
        $allTypes = $propertyType->getTypes();
        
        if ($value === null) {
            foreach ($allTypes as $type) {
                if ($type->allowsNull()) {
                    return;
                }
            }
        }
        
        if (isset($this->typeAliases[$valueType])) {
            $resolvedValueType = $this->typeAliases[$valueType];
            foreach ($allTypes as $type) {
                $typeName = $type->getName();
                if ($typeName === $resolvedValueType || $typeName === $valueType) {
                    try {
                        $this->validateByType($type, $property, $context);
                        return;
                    } catch (RespectValidationException) {
                        continue;
                    }
                }
            }
        }

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

    private function getDefaultRules(): array
    {
        return [
            'bool' => new TypeValidators\RawBool(),
            'int' => new TypeValidators\RawInt(),
            'float' => new TypeValidators\RawFloat(),
            'string' => new TypeValidators\RawString(),
            'array' => new TypeValidators\RawArray(),
            'object' => new TypeValidators\RawObject(),
            'enum' => new TypeValidators\RawEnum(),
            'null' => new TypeValidators\RawNull(),
            'mixed' => new TypeValidators\RawMixed(),
            'callable' => new TypeValidators\RawCallable(),
            DateTime::class => new TypeValidators\DateTime(),
            'interface' => new TypeValidators\StrictType(),
            ArrayObject::class => new TypeValidators\ArrayObject(),
            'default' => new TypeValidators\AnyClass(),
        ];
    }

    public function getTypeValidator(ReflectionNamedType|ReflectionType $propertyType, bool $ignoreNull = false): TypeValidators\BaseType
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

    private function getTypeValidatorCached(ReflectionNamedType|ReflectionType $propertyType): TypeValidators\BaseType
    {
        $cacheKey = $propertyType->getName() . ($propertyType->allowsNull() ? ':nullable' : '');
        
        if (!isset(self::$typeValidatorCache[$cacheKey])) {
            self::$typeValidatorCache[$cacheKey] = $this->getTypeValidator($propertyType);
        }
        
        return self::$typeValidatorCache[$cacheKey];
    }

    public static function clearCache(): void
    {
        self::$typeValidatorCache = [];
    }
}
