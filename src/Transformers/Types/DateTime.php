<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;
use DateTime as BaseDateTime;
use Exception;

class DateTime implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value): BaseDateTime
    {
        try {
            return new BaseDateTime($value);
        } catch (Exception $e) {
            throw new TransformException('Invalid datetime', previous: $e);
        }
    }
}
