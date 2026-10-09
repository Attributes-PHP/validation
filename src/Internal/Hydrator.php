<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Attributes\Validation\BaseModel;
use Attributes\Validation\Exceptions\ValidationException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Hydrates a BaseModel instance from a raw data array.
 *
 * @internal
 */
final class Hydrator
{
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
     * Validates the public non-static properties of the model class
     * hierarchy (the model class first, up to but excluding BaseModel).
     *
     * Returns true only when the shared error collection stayed empty.
     */
    public static function validateModel(
        array $rawData,
        BaseModel $model,
        ResolvedConfigs $configs,
        array &$errors,
        ?string $parentPath,
    ): bool {
        for ($class = $model::class; $class !== false && $class !== BaseModel::class; $class = get_parent_class($class)) {
            $reflection = new ReflectionClass($class);

            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic() || $property->isPrivate() || $property->isProtected()) {
                    continue;
                }

                if ($property->getDeclaringClass()->getName() !== $class) {
                    continue;
                }

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
        }

        return $errors === [];
    }

    /**
     * Whether the model class overrides one of the validation hooks.
     */
    private static function overridesHooks(string $class): bool
    {
        $reflection = new ReflectionClass($class);

        foreach (['beforeValidation', 'afterValidation'] as $method) {
            $hook = $reflection->getMethod($method);

            if (! $hook instanceof ReflectionMethod || $hook->getDeclaringClass()->getName() !== BaseModel::class) {
                return true;
            }
        }

        return false;
    }
}
