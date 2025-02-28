<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use ArrayAccess;
use Attributes\Validation\Exceptions\TransformException;
use SimpleXMLElement;

class RawArray implements TypeCast
{
    /**
     * Casts a given value into an array
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. True for strict casting or else otherwise
     * @return array|ArrayAccess|SimpleXMLElement - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value, bool $strict): array|ArrayAccess|SimpleXMLElement
    {
        if ($strict) {
            return is_array($value) ? $value : throw new TransformException('Invalid array');
        }

        return is_array($value) || $value instanceof ArrayAccess || $value instanceof SimpleXMLElement ? $value : throw new TransformException('Invalid array');
    }
}
