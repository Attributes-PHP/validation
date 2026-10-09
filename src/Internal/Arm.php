<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

/**
 * One arm of an array type-hint attribute spec: a basic type mask, a
 * resolved class, or a nested array spec.
 *
 * @internal
 */
final class Arm
{
    public function __construct(
        public ?int $basicMask = null,
        public ?string $classString = null,
        public ?string $class = null,
        public ?Spec $nested = null,
    ) {}
}
