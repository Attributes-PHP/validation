<?php

declare(strict_types=1);

namespace Attributes\Validation\Fields;

use Attributes\Validation\Internal\Value;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class Intersection implements Field
{
    public array $of = [];

    public function __construct(mixed ...$of)
    {
        $arms = array_values($of);

        foreach ($arms as $index => $arm) {
            if (! is_string($arm)) {
                throw new \TypeError(sprintf(
                    'Intersection::__construct(): Argument #%d must be of type string, %s given',
                    $index + 1,
                    Value::typeName($arm)
                ));
            }

            if (enum_exists($arm) || ! (class_exists($arm) || interface_exists($arm))) {
                throw new \ValueError(sprintf(
                    'Intersection::__construct(): Argument #%d must be a class or interface, "%s" given',
                    $index + 1,
                    $arm
                ));
            }
        }

        $this->of = $arms;
    }
}
