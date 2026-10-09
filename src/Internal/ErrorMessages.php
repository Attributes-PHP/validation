<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Attributes\Validation\Fields\ErrorMessage;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

/**
 * Error message templates, placeholder expansion and expected-type strings.
 *
 * @internal
 */
final class ErrorMessages
{
    public const REQUIRED = 'required';

    public const TYPE = 'type';

    private const DEFAULTS = [
        self::REQUIRED => 'Field is required',
        self::TYPE => 'Must be {expected}',
    ];

    /**
     * Adds an error for a field to the error collection.
     */
    public static function addFieldError(
        string $type,
        ?string $parentPath,
        string $fieldName,
        ?ReflectionProperty $property,
        mixed $value,
        array &$errors,
        ?string $expectedOverride = null,
    ): void {
        $template = self::template($type, $property);
        $message = self::replacePlaceholders($template, $fieldName, $value, $property, $expectedOverride);
        $path = ($parentPath === null || $parentPath === '') ? $fieldName : $parentPath.'.'.$fieldName;

        $errors[$path][] = $message;
    }

    /**
     * Renders the expected type string of a property type hint, mirroring
     * the engine's rendering of unions, classes and enum cases.
     */
    public static function expectedForHint(ReflectionType $type): string
    {
        if ($type instanceof \ReflectionIntersectionType) {
            return 'mixed';
        }

        $arms = $type instanceof ReflectionUnionType ? $type->getTypes() : [$type];

        $basicMask = 0;
        $named = [];

        foreach ($arms as $arm) {
            if (! $arm instanceof ReflectionNamedType) {
                continue;
            }

            if ($arm->allowsNull()) {
                $basicMask |= Masks::NULL;
            }

            if ($arm->isBuiltin()) {
                $basicMask |= Masks::forName($arm->getName());

                continue;
            }

            $named[] = $arm->getName();
        }

        $basicNames = [];
        $basicTotal = 0;

        foreach (Masks::BASIC_NAMES as [$mask, $name]) {
            if ($basicMask & $mask) {
                $basicNames[] = $name;
                $basicTotal++;
            }
        }

        $hasEnum = false;

        foreach ($named as $name) {
            if (enum_exists($name)) {
                $hasEnum = true;
            }
        }

        // Basic types only
        if ($named === []) {
            if ($basicTotal <= 1) {
                foreach ($arms as $arm) {
                    return self::typeName($arm, $basicMask);
                }
            }

            return self::join($basicNames);
        }

        // Class types only: class names or-joined
        if ($basicTotal === 0 && ! $hasEnum) {
            return implode(' or ', $named);
        }

        // Mixed basic, class and enum types: basic names first, then named
        // types in declaration order; enums expand to one part per case
        $parts = $basicNames;

        foreach ($named as $name) {
            if (enum_exists($name)) {
                foreach ((new \ReflectionEnum($name))->getCases() as $case) {
                    $enumValue = $case->getValue();
                    $parts[] = Value::toString($enumValue instanceof \BackedEnum ? $enumValue->value : $enumValue->name);
                }

                continue;
            }

            $parts[] = $name;
        }

        return self::join($parts);
    }

    private static function typeName(ReflectionNamedType $arm, int $basicMask): string
    {
        if ($arm->isBuiltin()) {
            $mask = Masks::forName($arm->getName());

            if ($mask !== 0 && ($basicMask & $mask) === $mask) {
                foreach (Masks::BASIC_NAMES as [$basicMaskEntry, $name]) {
                    if ($basicMaskEntry === $mask) {
                        return $name;
                    }
                }
            }

            return 'mixed';
        }

        return $arm->getName();
    }

    /**
     * Joins type names with ", " and a final " or ".
     */
    private static function join(array $parts): string
    {
        $total = count($parts);

        if ($total === 0) {
            return 'mixed';
        }

        $result = '';

        foreach ($parts as $index => $part) {
            if ($index > 0) {
                $result .= $index + 1 === $total ? ' or ' : ', ';
            }

            $result .= $part;
        }

        return $result;
    }

    /**
     * Returns the error message template for an error type: the custom
     * template declared with #[ErrorMessage] when the property carries one,
     * the default template otherwise.
     */
    private static function template(string $type, ?ReflectionProperty $property): string
    {
        if ($property === null) {
            return self::DEFAULTS[$type];
        }

        $attributes = $property->getAttributes(ErrorMessage::class);

        if ($attributes === []) {
            return self::DEFAULTS[$type];
        }

        $argumentName = $type;
        $argumentPosition = $type === self::REQUIRED ? 0 : 1;
        $position = 0;

        foreach ($attributes[0]->getArguments() as $name => $value) {
            if (is_int($name)) {
                if ($position !== $argumentPosition) {
                    $position++;

                    continue;
                }
            } elseif (strcasecmp((string) $name, $argumentName) !== 0) {
                continue;
            }

            if (is_string($value)) {
                return $value;
            }

            if ($value !== null) {
                throw new \ValueError('Only string arguments are valid for Attributes\Validation\Fields\ErrorMessage::construct(...)');
            }

            break;
        }

        return self::DEFAULTS[$type];
    }

    /**
     * Substitutes the {field}, {value} and {expected} placeholders of an
     * error message template.
     */
    private static function replacePlaceholders(
        string $template,
        string $fieldName,
        mixed $value,
        ?ReflectionProperty $property,
        ?string $expectedOverride,
    ): string {
        $expected = $expectedOverride;

        if ($expected === null && str_contains($template, '{expected}')) {
            $expected = self::expectedForHint($property->getType());
        }

        return str_replace(
            ['{field}', '{value}', '{expected}'],
            [$fieldName, Value::toString($value), $expected ?? ''],
            $template
        );
    }
}
