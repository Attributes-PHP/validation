<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid boolean
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawBool implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is a valid boolean
     *
     * @param  Context  $context  - Validation context
     */
    public function extract(Context $context): Validatable
    {
        return $context->getGlobal('option.strict') ? new Rules\BoolType : new Rules\AllOf(new Rules\BoolVal, new Rules\NotOptional);
    }
}
