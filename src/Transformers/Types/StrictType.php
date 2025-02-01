<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;

class StrictType implements TypeCast
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Checks if a given value has the expected type
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. This is ignored
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value, bool $strict): mixed
    {
        return is_a($value, $this->type) ? $value : throw new TransformException("Invalid $this->type");
    }
}
