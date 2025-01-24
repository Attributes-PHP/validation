<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators\Rules\Exceptions;

use Respect\Validation\Exceptions\NestedValidationException;

final class IntersectionException extends NestedValidationException
{
    /**
     * {@inheritDoc}
     */
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'All of the rules must pass for {{name}}',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'All of the rules rules must not pass for {{name}}',
        ],
    ];
}
