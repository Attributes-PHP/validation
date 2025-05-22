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
use Generator;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;

class TypeHintValidator implements PropertyValidator
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

        $context->setLocal(self::class, $this, override: true);
        $propertyType = $reflectionProperty->getType();
        if ($propertyType instanceof ReflectionNamedType) {
            $typeHintValidator = $this->getTypeValidator($propertyType);
            $context->setLocal(ReflectionNamedType::class, $propertyType, override: true);
            $context->setLocal('property.typeHint', $propertyType->getName(), override: true);
            $typeHintValidator->validate($property, $context);
        } elseif ($propertyType instanceof ReflectionUnionType) {
            foreach ($this->getTypeValidatorFromReflectionProperty($propertyType, $context) as $validator) {
                try {
                    $validator->validate($property, $context);

                    return;
                } catch (RespectValidationException $error) {
                }
            }

            throw new ValidationException('Invalid property '.$property->getName());
        } elseif ($propertyType instanceof ReflectionIntersectionType) {
            foreach ($this->getTypeValidatorFromReflectionProperty($propertyType, $context) as $validator) {
                $validator->validate($property, $context);
            }
        } else {
            throw new ValidationException("Unsupported type {$propertyType->getName()}");
        }
    }

    /**
     * @return Generator<TypeValidators\BaseType>
     */
    private function getTypeValidatorFromReflectionProperty(ReflectionUnionType|ReflectionIntersectionType $propertyType, Context $context): Generator
    {
        foreach ($propertyType->getTypes() as $type) {
            $validator = $this->getTypeValidator($type);
            $context->setLocal(ReflectionNamedType::class, $type, override: true);
            $context->setLocal('property.typeHint', $type->getName(), override: true);
            yield $validator;
        }
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
    public function getTypeValidator(ReflectionNamedType $propertyType, bool $ignoreNull = false): TypeValidators\BaseType
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
