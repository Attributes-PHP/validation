<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;

class RawBool implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. True for strict casting or else otherwise
     * @return mixed - Value properly cast
     *
     * @throws TransformException - If unable to
     */
    public function cast(mixed $value, bool $strict): bool
    {
        if ($strict) {
            return is_bool($value) ? $value : throw new TransformException('Invalid boolean');
        }

        $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($filteredValue === null) {
            throw new TransformException('Invalid boolean');
        }

        return $filteredValue;
    }
}
