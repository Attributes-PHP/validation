<?php

namespace Attributes\Validation\Transformers;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Property;
use Attributes\Validation\Transformers\Types as Types;
use DateTime;
use DateTimeInterface;
use Exception;
use ReflectionNamedType;

class CastPropertyTransformer implements PropertyTransformer
{
    private array $mappings;

    private array $parentMappings;

    public function __construct(array $mappings = [], array $parentMappings = [])
    {
        $this->mappings = array_merge($this->getDefaultMappings(), $mappings);
        $this->parentMappings = array_merge($this->getDefaultParentMappings(), $parentMappings);
    }

    /**
     * Casts a value according to the type hint of the given property
     *
     * @param  Property  $property  - Property to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Cast value
     *
     * @throws TransformException - If unable to cast property value
     * @throws ContextPropertyException - When unable to find context properties
     */
    public function transform(Property $property, Context $context): mixed
    {
        $reflectionProperty = $property->getReflection();
        if (! $reflectionProperty->hasType()) {
            return $property->getValue();
        }

        $context->setLocal(PropertyTransformer::class, $this, override: true);
        $propertyType = $reflectionProperty->getType();
        if ($propertyType instanceof ReflectionNamedType) {
            return $this->castValue($propertyType, $property->getValue(), $context);
        }

        $allPropertyTypes = $propertyType->getTypes();
        // Looping reversely on purpose to cast sensible types first e.g. bool -> float -> int -> string -> array -> object
        for ($i = count($allPropertyTypes) - 1; $i >= 0; $i--) {
            $type = $allPropertyTypes[$i];
            try {
                return $this->castValue($type, $property->getValue(), $context);
            } catch (TransformException $error) {
            }
        }

        throw new TransformException("Unable to cast property '{$property->getName()}' into any of the Union types");
    }

    /**
     * Tries to cast a given value according to the type hint
     *
     * @param  ReflectionNamedType  $propertyType  - The given property type
     * @param  mixed  $value  - The value to cast
     * @return mixed - Cast value
     *
     * @throws TransformException - If unable to cast the given value
     * @throws ContextPropertyException
     */
    protected function castValue(ReflectionNamedType $propertyType, mixed $value, Context $context): mixed
    {
        $propertyTypeName = $propertyType->getName();
        $context->setLocal('property.typeHint', $propertyTypeName, override: true);
        $cast = $this->getTypeCastInstance($propertyTypeName);
        $context->setLocal(Types\TypeCast::class, $cast, override: true);
        if ($propertyType->allowsNull()) {
            $cast = new $this->mappings['null']($cast);
        }

        try {
            return $cast->cast($value, $context);
        } catch (TransformException $error) {
            throw $error;
        } catch (Exception $error) {
            throw new TransformException("Unexpected error while casting $propertyTypeName", previous: $error);
        }
    }

    /**
     * @throws TransformException
     */
    private function getParentTypeClass(string $propertyTypeName): string
    {
        if (! class_exists($propertyTypeName)) {
            throw new TransformException("Unsupported cast of '{$propertyTypeName}' property type");
        }

        foreach ($this->parentMappings as $type) {
            if (is_subclass_of($propertyTypeName, $type)) {
                $this->mappings[$propertyTypeName] = $this->mappings[$type];

                return $type;
            }
        }

        return $this->mappings['default'];
    }

    protected function getDefaultMappings(): array
    {
        return [
            // Builtins
            'bool' => Types\RawBool::class,
            'float' => Types\RawFloat::class,
            'int' => Types\RawInt::class,
            'string' => Types\RawString::class,
            'null' => Types\RawNull::class,
            'enum' => Types\RawEnum::class,
            'array' => Types\RawArray::class,
            'object' => Types\RawObject::class,
            // Class builtins
            DateTime::class => Types\DateTime::class,
            DateTimeInterface::class => Types\DateTime::class,
            // Default casting
            'default' => Types\AnyClass::class,
        ];
    }

    protected function getDefaultParentMappings(): array
    {
        return [
            DateTimeInterface::class,
        ];
    }

    /**
     * @throws TransformException
     */
    public function getTypeCastInstance(string $propertyTypeName): Types\TypeCast
    {
        if (enum_exists($propertyTypeName)) {
            $propertyTypeName = 'enum';
        }

        $castClass = $this->mappings[$propertyTypeName] ?? $this->getParentTypeClass($propertyTypeName);
        if (! is_subclass_of($castClass, Types\TypeCast::class)) {
            $expectedClass = Types\TypeCast::class;
            throw new TransformException("Unable to cast '$propertyTypeName'. Expected '$castClass' to implement $expectedClass");
        }

        return new $castClass;
    }
}
