<?php

declare(strict_types=1);

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\TransformException;
use DateTime as BaseDateTime;
use DateTimeInterface;
use Throwable;

class DateTime implements TypeCast
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
    public function cast(mixed $value, Context $context): BaseDateTime
    {
        if ($value instanceof BaseDateTime) {
            return $value;
        }

        try {
            if ($value instanceof DateTimeInterface) {
                return BaseDateTime::createFromInterface($value);
            }

            $format = $context->getOptionalGlobal('datetime.format', DateTimeInterface::ATOM);
            $timezone = $context->getOptionalGlobal('datetime.timezone');

            return BaseDateTime::createFromFormat($format, (string) $value, $timezone);
        } catch (Throwable $e) {
            throw new TransformException('Invalid datetime', previous: $e);
        }
    }
}
