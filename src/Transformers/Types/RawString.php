<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;
use Throwable;

class RawString implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     * @throws ContextPropertyException - When unable to find context property
     */
    public function cast(mixed $value, Context $context): string
    {
        if ($context->getGlobal('option.strict')) {
            return is_string($value) ? $value : throw new TransformException('Invalid string');
        }

        try {
            return (string) $value;
        } catch (Throwable $e) {
            throw new TransformException('Invalid string', previous: $e);
        }
    }
}
