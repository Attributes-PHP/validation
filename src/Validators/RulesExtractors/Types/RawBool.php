<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid boolean
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Validators\RulesExtractors\PropertiesContainer;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawBool implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is a valid boolean
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  PropertiesContainer  $propertiesContainer  - Additional properties which could influence the validation rules
     */
    public function extract(bool $strict, PropertiesContainer $propertiesContainer): Validatable
    {
        return $strict ? new Rules\BoolType : new Rules\AllOf(new Rules\BoolVal, new Rules\NotOptional);
    }
}
