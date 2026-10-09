<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Attributes\Validation\BaseModel;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

/**
 * Validates a raw value against a property's type hint, with the same arm
 * ordering semantics as the engine: exact matches win over coercions, and
 * the builtin arms of a union collapse into one basic mask whose loose
 * verification prefers boolean, then integer, float, string.
 *
 * @internal
 */
final class TypeHintValidator
{
    /**
     * Validates a value against the property type hint, coercing it when
     * needed. Returns ['ok' => bool, 'value' => mixed].
     */
    public static function validate(
        mixed $value,
        ReflectionProperty $property,
        ResolvedConfigs $configs,
        array &$errors,
        ?string $parentPath,
        string $fieldName,
    ): array {
        $type = $property->getType();

        if ($type === null) {
            return ['ok' => true, 'value' => $value];
        }

        if (self::exactMatch($type, $value)) {
            if (is_array($value)) {
                $ok = ArraySpecValidator::validateArray($value, $property, $configs, $errors, $parentPath, $fieldName);

                return ['ok' => $ok, 'value' => $value];
            }

            return ['ok' => true, 'value' => $value];
        }

        $basicMask = 0;
        $namedArms = [];
        $intersectionArms = [];

        foreach (self::arms($type) as $arm) {
            if ($arm instanceof ReflectionIntersectionType) {
                $intersectionArms[] = $arm;

                continue;
            }

            if ($arm->allowsNull()) {
                $basicMask |= Masks::NULL;
            }

            if ($arm->isBuiltin()) {
                $basicMask |= Masks::forName($arm->getName());

                continue;
            }

            $namedArms[] = $arm;
        }

        if ($basicMask !== 0) {
            if (! $configs->strict && ($basicMask & Masks::BOOL) !== 0) {
                $bool = Coercion::coerceBool($value);

                if ($bool !== null) {
                    return ['ok' => true, 'value' => $bool];
                }
            } else {
                $coerced = Coercion::verifyScalar($value, $basicMask, $configs->strict);

                if ($coerced !== null) {
                    return ['ok' => true, 'value' => $coerced];
                }
            }
        }

        foreach ($intersectionArms as $arm) {
            if (self::matchesIntersection($arm, $value, $property)) {
                return ['ok' => true, 'value' => $value];
            }
        }

        foreach ($namedArms as $arm) {
            $class = self::resolveClassName($arm->getName(), $property);

            if ($class === null) {
                continue;
            }

            $handled = self::handleClass($value, $class, $configs, $errors, $parentPath, $fieldName);

            if ($handled !== null) {
                return ['ok' => true, 'value' => $handled];
            }
        }

        if (! self::isBaseModelPropertyHint($type, $property)) {
            ErrorMessages::addFieldError(ErrorMessages::TYPE, $parentPath, $fieldName, $property, $value, $errors);
        }

        return ['ok' => false, 'value' => $value];
    }

    /**
     * Whether the value's type is already allowed by the hint, without any
     * coercion.
     */
    private static function exactMatch(ReflectionType $type, mixed $value): bool
    {
        foreach (self::arms($type) as $arm) {
            if (! $arm instanceof ReflectionNamedType) {
                continue;
            }

            $name = $arm->getName();

            if ($arm->allowsNull() && $value === null) {
                return true;
            }

            if ($arm->isBuiltin()) {
                if (match ($name) {
                    'bool' => is_bool($value),
                    'int' => is_int($value),
                    'float' => is_float($value),
                    'string' => is_string($value),
                    'array' => is_array($value),
                    'object' => is_object($value),
                    'null' => $value === null,
                    'false' => $value === false,
                    'true' => $value === true,
                    'mixed' => true,
                    'iterable' => is_array($value) || $value instanceof \Traversable,
                    default => false,
                }) {
                    return true;
                }

                continue;
            }

            if (is_object($value) && $value instanceof $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handles a class type arm: accepts existing instances, coerces strings
     * for DateTime-like classes and hydrates raw arrays into nested
     * BaseModel instances.
     *
     * Returns the accepted value, or null when the arm rejects the value.
     */
    public static function handleClass(
        mixed $value,
        string $class,
        ResolvedConfigs $configs,
        array &$errors,
        ?string $parentPath,
        string $fieldName,
    ): mixed {
        if (is_string($value) && self::isDateTimeClass($class) && strlen($value) > 12) {
            $datetime = date_create_from_format('X-m-d\TH:i:sP', $value);

            if ($datetime !== false) {
                return $datetime;
            }
        }

        if (is_object($value) && $value instanceof $class) {
            return $value;
        }

        if (is_array($value) && $class !== BaseModel::class && is_subclass_of($class, BaseModel::class)) {
            $model = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            $nestedPath = ($parentPath === null || $parentPath === '') ? $fieldName : $parentPath.'.'.$fieldName;

            if (Hydrator::validateModel($value, $model, $configs, $errors, $nestedPath)) {
                return $model;
            }
        }

        return null;
    }

    /**
     * Whether a value object matches every member of an intersection type.
     */
    private static function matchesIntersection(ReflectionIntersectionType $type, mixed $value, ReflectionProperty $property): bool
    {
        if (! is_object($value)) {
            return false;
        }

        foreach ($type->getTypes() as $arm) {
            $class = self::resolveClassName($arm->getName(), $property);

            if ($class === null || ! $value instanceof $class) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the property hint is a single BaseModel class, whose nested
     * hydration already reported the error.
     */
    private static function isBaseModelPropertyHint(ReflectionType $type, ReflectionProperty $property): bool
    {
        if ($type instanceof ReflectionUnionType || ! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return false;
        }

        $class = self::resolveClassName($type->getName(), $property);

        return $class !== null && is_subclass_of($class, BaseModel::class);
    }

    /**
     * Whether the class is DateTime or implements DateTimeInterface.
     */
    private static function isDateTimeClass(string $class): bool
    {
        return $class === \DateTime::class
            || $class === \DateTimeInterface::class
            || is_subclass_of($class, \DateTimeInterface::class);
    }

    /**
     * Resolves a type-hint class name against the declaring class.
     */
    private static function resolveClassName(string $name, ReflectionProperty $property): ?string
    {
        if (strcasecmp($name, 'self') === 0) {
            return $property->getDeclaringClass()->getName();
        }

        if (strcasecmp($name, 'parent') === 0) {
            $parent = $property->getDeclaringClass()->getParentClass();

            return $parent === false ? null : $parent->getName();
        }

        $name = ltrim($name, '\\');

        return Value::isResolvableClassName($name) ? $name : null;
    }

    /**
     * The named type arms of a type hint.
     *
     * @return array<int, ReflectionNamedType|ReflectionIntersectionType>
     */
    private static function arms(ReflectionType $type): array
    {
        if ($type instanceof ReflectionUnionType) {
            return $type->getTypes();
        }

        return [$type];
    }
}
