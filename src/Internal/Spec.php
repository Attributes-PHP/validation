<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

/**
 * A resolved Sequence, Union, Intersection or Dict attribute: the arms of
 * the value positions, the Dict key arms and the intersection flag.
 *
 * @internal
 */
final class Spec
{
    /**
     * @var array<int, Arm>
     */
    public array $arms = [];

    /**
     * @var array<int, Arm>|null
     */
    public ?array $keyArms = null;

    public bool $isIntersection = false;
}
