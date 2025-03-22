<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;

class StrictType implements TypeCast
{
    /**
     * Checks if a given value has the expected type
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     * @throws ContextPropertyException - When unable to fiend context property
     */
    public function cast(mixed $value, Context $context): mixed
    {
        $typeHint = $context->getLocal('property.typeHint');

        return is_a($value, $typeHint) ? $value : throw new TransformException("Invalid $typeHint");
    }
}
