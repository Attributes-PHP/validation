<?php

/**
 * Holds interface for validating a given property
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Property;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;

interface BaseType
{
    /**
     * Validates a given property value
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If invalid
     */
    public function validate(Property $property, Context $context): void;
}
