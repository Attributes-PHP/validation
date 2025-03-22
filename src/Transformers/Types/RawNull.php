<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;

class RawNull implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Value properly cast
     *
     * @throws ContextPropertyException - When unable to find
     * @throws TransformException
     */
    public function cast(mixed $value, Context $context): mixed
    {
        $cast = $context->getLocal(TypeCast::class);
        if ($context->getGlobal('option.strict')) {
            return is_null($value) ? $value : $cast->cast($value, $context);
        }

        return is_null($value) || $value === '' ? null : $cast->cast($value, $context);
    }
}
