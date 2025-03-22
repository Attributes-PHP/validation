<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid float
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawFloat implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is a valid float
     *
     * @param  Context  $context  - Validation context
     */
    public function extract(Context $context): Validatable
    {
        return $context->getGlobal('option.strict') ? new Rules\FloatType : new Rules\AllOf(new Rules\FloatVal, new Rules\Not(new Rules\BoolType));
    }
}
