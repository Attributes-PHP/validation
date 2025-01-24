<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators\Rules;

use Respect\Validation\Exceptions\AllOfException;

use function count;

class Intersection extends InternalType
{
    public function assert($input): void
    {
        $exceptions = $this->getAllThrownExceptions($input);
        $numRules = count($this->getRules());
        $numExceptions = count($exceptions);
        $summary = [
            'total' => $numRules,
            'failed' => $numExceptions,
            'passed' => $numRules - $numExceptions,
        ];
        if (! empty($exceptions)) {
            /** @var AllOfException $allOfException */
            $allOfException = $this->reportError($input, $summary);
            $allOfException->addChildren($exceptions);

            throw $allOfException;
        }
    }
}
