<?php

/**
 * Holds logic validation rules used to verify if a given value is null
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawNull implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is null
     *
     * @param  Context  $context  - Validation context
     *
     * @throws ValidationException
     */
    public function extract(Context $context): Validatable
    {
        $typeHint = $context->getLocal('property.typeHint');
        $allRules = $context->getLocal('option.typeHintRules');
        $ruleExtractor = $allRules[$typeHint] ?? $allRules['default'];
        $rule = $ruleExtractor->extract($context);

        return $context->getGlobal('option.strict') ? new Rules\Nullable($rule) : new Rules\Optional($rule);
    }
}
