<?php

declare(strict_types=1);

namespace Attributes\Validation\Fields;

use Attributes\Validation\Internal\Value;
use Attributes\Validation\Type;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class Sequence implements Field
{
    public mixed $of;

    public function __construct(mixed $of)
    {
        if (! (is_int($of) || is_string($of) || $of instanceof Type
            || $of instanceof Sequence || $of instanceof Union || $of instanceof Intersection || $of instanceof Dict)) {
            throw new \TypeError(sprintf(
                'Sequence::__construct(): Argument #1 ($of) must be of type int|string|\Attributes\Validation\Type|Sequence|Union|Intersection|Dict, %s given',
                Value::typeName($of)
            ));
        }

        if (is_string($of) && ! Value::isResolvableClassName($of)) {
            throw new \ValueError(sprintf(
                'Sequence::__construct(): Argument #1 ($of) must be a valid class, interface or enum, "%s" given',
                $of
            ));
        }

        $this->of = $of;
    }
}
