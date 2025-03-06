<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Exceptions\TransformException;
use DateTime as BaseDateTime;
use DateTimeInterface;
use Throwable;

class DateTime implements TypeCast
{
    private string $format;

    public function __construct(string $format = DateTimeInterface::ATOM)
    {
        $this->format = $format;
    }

    /**
     * Casts a given value into a given type
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. For DateTime this is ignored.
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     */
    public function cast(mixed $value, bool $strict): BaseDateTime
    {
        if ($value instanceof BaseDateTime) {
            return $value;
        }

        try {
            if ($value instanceof DateTimeInterface) {
                return BaseDateTime::createFromInterface($value);
            }

            return BaseDateTime::createFromFormat($this->format, (string) $value);
        } catch (Throwable $e) {
            throw new TransformException('Invalid datetime', previous: $e);
        }
    }
}
