<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Attributes\Validation\BaseModel;
use Attributes\Validation\Fields\Dict;
use Attributes\Validation\Fields\Intersection;
use Attributes\Validation\Fields\Sequence;
use Attributes\Validation\Fields\Union;
use Attributes\Validation\Type;
use ReflectionAttribute;
use ReflectionProperty;

/**
 * Validates the elements of a typed array property against its array
 * type-hint attribute (Sequence, Union, Intersection or Dict).
 *
 * Attribute arguments are read raw (ReflectionAttribute::getArguments()),
 * so class-name resolution and spec errors surface at validation time,
 * even for empty arrays.
 *
 * @internal
 */
final class ArraySpecValidator
{
    private const ATTRIBUTE_CLASSES = [Sequence::class, Union::class, Intersection::class, Dict::class];

    private const REFERENCE_NAMES = [
        'attributes\validation\fields\sequence' => Sequence::class,
        'attributes\validation\fields\union' => Union::class,
        'attributes\validation\fields\intersection' => Intersection::class,
        'attributes\validation\fields\dict' => Dict::class,
    ];

    private static array $specCache = [];

    /**
     * Validates every key and element of an array value against the
     * property's array type-hint attribute, coercing elements in place.
     */
    public static function validateArray(
        array &$value,
        ReflectionProperty $property,
        ResolvedConfigs $configs,
        array &$errors,
        ?string $parentPath,
        string $fieldName,
    ): bool {
        $spec = self::buildForProperty($property);

        if ($spec === null) {
            return true;
        }

        return self::validateArrayElements($value, $spec, $property, $configs, $errors, $parentPath, $fieldName);
    }

    /**
     * Walks the array and validates/coerces every key and element against
     * the given spec.
     */
    private static function validateArrayElements(
        array &$value,
        Spec $spec,
        ReflectionProperty $property,
        ResolvedConfigs $configs,
        array &$errors,
        ?string $parentPath,
        string $fieldName,
    ): bool {
        $basePath = ($parentPath === null || $parentPath === '') ? $fieldName : $parentPath.'.'.$fieldName;
        $allValid = true;

        foreach ($value as $key => $element) {
            $elementName = (string) $key;

            if (! self::keyMatchesArms($key, $spec)) {
                ErrorMessages::addFieldError(
                    ErrorMessages::TYPE,
                    $basePath,
                    $elementName,
                    $property,
                    $element,
                    $errors,
                    self::armsExpected($spec->keyArms ?? [], false)
                );

                $allValid = false;

                if ($configs->stopAtFirstError) {
                    break;
                }

                continue;
            }

            $result = self::validateValueAgainstArms($element, $spec, $property, $configs, $errors, $basePath, $elementName);
            $value[$key] = $result['value'];

            if ($result['status'] !== 'valid') {
                if ($result['status'] === 'invalid') {
                    ErrorMessages::addFieldError(
                        ErrorMessages::TYPE,
                        $basePath,
                        $elementName,
                        $property,
                        $element,
                        $errors,
                        self::armsExpected($spec->arms, $spec->isIntersection)
                    );
                }

                $allValid = false;

                if ($configs->stopAtFirstError) {
                    break;
                }
            }
        }

        return $allValid;
    }

