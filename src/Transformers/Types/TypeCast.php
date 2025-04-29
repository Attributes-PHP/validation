<?php

declare(strict_types=1);

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\TransformException;

interface TypeCast
{
    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value, Context $context): mixed;
}
