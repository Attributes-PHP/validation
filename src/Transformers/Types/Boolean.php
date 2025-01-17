<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;

class Boolean implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value): bool
    {
        $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($filteredValue === null) {
            throw new TransformException('Invalid boolean');
        }

        return $filteredValue;
    }
}
