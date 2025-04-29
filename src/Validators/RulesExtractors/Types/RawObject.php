<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid object
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawObject implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is a valid object
     *
     * @param  Context  $context  - Validation context
     */
    public function extract(Context $context): Validatable
    {
        return new Rules\AnyOf(new Rules\ObjectType, new Rules\ArrayVal);
    }
}
