<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Attributes\Validation\Fields\Alias;
use ReflectionProperty;

/**
 * Resolves the raw-data field name of a property: the Alias attribute wins,
 * then the ModelConfigs alias generator, then the property name.
 *
 * @internal
 */
final class FieldNameResolver
{
    public static function resolve(ReflectionProperty $property, ?string $generator): string
    {
        $attributes = $property->getAttributes(Alias::class);

        if ($attributes !== []) {
            $arguments = $attributes[0]->getArguments();
            $name = $arguments[0] ?? $arguments['name'] ?? null;

            if (! is_string($name)) {
                throw new \TypeError(sprintf(
                    'Attributes\Validation\Fields\Alias::__construct(): Argument #1 ($name) must be of type string, %s given',
                    Value::typeName($name)
                ));
            }

            return $name;
        }

        return match ($generator) {
            'pascal' => CaseConvert::pascal($property->getName()),
            'camel' => CaseConvert::camel($property->getName()),
            'snake' => CaseConvert::snake($property->getName()),
            'kebab' => CaseConvert::kebab($property->getName()),
            default => $property->getName(),
        };
    }
}
