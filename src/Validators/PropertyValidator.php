<?php

namespace Attributes\Validation\Validators;

use Attributes\Validation\Context;
use Attributes\Validation\Property;

interface PropertyValidator
{
    /**
     * Validates a given property
     *
     * @param  Property  $property  - The property to be validated
     */
    public function validate(Property $property, Context $context): void;
}
