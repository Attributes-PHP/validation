<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;
use Exception;

class Integer implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value): mixed
    {
        try {
            return $value + 0;
        } catch (Exception $e) {
            throw new TransformException('Invalid integer', previous: $e);
        }
    }
}
