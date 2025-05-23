<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators\Rules\Exceptions;

use Respect\Validation\Exceptions\NestedValidationException;

final class UnionException extends NestedValidationException
{
    /**
     * {@inheritDoc}
     */
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Only one of these rules must pass for {{name}}',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Only one of these rules must not pass for {{name}}',
        ],
    ];
}
