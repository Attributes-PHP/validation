<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Attributes\Validation\BaseModel;
use Attributes\Validation\Exceptions\ValidationException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Hydrates a BaseModel instance from a raw data array.
 *
 * @internal
 */
final class Hydrator
{
    /**
     * The prepared property entries of each model class hierarchy per
     * alias generator: the property reflection, its resolved field name
     * and whether it declares a default value, all static per class, so
     * the walk and the field-name resolution run once per class.
     *
     * @var array<string, list<array{property: ReflectionProperty, fieldName: string, hasDefault: bool}>>
     */
    private static array $entriesCache = [];

    /**
     * Whether each model class overrides one of the validation hooks.
     *
     * @var array<string, bool>
     */
    private static array $hooksCache = [];

    /**
     * Validates a raw data array against the model's type hints and
     * attributes, populates the model and runs its hooks.
     *
     * @throws ValidationException when any field fails validation
     */
    public static function hydrate(array $rawData, BaseModel $model): BaseModel
    {
        $class = $model::class;
        $configs = ResolvedConfigs::resolve($class);
        $hooksOverridden = self::overridesHooks($class);

        if ($hooksOverridden) {
            $result = (new ReflectionMethod($model, 'beforeValidation'))
                ->invoke($model, $rawData, $configs->toModelConfigs());

            if (is_array($result)) {
                $rawData = $result;
            }
        }

        $errors = [];

        if (! self::validateModel($rawData, $model, $configs, $errors, null)) {
            throw new ValidationException($errors);
        }

        if ($hooksOverridden) {
            (new ReflectionMethod($model, 'afterValidation'))
                ->invoke($model, $rawData, $configs->toModelConfigs());
        }

        return $model;
    }

    /**
     * Validates the properties of the model class hierarchy against the
     * raw data. Returns true only when the shared error collection stayed
     * empty.
     */
    public static function validateModel(
        array $rawData,
        BaseModel $model,
        ResolvedConfigs $configs,
        array &$errors,
        ?string $parentPath,
    ): bool {
        $cacheKey = $model::class."\0".(string) $configs->aliasGenerator;

        foreach (self::entries($cacheKey, $model::class, $configs->aliasGenerator) as $entry) {
            $fieldName = $entry['fieldName'];

            if (! array_key_exists($fieldName, $rawData)) {
                if ($entry['hasDefault']) {
                    continue;
                }

                ErrorMessages::addFieldError(ErrorMessages::REQUIRED, $parentPath, $fieldName, $entry['property'], null, $errors);

                if ($configs->stopAtFirstError) {
                    return false;
                }

                continue;
            }

            $value = $rawData[$fieldName];
            $result = TypeHintValidator::validate($value, $entry['property'], $configs, $errors, $parentPath, $fieldName);

            if (! $result['ok']) {
                if ($configs->stopAtFirstError) {
                    return false;
                }

                continue;
            }

            $entry['property']->setValue($model, $result['value']);
        }

        return $errors === [];
    }

    /**
     * The prepared property entries of the model class hierarchy,
     * cached per class and alias generator.
     *
     * @return list<array{property: ReflectionProperty, fieldName: string, hasDefault: bool}>
     */
    private static function entries(string $cacheKey, string $class, ?string $generator): array
    {
        if (isset(self::$entriesCache[$cacheKey])) {
            return self::$entriesCache[$cacheKey];
        }

        $entries = [];

        for ($current = $class; $current !== false && $current !== BaseModel::class; $current = get_parent_class($current)) {
            $reflection = new ReflectionClass($current);

            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic() || $property->isPrivate() || $property->isProtected()) {
                    continue;
                }

                if ($property->getDeclaringClass()->getName() !== $current) {
                    continue;
                }

                $entries[] = [
                    'property' => $property,
                    'fieldName' => FieldNameResolver::resolve($property, $generator),
                    'hasDefault' => $property->hasDefaultValue(),
                ];
            }
        }

        return self::$entriesCache[$cacheKey] = $entries;
    }

    /**
     * Whether the model class overrides one of the validation hooks,
     * cached per class.
     */
    private static function overridesHooks(string $class): bool
    {
        if (array_key_exists($class, self::$hooksCache)) {
            return self::$hooksCache[$class];
        }

        $reflection = new ReflectionClass($class);
        $overridden = false;

        foreach (['beforeValidation', 'afterValidation'] as $method) {
            $hook = $reflection->getMethod($method);

            if (! $hook instanceof ReflectionMethod || $hook->getDeclaringClass()->getName() !== BaseModel::class) {
                $overridden = true;

                break;
            }
        }

        return self::$hooksCache[$class] = $overridden;
    }
}
