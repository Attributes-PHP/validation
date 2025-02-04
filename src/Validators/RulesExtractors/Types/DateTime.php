<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid datetime
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class DateTime implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is a valid datetime
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  string  $typeHint  - The exact type-hint. Useful for more complex ones e.g. classes
     */
    public function extract(bool $strict, string $typeHint): Validatable
    {
        return new Rules\DateTime;
    }
}
