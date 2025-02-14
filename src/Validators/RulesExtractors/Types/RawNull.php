<?php

/**
 * Holds logic validation rules used to verify if a given value is null
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Validators\RulesExtractors\RulesContainer;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawNull implements TypeRespectExtractor
{
    private RulesContainer $rulesContainer;

    public function __construct(RulesContainer $rulesContainer)
    {
        $this->rulesContainer = $rulesContainer;
    }

    /**
     * Retrieves the validation rules to check if a value is null
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  string  $typeHint  - The exact type-hint. Useful for more complex ones e.g. classes
     *
     * @throws ValidationException
     */
    public function extract(bool $strict, string $typeHint): Validatable
    {
        $allRules = $this->rulesContainer->getRules();
        $ruleExtractor = $allRules[$typeHint] ?? $allRules['default'];
        $rule = $ruleExtractor->extract($strict, $typeHint);

        return $strict ? new Rules\Nullable($rule) : new Rules\Optional($rule);
    }
}
