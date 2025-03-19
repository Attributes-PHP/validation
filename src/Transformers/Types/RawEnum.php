<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Property;
use Attributes\Validation\Transformers\PropertyTransformer;
use ReflectionEnum;
use ReflectionProperty;
use Throwable;

class RawEnum implements TypeCast
{
    private string $type;

    private PropertyTransformer $propertyTransformer;

    public function __construct(string $type, PropertyTransformer $propertyTransformer)
    {
        $this->type = $type;
        $this->propertyTransformer = $propertyTransformer;
    }

    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. True for strict casting or else otherwise
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value, bool $strict): string
    {
        if (!enum_exists($this->type)) {
            throw new TransformException('Enum \'' . $this->type . '\' does not exist.');
        }

        try {
            $reflectionEnum = new ReflectionEnum($this->type);
            $typeHintName = $reflectionEnum->getBackingType()->getName() ?: 'string';
            new Property(new ReflectionProperty($this->type), $value);
            return $this->propertyTransformer->transform()
            return (string) $value;
        } catch (Throwable $e) {
            throw new TransformException('Invalid string', previous: $e);
        }
    }
}
