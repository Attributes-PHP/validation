<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators\Rules;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules\Core\Composite;
use Respect\Validation\Validatable;

/**
 * @internal
 */
abstract class InternalType extends Composite
{
    private array $mapping;

    private array $validTypeHints = [];

    public function __construct(array $mapping, Validatable $rule1, Validatable $rule2, Validatable ...$rules)
    {
        $this->mapping = $mapping;
        parent::__construct($rule1, $rule2, ...$rules);
    }

    /**
     * Returns all the exceptions throw when asserting all rules.
     *
     * @param  mixed  $input
     * @return ValidationException[]
     */
    protected function getAllThrownExceptions($input): array
    {
        return array_filter(
            array_map(
                function (Validatable $rule) use ($input): ?ValidationException {
                    try {
                        $rule->assert($input);
                        $this->addValidRule($rule);
                    } catch (ValidationException $exception) {
                        $this->updateExceptionTemplate($exception);

                        return $exception;
                    }

                    return null;
                },
                $this->getRules()
            )
        );
    }

    private function addValidRule(Validatable $rule): void
    {
        $this->validTypeHints[] = $this->mapping[$rule->getName()];
    }

    private function updateExceptionTemplate(ValidationException $exception): void
    {
        if ($this->template === null || $exception->hasCustomTemplate()) {
            return;
        }

        $exception->updateTemplate($this->template);

        if (! $exception instanceof NestedValidationException) {
            return;
        }

        foreach ($exception->getChildren() as $childException) {
            $this->updateExceptionTemplate($childException);
        }
    }

    /**
     * @deprecated Calling `validate()` directly from rules is deprecated. Please use {@see \Respect\Validation\Validator::isValid()} instead.
     */
    public function validate($input): bool
    {
        foreach ($this->getRules() as $v) {
            if ($v->validate($input)) {
                $this->validTypeHint = $this->mapping[$v->getName()];

                return true;
            }
        }

        return false;
    }

    /**
     * @deprecated Calling `check()` directly from rules is deprecated. Please use {@see \Respect\Validation\Validator::check()} instead.
     */
    public function check($input): void
    {
        foreach ($this->getRules() as $v) {
            try {
                $v->check($input);
                $this->validTypeHint = $this->mapping[$v->getName()];

                return;
            } catch (ValidationException $e) {
                if (! isset($firstException)) {
                    $firstException = $e;
                }
            }
        }

        if (isset($firstException)) {
            throw $firstException;
        }

        throw $this->reportError($input);
    }

    public function getValidTypeHints(): array
    {
        return $this->validTypeHints;
    }
}
