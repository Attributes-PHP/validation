<?php

namespace Attributes\Validation\Transformers;

use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Property;
use Attributes\Validation\Transformers\Types as Types;
use Attributes\Validation\Transformers\Types\TypeCast;
use DateTime;
use DateTimeInterface;
use Exception;
use ReflectionNamedType;

class CastPropertyTransformer implements PropertyTransformer
{
    private array $mappings;

    public function __construct(array $mappings = [])
    {
        $this->mappings = array_merge($this->getDefaultMappings(), $mappings);
    }

    /**
     * Casts a value according to the type hint of the given property
     *
     * @param  Property  $property  - Property to cast
     * @return mixed - Cast value
     *
     * @throws TransformException - If unable to cast property value
     */
    public function transform(Property $property): mixed
    {
        $reflectionProperty = $property->getReflection();
        if (! $reflectionProperty->hasType()) {
            return $property->getValue();
        }

        $propertyType = $reflectionProperty->getType();
        if ($propertyType instanceof ReflectionNamedType) {
            return $this->castValue($propertyType, $property->getValue());
        }

        $allPropertyTypes = $propertyType->getTypes();
        // Looping reversely on purpose to cast sensible types first e.g. bool -> float -> int -> string -> array -> object
        for ($i = count($allPropertyTypes) - 1; $i >= 0; $i--) {
            $type = $allPropertyTypes[$i];
            try {
                return $this->castValue($type, $property->getValue());
            } catch (TransformException $error) {
            }
        }

        throw new TransformException("Unable to cast property value '{$property->getName()}' into any of the Union types");
    }

    /**
     * Tries to cast a given value according to the type hint
     *
     * @param  ReflectionNamedType  $propertyType  - The given property type
     * @param  mixed  $value  - The value to cast
     * @return mixed - Cast value
     *
     * @throws TransformException - If unable to cast the given value
     */
    protected function castValue(ReflectionNamedType $propertyType, mixed $value): mixed
    {
        $propertyTypeName = $propertyType->getName();
        if (! isset($this->mappings[$propertyTypeName])) {
            throw new TransformException("Unsupported cast of '{$propertyTypeName}' property type");
        }

        $castClass = $this->mappings[$propertyTypeName];
        if (! is_subclass_of($castClass, TypeCast::class)) {
            $expectedClass = TypeCast::class;
            throw new TransformException("Unable to cast '$propertyTypeName'. Expected '$castClass' to implement $expectedClass");
        }

        try {
            $cast = new $castClass;

            return $cast->cast($value);
        } catch (TransformException $error) {
            throw $error;
        } catch (Exception $error) {
            throw new TransformException("Unexpected error while casting $propertyTypeName", previous: $error);
        }
    }

    private function getDefaultMappings(): array
    {
        return [
            // Builtins
            'bool' => Types\Boolean::class,
            'float' => Types\FloatingPoint::class,
            'int' => Types\Integer::class,
            'string' => Types\RawString::class,
            // Class builtins
            DateTime::class => Types\DateTime::class,
            DateTimeInterface::class => Types\DateTime::class,
        ];
    }
}
