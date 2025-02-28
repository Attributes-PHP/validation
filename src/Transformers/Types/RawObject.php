<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;

class RawObject implements TypeCast
{
    /**
     * Casts a given value into aan object
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. True for strict casting or else otherwise
     *
     * @throws TransformException
     */
    public function cast(mixed $value, bool $strict): object
    {
        return is_object($value) || is_array($value) ? (object) $value : throw new TransformException('Invalid array');
    }
}