    /**
     * Whether the Dict key spec accepts the key kind.
     */
    private static function keyMatchesArms(int|string $key, Spec $spec): bool
    {
        if ($spec->keyArms === null) {
            return true;
        }

        $needed = is_string($key) ? Masks::STRING : Masks::INT;

        foreach ($spec->keyArms as $arm) {
            if (($arm->basicMask & $needed) !== 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates a single element against the spec arms, with the same
     * ordering semantics as the type-hint union loop: exact matches win
     * over coercions; coercions run in declaration order.
     *
     * @return array{status: string, value: mixed}
     */
    private static function validateValueAgainstArms(
        mixed $element,
        Spec $spec,
        ReflectionProperty $property,
        ResolvedConfigs $configs,
        array &$errors,
        string $basePath,
        string $elementName,
    ): array {
        $valid = ['status' => 'valid', 'value' => $element];

        if ($spec->isIntersection) {
            if (is_object($element)) {
                foreach ($spec->arms as $arm) {
                    if ($arm->class === null || ! $element instanceof $arm->class) {
                        return ['status' => 'invalid', 'value' => $element];
                    }
                }

                return $valid;
            }

            return ['status' => 'invalid', 'value' => $element];
        }

        $typeMask = Masks::of($element);

        // Pass 1: exact matches only
        foreach ($spec->arms as $arm) {
            if ($arm->nested !== null) {
                if (is_array($element)
                    && self::validateArrayElements($element, $arm->nested, $property, $configs, $errors, $basePath, $elementName)) {
                    return ['status' => 'valid', 'value' => $element];
                }

                continue;
            }

            if ($arm->class !== null) {
                if (is_object($element) && $element instanceof $arm->class) {
                    return $valid;
                }

                continue;
            }

            if (($arm->basicMask & $typeMask) !== 0) {
                return $valid;
            }
        }

        // Pass 2: coercions in declaration order
        $reported = false;

        foreach ($spec->arms as $arm) {
            if ($arm->nested !== null) {
                if (is_array($element)) {
                    // Already fully validated (and failed) in pass 1: its
                    // element errors are already in the error collection
                    $reported = true;
                }

                continue;
            }

            if ($arm->class !== null) {
                $handled = TypeHintValidator::handleClass($element, $arm->class, $configs, $errors, $basePath, $elementName);

                if ($handled !== null) {
                    return ['status' => 'valid', 'value' => $handled];
                }

                if (is_array($element) && is_subclass_of($arm->class, BaseModel::class)) {
                    $reported = true;
                }

                continue;
            }

            if (! $configs->strict && ($arm->basicMask & Masks::BOOL) !== 0) {
                $bool = Coercion::coerceBool($element);

                if ($bool !== null) {
                    return ['status' => 'valid', 'value' => $bool];
                }
            }

            foreach ([Masks::INT, Masks::FLOAT, Masks::STRING] as $bit) {
                if (($arm->basicMask & $bit) === 0) {
                    continue;
                }

                $coerced = Coercion::verifyScalar($element, $bit, $configs->strict);

                if ($coerced !== null) {
                    return ['status' => 'valid', 'value' => $coerced];
                }
            }
        }

        return ['status' => $reported ? 'reported' : 'invalid', 'value' => $element];
    }

    /**
     * Builds (and caches) the spec of a property's first array type-hint
     * attribute, or null when the property declares none of them.
     */
    private static function buildForProperty(ReflectionProperty $property): ?Spec
    {
        $cacheKey = $property->getDeclaringClass()->getName().'::$'.$property->getName();

        if (array_key_exists($cacheKey, self::$specCache)) {
            return self::$specCache[$cacheKey];
        }

        $attribute = self::findArrayAttribute($property);

        if ($attribute === null) {
            return self::$specCache[$cacheKey] = null;
        }

        return self::$specCache[$cacheKey] = self::buildSpecFromAttribute($attribute, $property);
    }

    /**
     * The first Sequence, Union, Intersection or Dict attribute declared on
     * the property, in declaration order.
     */
    private static function findArrayAttribute(ReflectionProperty $property): ?ReflectionAttribute
    {
        foreach ($property->getAttributes() as $attribute) {
            if (in_array($attribute->getName(), self::ATTRIBUTE_CLASSES, true)) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * Builds a spec straight from an attribute declaration on the property.
     */
    private static function buildSpecFromAttribute(ReflectionAttribute $attribute, ReflectionProperty $property): Spec
    {
        $spec = new Spec;
        $name = $attribute->getName();
        $arguments = $attribute->getArguments();

        if ($name === Dict::class) {
            [$keyValue, $ofValue] = self::findDictArguments($arguments, $property);

            $spec->keyArms = self::buildKeyArms($keyValue, $property);
            $spec->arms = self::buildValueArms($ofValue, $property, $spec);

            return $spec;
        }

        if ($name === Sequence::class) {
            $ofValue = self::firstArgument($arguments);
            $spec->arms = self::buildValueArms($ofValue, $property, $spec);

            return $spec;
        }

        $spec->isIntersection = $name === Intersection::class;

        if ($arguments === []) {
            throw new \ValueError(sprintf(
                'Attribute "%s" for property "%s" of class "%s" must declare at least one type.',
                strtolower($name),
                $property->getName(),
                $property->getDeclaringClass()->getName()
            ));
        }

        $arms = [];

        foreach (array_values($arguments) as $armValue) {
            $arms[] = self::buildArm($armValue, false, $property);
        }

        $spec->arms = $arms;

        if ($spec->isIntersection) {
            self::validateIntersectionArms($spec);
        }

        return $spec;
    }

    /**
     * Builds a spec from a materialized attribute object (a nested new
     * expression inside an attribute).
     */
    private static function buildSpecFromObject(Sequence|Union|Intersection|Dict $object, ReflectionProperty $property): Spec
    {
        $spec = new Spec;

        if ($object instanceof Dict) {
            $spec->keyArms = self::buildKeyArms($object->key, $property);
            $spec->arms = self::buildValueArms($object->of, $property, $spec);

            return $spec;
        }

        if ($object instanceof Sequence) {
            $spec->arms = self::buildValueArms($object->of, $property, $spec);

            return $spec;
        }

        $spec->isIntersection = $object instanceof Intersection;

        $arms = [];

        foreach ($object->of as $armValue) {
            $arms[] = self::buildArm($armValue, false, $property);
        }

        $spec->arms = $arms;

        if ($spec->isIntersection) {
            self::validateIntersectionArms($spec);
        }

        return $spec;
    }

    /**
     * Reads the two Dict arguments by name, falling back to positional
     * order.
     */
    private static function findDictArguments(array $arguments, ReflectionProperty $property): array
    {
        $keyValue = null;
        $ofValue = null;
        $keyIndex = null;
        $ofIndex = null;

        foreach ($arguments as $name => $value) {
            if (is_int($name)) {
                if ($keyIndex === null) {
                    $keyIndex = $name;
                    $keyValue = $value;
                } elseif ($ofIndex === null) {
                    $ofIndex = $name;
                    $ofValue = $value;
                }

                continue;
            }

            if (strcasecmp($name, 'key') === 0 && $keyIndex === null) {
                $keyIndex = -1;
                $keyValue = $value;
            } elseif (strcasecmp($name, 'of') === 0 && $ofIndex === null) {
                $ofIndex = -1;
                $ofValue = $value;
            }
        }

        if ($keyValue === null || $ofValue === null) {
            throw new \ValueError(sprintf(
                'Dict attribute for property "%s" of class "%s" must declare both key and of types.',
                $property->getName(),
                $property->getDeclaringClass()->getName()
            ));
        }

        return [$keyValue, $ofValue];
    }

    /**
     * The single argument of a one-argument attribute, by position or name.
     */
    private static function firstArgument(array $arguments): mixed
    {
        foreach ($arguments as $name => $value) {
            if (is_int($name)) {
                return $value;
            }
        }

        return $arguments['of'] ?? array_values($arguments)[0] ?? null;
    }

    /**
     * Builds the Dict key arms: a single basic type, or the flattened arms
     * of a Union object.
     *
     * @return array<int, Arm>
     */
    private static function buildKeyArms(mixed $keyValue, ReflectionProperty $property): array
    {
        if ($keyValue instanceof Union) {
            $arms = [];

            foreach ($keyValue->of as $armValue) {
                $arms[] = self::buildArm($armValue, true, $property);
            }

            return $arms;
        }

        return [self::buildArm($keyValue, true, $property)];
    }

    /**
     * Builds the value arms of a Dict or Sequence: a single arm, the
     * flattened arms of a Union object, or an Intersection object whose
     * value must match every class.
     *
     * @return array<int, Arm>
     */
    private static function buildValueArms(mixed $ofValue, ReflectionProperty $property, Spec $spec): array
    {
        $isIntersection = $ofValue instanceof Intersection;
        $values = [];

        if ($ofValue instanceof Union || $ofValue instanceof Intersection) {
            $values = $ofValue->of;
        } else {
            $values = [$ofValue];
        }

        $arms = [];

        foreach ($values as $armValue) {
            $arms[] = self::buildArm($armValue, false, $property);
        }

        if ($isIntersection) {
            $spec->isIntersection = true;
            $spec->arms = $arms;
            self::validateIntersectionArms($spec);
        }

        return $arms;
    }

    /**
     * Validates that an intersection spec only declares class arms.
     */
    private static function validateIntersectionArms(Spec $spec): void
    {
        foreach ($spec->arms as $arm) {
            if ($arm->class === null) {
                throw new \ValueError('Intersection types must be class names.');
            }
        }
    }

    /**
     * Builds one arm. In arm position attribute objects and class-name
     * references become nested array specs; in key position only the
     * integer and string basic types are accepted.
     */
    private static function buildArm(mixed $armValue, bool $isKey, ReflectionProperty $property): Arm
    {
        if (is_int($armValue)) {
            return self::buildBasicArm($armValue, $isKey);
        }

        if ($armValue instanceof Type) {
            return self::buildBasicArm($armValue->value, $isKey);
        }

        if (is_object($armValue)) {
            if ($isKey) {
                throw new \ValueError('Dict key types must be integer or string.');
            }

            if (! $armValue instanceof Sequence && ! $armValue instanceof Union
                && ! $armValue instanceof Intersection && ! $armValue instanceof Dict) {
                throw new \ValueError(sprintf(
                    'Unsupported type "%s" in an Attributes\Validation array type attribute.',
                    $armValue::class
                ));
            }

            return new Arm(nested: self::buildSpecFromObject($armValue, $property));
        }

        if (is_string($armValue)) {
            $nested = self::buildReferenceArm($armValue, $property, $isKey);

            if ($nested !== null) {
                return new Arm(nested: $nested);
            }

            if ($isKey) {
                throw new \ValueError(sprintf('Dict key types must be integer or string, "%s" given.', $armValue));
            }

            $class = self::resolveArmClassName($armValue, $property);

            if ($class === null) {
                throw self::armClassNotFoundError($armValue, $property);
            }

            return new Arm(classString: $armValue, class: $class);
        }

        throw new \ValueError('Unsupported type in an Attributes\Validation array type attribute.');
    }

    /**
     * Builds a basic arm from a Type case value, which may combine several
     * type masks with the bitwise OR operator.
     */
    private static function buildBasicArm(int $typeValue, bool $isKey): Arm
    {
        if ($typeValue <= 0 || ($typeValue & ~Masks::ALL_BITS) !== 0) {
            throw new \ValueError(sprintf(
                'Unsupported type value %d in an Attributes\Validation array type attribute.',
                $typeValue
            ));
        }

        $mask = $typeValue;

        if (($mask & Masks::DICT) !== 0) {
            $mask = ($mask & ~Masks::DICT) | Masks::ARRAY;
        }

        if ($isKey && ($mask & ~(Masks::INT | Masks::STRING)) !== 0) {
            throw new \ValueError(sprintf('Dict key types must be integer or string, %d given.', $typeValue));
        }

        return new Arm(basicMask: $mask);
    }

    /**
     * Builds a nested spec from a class-name reference arm (Dict::class):
     * the name must match one of the array type attributes and the property
     * must declare it. Self-references are rejected.
     *
     * Returns null when the name is not one of the array type attribute
     * names (falling back to class resolution).
     */
    private static function buildReferenceArm(string $name, ReflectionProperty $property, bool $isKey): ?Spec
    {
        if ($isKey) {
            return null;
        }

        $class = self::REFERENCE_NAMES[strtolower($name)] ?? null;

        if ($class === null) {
            return null;
        }

        $attributes = $property->getAttributes($class);

        if ($attributes === []) {
            throw new \ValueError(sprintf(
                'Type "%s" for property "%s" of class "%s" references an attribute not declared on the property.',
                $name,
                $property->getName(),
                $property->getDeclaringClass()->getName()
            ));
        }

        $referenced = $attributes[0];
        $root = self::findArrayAttribute($property);

        if ($root !== null && $root->getName() === $referenced->getName()) {
            throw new \ValueError(sprintf(
                'Type "%s" for property "%s" of class "%s" references itself.',
                $name,
                $property->getName(),
                $property->getDeclaringClass()->getName()
            ));
        }

        return self::buildSpecFromAttribute($referenced, $property);
    }

    /**
     * Resolves a class name arm the way PHP resolves unqualified type hint
     * names: relative to the declaring class namespace first, then in the
     * global namespace.
     */
    private static function resolveArmClassName(string $name, ReflectionProperty $property): ?string
    {
        if ($name === '') {
            return null;
        }

        if (strcasecmp($name, 'self') === 0) {
            return $property->getDeclaringClass()->getName();
        }

        if (strcasecmp($name, 'parent') === 0) {
            $parent = $property->getDeclaringClass()->getParentClass();

            return $parent === false ? null : $parent->getName();
        }

        if (str_starts_with($name, '\\')) {
            $unqualified = substr($name, 1);

            return Value::isResolvableClassName($unqualified) ? $unqualified : null;
        }

        $declaringClass = $property->getDeclaringClass()->getName();
        $separator = strrpos($declaringClass, '\\');

        if ($separator !== false) {
            $qualified = substr($declaringClass, 0, $separator + 1).$name;

            if (Value::isResolvableClassName($qualified)) {
                return $qualified;
            }
        }

        return Value::isResolvableClassName($name) ? $name : null;
    }

    private static function armClassNotFoundError(string $name, ReflectionProperty $property): \ValueError
    {
        $declaringClass = $property->getDeclaringClass()->getName();
        $separator = strrpos($declaringClass, '\\');

        if ($separator !== false && ! str_starts_with($name, '\\')
            && strcasecmp($name, 'self') !== 0 && strcasecmp($name, 'parent') !== 0) {
            $qualified = substr($declaringClass, 0, $separator + 1).$name;

            return new \ValueError(sprintf(
                'Type "%s" for property "%s" of class "%s" could not be found: looked for "%s" and "%s".',
                $name,
                $property->getName(),
                $declaringClass,
                $qualified,
                $name
            ));
        }

        return new \ValueError(sprintf(
            'Type "%s" for property "%s" of class "%s" could not be found: looked for "%s".',
            $name,
            $property->getName(),
            $declaringClass,
            $name
        ));
    }

    /**
     * Renders the expected-type string of an arm list, joined like a
     * union (or an intersection with " and ").
     */
    private static function armsExpected(array $arms, bool $isIntersection): string
    {
        $parts = [];

        foreach ($arms as $arm) {
            $parts[] = self::armExpected($arm);
        }

        $total = count($parts);

        if ($total === 0) {
            return 'mixed';
        }

        $result = '';

        foreach ($parts as $index => $part) {
            if ($index > 0) {
                $isLast = $index + 1 === $total;
                $result .= $isLast ? ($isIntersection ? ' and ' : ' or ') : ', ';
            }

            $result .= $part;
        }

        return $result;
    }

    /**
     * Renders a single arm for an error message.
     */
    private static function armExpected(Arm $arm): string
    {
        if ($arm->nested !== null) {
            return self::specToNestedString($arm->nested);
        }

        if ($arm->classString !== null) {
            return $arm->classString;
        }

        foreach (Masks::BASIC_NAMES as [$mask, $name]) {
            if ($arm->basicMask === $mask) {
                return $name;
            }
        }

        // Bitmask arms carry several type bits: render each as a union arm
        $parts = [];

        foreach (Masks::BASIC_NAMES as [$mask, $name]) {
            if ($mask !== Masks::NULL && ($arm->basicMask & $mask) === $mask) {
                $parts[] = $name;
            }
        }

        if ($parts !== []) {
            $total = count($parts);
            $result = '';

            foreach ($parts as $index => $part) {
                if ($index > 0) {
                    $result .= $index + 1 === $total ? ' or ' : ', ';
                }

                $result .= $part;
            }

            return $result;
        }

        return 'mixed';
    }

    /**
     * Renders a spec in nested arm position: "array<values>" or
     * "array<keys, values>".
     */
    private static function specToNestedString(Spec $spec): string
    {
        $value = self::armsExpected($spec->arms, $spec->isIntersection);

        if ($spec->keyArms === null) {
            return 'array<'.$value.'>';
        }

        return 'array<'.self::armsExpected($spec->keyArms, false).', '.$value.'>';
    }
}
