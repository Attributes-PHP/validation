<?php

declare(strict_types=1);

namespace Attributes\Validation;

final class ValidationResult
{
    public function __construct(
        public readonly bool $isValid,
        public readonly ?string $error = null,
        public readonly bool $shouldContinue = true,
        public readonly bool $shouldStop = false,
    ) {}

    public static function valid(): self
    {
        return new self(true);
    }

    public static function invalid(string $error): self
    {
        return new self(false, $error, true, false);
    }

    public static function stop(string $error): self
    {
        return new self(false, $error, false, true);
    }

    public function isInvalid(): bool
    {
        return ! $this->isValid;
    }
}
