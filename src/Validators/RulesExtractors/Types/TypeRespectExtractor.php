<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\ValidationException;
use Respect\Validation\Validatable;

interface TypeRespectExtractor
{
    /**
     * Extracts validation rules from a given type hint
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  string  $typeHint  - The exact type-hint. Useful for more complex ones e.g. classes
     *
     * @throws ValidationException
     */
    public function extract(bool $strict, string $typeHint): Validatable;
}
