<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid datetime
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ValidationException;
use DateTimeInterface;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class DateTime implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is a valid datetime
     *
     * @param  Context  $context  - Validation context
     *
     * @throws ValidationException
     */
    public function extract(Context $context): Validatable
    {
        $format = $context->getOptionalProperty('datetime.format', DateTimeInterface::ATOM);

        return new Rules\AnyOf(new Rules\DateTime(format: $format), new Rules\Instance(DateTimeInterface::class));
    }
}
