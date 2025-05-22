<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators;

use Attributes\Validation\Context;
use Attributes\Validation\Property;
use Respect\Validation\Exceptions\ValidationException;

interface PropertyValidator
{
    /**
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - The current context
     *
     * @throws ValidationException - if value not valid
     */
    public function validate(Property $property, Context $context): void;
}
