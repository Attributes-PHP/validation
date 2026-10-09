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
     * The public non-static properties of each model class hierarchy,
     * walked once per class (the model class first, up to but excluding
     * BaseModel).
     *
     * @var array<string, list<ReflectionProperty>>
     */
    private static array $propertiesCache = [];

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
        foreach (self::properties($model::class) as $property) {
            $fieldName = FieldNameResolver::resolve($property, $configs->aliasGenerator);

            if (! array_key_exists($fieldName, $rawData)) {
                if ($property->hasDefaultValue()) {
                    continue;
                }

                ErrorMessages::addFieldError(ErrorMessages::REQUIRED, $parentPath, $fieldName, $property, null, $errors);

                if ($configs->stopAtFirstError) {
                    return false;
                }

                continue;
            }

            $value = $rawData[$fieldName];
            $result = TypeHintValidator::validate($value, $property, $configs, $errors, $parentPath, $fieldName);

            if (! $result['ok']) {
                if ($configs->stopAtFirstError) {
                    return false;
                }

                continue;
            }

            $property->setValue($model, $result['value']);
        }

        return $errors === [];
    }

    /**
     * The public non-static properties of the model class hierarchy,
     * cached per class.
     *
     * @return list<ReflectionProperty>
     */
    private static function properties(string $class): array
    {
        if (isset(self::$propertiesCache[$class])) {
            return self::$propertiesCache[$class];
        }

        $properties = [];

        for ($current = $class; $current !== false && $current !== BaseModel::class; $current = get_parent_class($current)) {
            $reflection = new ReflectionClass($current);

            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic() || $property->isPrivate() || $property->isProtected()) {
                    continue;
                }

                if ($property->getDeclaringClass()->getName() !== $current) {
                    continue;
                }

                $properties[] = $property;
            }
        }

        return self::$propertiesCache[$class] = $properties;
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
