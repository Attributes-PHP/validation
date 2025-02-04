<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;
use Throwable;

class RawInt implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. True for strict casting or else otherwise
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value, bool $strict): mixed
    {
        if ($strict) {
            return is_int($value) ? $value : throw new TransformException('Invalid integer');
        }

        try {
            return $value + 0;
        } catch (Throwable $e) {
            throw new TransformException('Invalid integer', previous: $e);
        }
    }
}
