<?php

/*
 * Copyright (c) Alexandre Gomes Gaigalas <alganet@gmail.com>
 * SPDX-License-Identifier: MIT
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Rules;

use Respect\Validation\Exceptions\AnyOfException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules\Core\Composite;
use Respect\Validation\Validatable;

use function count;

/**
 * @author Alexandre Gomes Gaigalas <alganet@gmail.com>
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
final class Union extends Composite
{
    private array $mapping;

    private ?string $validTypeHint = null;

    public function __construct(array $mapping, Validatable $rule1, Validatable $rule2, Validatable ...$rules)
    {
        $this->mapping = $mapping;
        parent::__construct($rule1, $rule2, ...$rules);
    }

    /**
     * @deprecated Calling `assert()` directly from rules is deprecated. Please use {@see \Respect\Validation\Validator::assert()} instead.
     */
    public function assert($input): void
    {
        $validators = $this->getRules();
        $exceptions = $this->getAllThrownExceptions($input);
        $numRules = count($validators);
        $numExceptions = count($exceptions);
        if ($numExceptions === $numRules) {
            /** @var AnyOfException $anyOfException */
            $anyOfException = $this->reportError($input);
            $anyOfException->addChildren($exceptions);

            throw $anyOfException;
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

    public function getValidTypeHint(): ?string
    {
        return $this->validTypeHint;
    }
}
