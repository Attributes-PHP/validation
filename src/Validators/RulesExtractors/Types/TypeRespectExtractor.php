<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Validators\RulesExtractors\PropertiesContainer;
use Respect\Validation\Validatable;

interface TypeRespectExtractor
{
    /**
     * Extracts validation rules from a given type hint
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  PropertiesContainer  $propertiesContainer  - Additional properties which could influence the validation rules
     *
     * @throws ValidationException
     */
    public function extract(bool $strict, PropertiesContainer $propertiesContainer): Validatable;
}
