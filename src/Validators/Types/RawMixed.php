<?php

/**
 * Holds logic to check if a given value is a mixed value
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Property;

final class RawMixed implements BaseType
{
    /**
     * Validates that a given property value is valid mixed value
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     */
    public function validate(Property $property, Context $context): void
    {
        // This is empty on purpose
    }
}
