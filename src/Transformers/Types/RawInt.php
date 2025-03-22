<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;
use Throwable;

class RawInt implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     * @throws ContextPropertyException - When unable to find context properties
     */
    public function cast(mixed $value, Context $context): mixed
    {
        if ($context->getGlobal('option.strict')) {
            return is_int($value) ? $value : throw new TransformException('Invalid integer');
        }

        try {
            return $value + 0;
        } catch (Throwable $e) {
            throw new TransformException('Invalid integer', previous: $e);
        }
    }
}
