<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;

class RawNull implements TypeCast
{
    private TypeCast $cast;

    public function __construct(TypeCast $cast)
    {
        $this->cast = $cast;
    }

    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. True for strict casting or else otherwise
     * @return mixed - Value properly cast
     *
     * @throws TransformException - If unable to
     */
    public function cast(mixed $value, bool $strict): mixed
    {
        if ($strict) {
            return is_null($value) ? $value : $this->cast->cast($value, true);
        }

        return is_null($value) || $value === '' ? null : $this->cast->cast($value, false);
    }
}
