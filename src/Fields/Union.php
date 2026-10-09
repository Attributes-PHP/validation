<?php

declare(strict_types=1);

namespace Attributes\Validation\Fields;

use Attributes\Validation\Internal\Value;
use Attributes\Validation\Type;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class Union implements Field
{
    public array $of = [];

    public function __construct(mixed ...$of)
    {
        $arms = array_values($of);

        foreach ($arms as $index => $arm) {
            if (! (is_int($arm) || is_string($arm) || $arm instanceof Type
                || $arm instanceof Sequence || $arm instanceof Union || $arm instanceof Intersection || $arm instanceof Dict)) {
                throw new \TypeError(sprintf(
                    'Union::__construct(): Argument #%d must be of type int|string|\Attributes\Validation\Type|Sequence|Union|Intersection|Dict, %s given',
                    $index + 1,
                    Value::typeName($arm)
                ));
            }

            if (is_string($arm) && ! Value::isResolvableClassName($arm)) {
                throw new \ValueError(sprintf(
                    'Union::__construct(): Argument #%d must be a valid class, interface or enum, "%s" given',
                    $index + 1,
                    $arm
                ));
            }
        }

        $this->of = $arms;
    }
}
