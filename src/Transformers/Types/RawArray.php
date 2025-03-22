<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use ArrayAccess;
use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;
use SimpleXMLElement;

class RawArray implements TypeCast
{
    /**
     * Casts a given value into an array
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return array|ArrayAccess|SimpleXMLElement - Value properly cast
     *
     * @throws TransformException
     * @throws ContextPropertyException - When unable to find context properties
     */
    public function cast(mixed $value, Context $context): array|ArrayAccess|SimpleXMLElement
    {
        if ($context->getGlobal('option.strict')) {
            return is_array($value) ? $value : throw new TransformException('Invalid array');
        }

        return is_array($value) || $value instanceof ArrayAccess || $value instanceof SimpleXMLElement ? $value : throw new TransformException('Invalid array');
    }
}
