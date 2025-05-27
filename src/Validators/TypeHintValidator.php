<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators;

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
        DateTime::class => DateTime::class,
        DateTimeInterface::class => DateTime::class,
        'interface' => 'interface',
        'default' => 'default',
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
        if ($propertyType instanceof ReflectionNamedType) {
            $this->validateByType($propertyType, $property, $context);
        } elseif ($propertyType instanceof ReflectionUnionType) {
            $this->validateUnion($propertyType, $property, $context);
        } elseif ($propertyType instanceof ReflectionIntersectionType) {
            foreach ($propertyType->getTypes() as $type) {
                $this->validateByType($type, $property, $context);
            }
        } else {
            throw new ValidationException("Unsupported type {$propertyType->getName()}");
        }
    }

    private function validateUnion(ReflectionUnionType $propertyType, Property $property, Context $context): void
    {
        $valueType = gettype($property->getValue());
        $allTypes = $propertyType->getTypes();
        if (isset($this->typeAliases[$valueType])) {
            $valueType = $this->typeAliases[$valueType];
            foreach ($allTypes as $type) {
                if ($type->getName() !== $valueType) {
                    continue;
                }

                try {
                    $this->typeHintRules[$valueType]->validate($property, $context);

                    return;
                } catch (RespectValidationException $e) {
                }
                break;
            }
        } else {
            $valueType = null;
        }

        foreach ($allTypes as $type) {
            if ($valueType === $type->getName()) {
                continue;
            }

            try {
                $this->validateByType($type, $property, $context);

                return;
            } catch (RespectValidationException $error) {
            }
        }

        throw new ValidationException('Invalid property '.$property->getName());
    }

    private function validateByType(ReflectionNamedType|ReflectionType $type, Property $property, Context $context): void
    {
        $typeHintValidator = $this->getTypeValidator($type);
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
            'bool' => new TypeValidators\RawBool,
            'int' => new TypeValidators\RawInt,
            'float' => new TypeValidators\RawFloat,
            'string' => new TypeValidators\RawString,
            'array' => new TypeValidators\RawArray,
            'object' => new TypeValidators\RawObject,
            'enum' => new TypeValidators\RawEnum,
            'null' => new TypeValidators\RawNull,
            DateTime::class => new TypeValidators\DateTime,
            DateTimeInterface::class => new TypeValidators\DateTime,
            'interface' => new TypeValidators\StrictType,
            'default' => new TypeValidators\AnyClass,
        ];
    }

    /**
     * Retrieves the type-hint validator according to the given property type
     */
    public function getTypeValidator(ReflectionNamedType|ReflectionType $propertyType, bool $ignoreNull = false): TypeValidators\BaseType
    {
        if ($propertyType->allowsNull() && ! $ignoreNull) {
            return $this->typeHintRules['null'];
        }

        $typeHintName = $propertyType->getName();
        $typeName = isset($this->typeHintRules[$typeHintName]) ? $typeHintName : 'default';
        if ($typeName == 'default') {
            if (enum_exists($typeHintName)) {
                return $this->typeHintRules['enum'];
            }
            if (interface_exists($typeHintName)) {
                return $this->typeHintRules['interface'];
            }
        }

        return $this->typeHintRules[$typeName];
    }
}
