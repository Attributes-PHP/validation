<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Validators\RulesExtractors\Types\TypeRespectExtractor;

interface RulesContainer
{
    /**
     * Retrieves the rules used for validating a given type-hint
     *
     * @return array<string,TypeRespectExtractor> - Type-hint mapping of validation rules
     */
    public function getRules(): array;
}
