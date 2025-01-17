<?php

namespace Attributes\Validation\Transformers;

use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Property;
use ReflectionNamedType;

class CastPropertyTransformer implements PropertyTransformer
{
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
            return $this->castValueFromReflectionNamedType($propertyType, $property->getValue());
        }

        $allPropertyTypes = $propertyType->getTypes();
        // Looping reversely on purpose to cast sensible types first e.g. bool -> float -> int -> string -> array -> object
        for ($i = count($allPropertyTypes) - 1; $i >= 0; $i--) {
            $type = $allPropertyTypes[$i];
            try {
                return $this->castValueFromReflectionNamedType($type, $property->getValue());
            } catch (TransformException $error) {
            }
        }

        throw new TransformException("Unable to cast property value '{$property->getName()}' into any of the Union types");
    }

    /**
     * @throws TransformException - If unable to cast value to given type
     */
    protected function castValueFromReflectionNamedType(ReflectionNamedType $propertyType, mixed $value): mixed
    {
        if ($propertyType->isBuiltin()) {
            if (! settype($value, $propertyType->getName())) {
                throw new TransformException("Unable to cast '$value' into a '{$propertyType->getName()}'");
            }
        }

        return $value;
    }
}
