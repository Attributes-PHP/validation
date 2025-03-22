<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Transformers\PropertyTransformer;
use ReflectionEnum;
use Throwable;

class RawEnum implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     * @throws ContextPropertyException
     */
    public function cast(mixed $value, Context $context): string
    {
        $typeHint = $context->getLocal('property.typeHint');
        if (! enum_exists($typeHint)) {
            throw new TransformException('Enum \''.$typeHint.'\' does not exist.');
        }

        $transformer = $context->getLocal(PropertyTransformer::class);
        try {
            $reflectionEnum = new ReflectionEnum($typeHint);
            $typeHintName = $reflectionEnum->getBackingType()->getName() ?: 'string';
            $cast = $transformer->getTypeCastInstance($typeHintName);
            $value = $cast->cast($value, $context);
            if (is_string($value)) {
                return $reflectionEnum->getCase($value)->getValue();
            }

            foreach ($typeHint::getCases() as $case) {
                if ($case->value == $value) {
                    return $case;
                }
            }
        } catch (Throwable $e) {
            throw new TransformException('Invalid enum', previous: $e);
        }

        throw new TransformException('Invalid enum');
    }
}
