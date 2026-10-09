<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Attributes\Validation\BaseModel;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * Validates a raw value against a property's type hint, with the same arm
 * ordering semantics as the engine: exact matches win over coercions, and
 * the builtin arms of a union collapse into one basic mask whose loose
 * verification prefers boolean, then integer, float, string.
 *
 * Type hints are compiled into TypePlan instances once per property, so the
 * arm walk and the class resolution do not repeat per validated value.
 *
 * @internal
 */
final class TypeHintValidator
{
    /**
     * @var array<string, TypePlan>
     */
    private static array $planCache = [];

    /**
     * The compiled class flags: DateTime-like and BaseModel subclass.
     *
     * @var array<string, array{dateTime: bool, baseModel: bool}>
     */
    private static array $classFlags = [];

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
        ?TypePlan $plan = null,
    ): array {
        $plan ??= self::plan($property);

        if ($plan->untyped) {
            return ['ok' => true, 'value' => $value];
        }

        $typeMask = Masks::of($value);
        $exact = $plan->mixed
            || ($typeMask & $plan->basicMask) !== 0
            || ($plan->iterable && (is_array($value) || $value instanceof \Traversable))
            || ($plan->classes !== [] && is_object($value) && self::instanceofAny($value, $plan->classes));

        if ($exact) {
            if (is_array($value)) {
                $ok = ArraySpecValidator::validateArray($value, $property, $configs, $errors, $parentPath, $fieldName);

                return ['ok' => $ok, 'value' => $value];
            }

            return ['ok' => true, 'value' => $value];
        }

        if ($plan->basicMask !== 0) {
            if (! $configs->strict && ($plan->basicMask & Masks::BOOL) !== 0) {
                $bool = Coercion::coerceBool($value);

                if ($bool !== null) {
                    return ['ok' => true, 'value' => $bool];
                }
            } else {
                $coerced = Coercion::verifyScalar($value, $plan->basicMask, $configs->strict);

                if ($coerced !== null) {
                    return ['ok' => true, 'value' => $coerced];
                }
            }
        }

        if (is_object($value)) {
            foreach ($plan->intersections as $members) {
                $matches = true;

                foreach ($members as $member) {
                    if (! $value instanceof $member) {
                        $matches = false;

                        break;
                    }
                }

                if ($matches) {
                    return ['ok' => true, 'value' => $value];
                }
            }
        }

        foreach ($plan->classes as $arm) {
            $handled = self::handleClass($value, $arm, $configs, $errors, $parentPath, $fieldName);

            if ($handled !== null) {
                return ['ok' => true, 'value' => $handled];
            }
        }

        if (! $plan->singleBaseModel) {
            ErrorMessages::addFieldError(ErrorMessages::TYPE, $parentPath, $fieldName, $property, $value, $errors);
        }

        return ['ok' => false, 'value' => $value];
    }

    /**
     * Handles a class type arm: accepts existing instances, coerces strings
     * for DateTime-like classes and hydrates raw arrays into nested
     * BaseModel instances.
     *
     * Returns the accepted value, or null when the arm rejects the value.
     *
     * @param  array{class: string, dateTime: bool, baseModel: bool}  $arm
     */
    public static function handleClass(
        mixed $value,
        array|string $arm,
        ResolvedConfigs $configs,
        array &$errors,
        ?string $parentPath,
        string $fieldName,
    ): mixed {
        $arm = is_string($arm) ? self::classFlags($arm) : $arm;

        if ($arm['dateTime'] && is_string($value) && strlen($value) > 12) {
            $datetime = date_create_from_format('X-m-d\TH:i:sP', $value);

            if ($datetime !== false) {
                return $datetime;
            }
        }

        $class = $arm['class'];

        if (is_object($value) && $value instanceof $class) {
            return $value;
        }

        if ($arm['baseModel'] && is_array($value)) {
            $model = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            $nestedPath = ($parentPath === null || $parentPath === '') ? $fieldName : $parentPath.'.'.$fieldName;

            if (Hydrator::validateModel($value, $model, $configs, $errors, $nestedPath)) {
                return $model;
            }
        }

        return null;
    }

    /**
     * Compiles the type hint of a property into a plan, cached per
     * property.
     */
    public static function plan(ReflectionProperty $property): TypePlan
    {
        $cacheKey = $property->getDeclaringClass()->getName().'::$'.$property->getName();

        if (isset(self::$planCache[$cacheKey])) {
            return self::$planCache[$cacheKey];
        }

        $plan = new TypePlan;
        $type = $property->getType();

        if ($type === null) {
            $plan->untyped = true;

            return self::$planCache[$cacheKey] = $plan;
        }

        $singleNamed = null;
        $arms = $type instanceof ReflectionUnionType ? $type->getTypes() : [$type];

        foreach ($arms as $arm) {
            if ($arm instanceof ReflectionIntersectionType) {
                $members = [];

                foreach ($arm->getTypes() as $member) {
                    $members[] = self::resolveClassName($member->getName(), $property);
                }

                // An unresolvable member can never match, so the whole
                // intersection arm contributes no match
                if (! in_array(null, $members, true)) {
                    $plan->intersections[] = $members;
                }

                continue;
            }

            if ($arm->allowsNull()) {
                $plan->basicMask |= Masks::NULL;
            }

            $name = $arm->getName();

            if ($arm->isBuiltin()) {
                if ($name === 'mixed') {
                    $plan->mixed = true;
                } elseif ($name === 'iterable') {
                    $plan->iterable = true;
                    $plan->basicMask |= Masks::ARRAY;
                } else {
                    $plan->basicMask |= Masks::forName($name);
                }

                continue;
            }

            $singleNamed = $singleNamed === null ? $name : $singleNamed;
            $class = self::resolveClassName($name, $property);

            if ($class !== null) {
                $flags = self::classFlags($class);
                $plan->classes[] = ['class' => $class, 'dateTime' => $flags['dateTime'], 'baseModel' => $flags['baseModel']];
            }
        }

        if (count($arms) === 1 && $singleNamed !== null) {
            $plan->singleBaseModel = $plan->classes !== [] && $plan->classes[0]['baseModel'];
        }

        return self::$planCache[$cacheKey] = $plan;
    }

    /**
     * Whether an object matches one of the class arms.
     *
     * @param  list<array{class: string, dateTime: bool, baseModel: bool}>  $classes
     */
    private static function instanceofAny(object $value, array $classes): bool
    {
        foreach ($classes as $arm) {
            if ($value instanceof $arm['class']) {
                return true;
            }
        }

        return false;
    }

    /**
     * The compiled flags of a class: DateTime-like and BaseModel subclass.
     *
     * @return array{dateTime: bool, baseModel: bool}
     */
    private static function classFlags(string $class): array
    {
        if (isset(self::$classFlags[$class])) {
            return self::$classFlags[$class];
        }

        return self::$classFlags[$class] = [
            'class' => $class,
            'dateTime' => $class === \DateTime::class
                || $class === \DateTimeInterface::class
                || is_subclass_of($class, \DateTimeInterface::class),
            'baseModel' => $class !== BaseModel::class && is_subclass_of($class, BaseModel::class),
        ];
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
}
