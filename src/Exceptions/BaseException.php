<?php

declare(strict_types=1);

namespace Attributes\Validation\Exceptions;

use Attributes\Validation\ErrorHolder;
use Exception;
use Throwable;

abstract class BaseException extends Exception
{
    private ?ErrorHolder $errorHolder;

    public function __construct(string $message, ?ErrorHolder $errorHolder = null, ?Throwable $previous = null)
    {
        $this->errorHolder = $errorHolder;
        parent::__construct($message, 0, $previous);
    }

    public function getErrors(): array
    {
        return $this->errorHolder ? $this->errorHolder->getErrors() : [];
    }
}
