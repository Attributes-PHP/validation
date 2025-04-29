<?php

declare(strict_types=1);

namespace Attributes\Validation\Exceptions;

use Attributes\Validation\ErrorInfo;
use Exception;
use Throwable;

abstract class BaseException extends Exception
{
    private ?ErrorInfo $result;

    public function __construct(string $message, ?ErrorInfo $result = null, ?Throwable $previous = null)
    {
        $this->result = $result;
        parent::__construct($message, 0, $previous);
    }

    public function getInfo(): ?ErrorInfo
    {
        return $this->result;
    }
}
