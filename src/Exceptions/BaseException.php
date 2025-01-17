<?php

namespace Attributes\Validation\Exceptions;

use Exception;
use Throwable;

abstract class BaseException extends Exception
{
    private array $propertyErrors;

    public function __construct(string $message, array $propertyErrors = [], ?Throwable $previous = null)
    {
        $this->propertyErrors = $propertyErrors;
        parent::__construct($message, 0, $previous);
    }

    public function getPropertyErrors(): array
    {
        return $this->propertyErrors;
    }
}
