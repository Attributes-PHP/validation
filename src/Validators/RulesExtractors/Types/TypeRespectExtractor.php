<?php

/**
 * Holds interface for casting a given value
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ValidationException;
use Respect\Validation\Validatable;

interface TypeRespectExtractor
{
    /**
     * Extracts validation rules from a given type hint
     *
     * @param  Context  $context  - Validation context
     *
     * @throws ValidationException
     */
    public function extract(Context $context): Validatable;
}
