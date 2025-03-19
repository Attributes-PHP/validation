<?php

/**
 * Holds logic validation rules used to verify if a given value is null
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Validators\RulesExtractors\PropertiesContainer;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawNull implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is null
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  PropertiesContainer  $propertiesContainer  - Additional properties which could influence the validation rules
     *
     * @throws ValidationException
     */
    public function extract(bool $strict, PropertiesContainer $propertiesContainer): Validatable
    {
        $typeHint = $propertiesContainer->getProperty('typeHint');
        $typeHintRulesExtractor = $propertiesContainer->getProperty('typeHintRulesExtractor');
        $allRules = $typeHintRulesExtractor->getRules();
        $ruleExtractor = $allRules[$typeHint] ?? $allRules['default'];
        $rule = $ruleExtractor->extract($strict, $propertiesContainer);

        return $strict ? new Rules\Nullable($rule) : new Rules\Optional($rule);
    }
}
