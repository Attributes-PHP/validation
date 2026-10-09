<?php

declare(strict_types=1);

namespace Attributes\Validation\Exceptions;

class ValidationException extends BaseException
{
    private array $allErrors = [];

    public function __construct(array $allErrors)
    {
        $this->allErrors = $allErrors;

        parent::__construct('Invalid data');
    }

    public function getErrors(): array
    {
        return $this->allErrors;
    }
}
