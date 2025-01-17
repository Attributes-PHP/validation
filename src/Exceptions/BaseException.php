<?php

namespace Attributes\Validation\Exceptions;

use Attributes\Validation\ValidationResult;
use Exception;
use Throwable;

abstract class BaseException extends Exception
{
    private ValidationResult $validationResult;

    public function __construct(string $message, ?ValidationResult $result = null, ?Throwable $previous = null)
    {
        $this->validationResult = $result;
        parent::__construct($message, 0, $previous);
    }

    public function getValidationResult(): ValidationResult
    {
        return $this->validationResult;
    }
}
