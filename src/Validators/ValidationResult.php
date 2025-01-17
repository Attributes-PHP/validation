<?php

namespace Attributes\Validation\Validators;

use Attributes\Validation\Property;
use Exception;

interface ValidationResult
{
    public function __construct(Property $property);

    public function getErrors();

    public function hasErrors();

    public function addError(Exception $error);
}
