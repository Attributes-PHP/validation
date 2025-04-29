<?php

declare(strict_types=1);

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\TransformException;

class RawObject implements TypeCast
{
    /**
     * Casts a given value into aan object
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     *
     * @throws TransformException
     */
    public function cast(mixed $value, Context $context): object
    {
        return is_object($value) || is_array($value) ? (object) $value : throw new TransformException('Invalid object/array');
    }
}
