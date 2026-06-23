<?php

declare(strict_types=1);

namespace AttributesValidation;

/**
 * Represents the result of a validation operation
 * Used to replace exception-based control flow with a more efficient approach
 */
final class ValidationResult
{
    public function __construct(
        public readonly bool $isValid,
        public readonly ?string $error = null,
        public readonly bool $shouldContinue = true,
        public readonly bool $shouldStop = false,
    ) {
    }

    /**
     * Create a valid result
     */
    public static function valid(): self
    {
        return new self(true);
    }

    /**
     * Create an invalid result that should continue validation
     */
    public static function invalid(string $error): self
    {
        return new self(false, $error, true, false);
    }

    /**
     * Create an invalid result that should stop validation
     */
    public static function stop(string $error): self
    {
        return new self(false, $error, false, true);
    }

    /**
     * Check if validation failed
     */
    public function isInvalid(): bool
    {
        return !$this->isValid;
    }
}