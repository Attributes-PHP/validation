<?php

declare(strict_types=1);

namespace Attributes\Validation\Fields;

use Attributes\Validation\Internal\Value;
use Attributes\Validation\Type;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class Dict implements Field
{
    public mixed $key;

    public mixed $of;

    public function __construct(mixed $key, mixed $of)
    {
        if (! (is_int($key) || $key instanceof Type || $key instanceof Union)) {
            throw new \TypeError(sprintf(
                'Dict::__construct(): Argument #1 ($key) must be of type int|\Attributes\Validation\Type|Union, %s given',
                Value::typeName($key)
            ));
        }

        if (! (is_int($of) || is_string($of) || $of instanceof Type
            || $of instanceof Union || $of instanceof Intersection || $of instanceof Sequence || $of instanceof Dict)) {
            throw new \TypeError(sprintf(
                'Dict::__construct(): Argument #2 ($of) must be of type int|string|\Attributes\Validation\Type|Union|Intersection|Sequence|Dict, %s given',
                Value::typeName($of)
            ));
        }

        if (is_string($of) && ! Value::isResolvableClassName($of)) {
            throw new \ValueError(sprintf(
                'Dict::__construct(): Argument #2 ($of) must be a valid class, interface or enum, "%s" given',
                $of
            ));
        }

        $this->key = $key;
        $this->of = $of;
    }
}
