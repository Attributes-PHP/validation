<?php

declare(strict_types=1);

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;

class RawBool implements TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     * @throws ContextPropertyException - When unable to find context properties
     */
    public function cast(mixed $value, Context $context): bool
    {
        if ($context->getGlobal('option.strict')) {
            return is_bool($value) ? $value : throw new TransformException('Invalid boolean');
        }

        $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($filteredValue === null) {
            throw new TransformException('Invalid boolean');
        }

        return $filteredValue;
    }
}
