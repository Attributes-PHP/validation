<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Transformers\CastContainer;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

class AnyClass implements TypeCast
{
    private string $type;

    private CastContainer $castContainer;

    public function __construct(string $type, CastContainer $castContainer)
    {
        $this->type = $type;
        $this->castContainer = $castContainer;
    }

    /**
     * Checks if a given value has the expected type
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. This is ignored
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     * @throws ReflectionException
     */
    public function cast(mixed $value, bool $strict): mixed
    {
        if (is_a($value, $this->type)) {
            return $value;
        }

        if (! class_exists($this->type)) {
            throw new TransformException("Unable to locate class '$this->type'");
        }

        if (! is_array($value)) {
            throw new TransformException("Unable to cast '$this->type'. Expected an array $value is not an array");
        }

        $class = new $this->type;
        $reflectionClass = new ReflectionClass($class);
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();

            if (! isset($value[$propertyName])) {
                continue;
            }

            $propertyValue = $value[$propertyName];
            if (! $property->hasType()) {
                $property->setValue($class, $propertyValue);

                continue;
            }

            $propertyType = $property->getType();
            if ($propertyType instanceof ReflectionNamedType) {
                $propertyValue = $this->castValue($propertyType, $strict, $propertyValue);
            } elseif ($propertyType instanceof ReflectionUnionType || $propertyType instanceof ReflectionIntersectionType) {
                $propertyValue = $this->castUnionOrIntersectionValue($propertyType, $strict, $propertyValue);
            } else {
                throw new TransformException("Unsupported reflection type {$propertyType->getName()}");
            }

            $property->setValue($class, $propertyValue);
        }

        return $class;
    }

    /**
     * @throws TransformException
     */
    private function castUnionOrIntersectionValue(ReflectionUnionType|ReflectionIntersectionType $propertyType, bool $strict, mixed $propertyValue): TypeCast
    {
        foreach ($propertyType->getTypes() as $type) {
            try {
                return $this->castValue($type, $strict, $propertyValue);
            } catch (TransformException $exception) {
            }
        }

        throw new TransformException('Unable to cast Union or Intersection');
    }

    /**
     * @throws TransformException
     */
    private function castValue(ReflectionNamedType $propertyType, bool $strict, mixed $propertyValue): mixed
    {
        $propertyTypeName = $propertyType->getName();
        $cast = $this->castContainer->getTypeCastInstance($propertyTypeName);

        return $cast->cast($propertyValue, $strict);
    }
}
