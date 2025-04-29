<?php

/**
 * Holds logic validation rules used to verify if a given value is of a given interface
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class StrictType implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a given value is of a given instance
     *
     * @param  Context  $context  - Validation context
     *
     * @throws ContextPropertyException
     */
    public function extract(Context $context): Validatable
    {
        $propertyTypeHint = $context->getLocal('property.typeHint');

        return new Rules\Instance($propertyTypeHint);
    }
}
