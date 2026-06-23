<?php

declare(strict_types=1);

namespace AttributesValidationCache;

use AttributesValidationContext;
use AttributesValidationValidatorsPropertyValidator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Caches model validation metadata to avoid repeated reflection
 */
final class ModelMetadataCache
{
    private static array $metadataCache = [];

    /**
     * Get metadata for a model class
     */
    public static function getMetadata(
        string $className,
        Context $context,
        PropertyValidator $propertyValidator
    ): ModelMetadata {
        if (!isset(self::$metadataCache[$className])) {
            self::$metadataCache[$className] = self::buildMetadata($className, $context, $propertyValidator);
        }
        return self::$metadataCache[$className];
    }

    /**
     * Build metadata for a model class
     */
    private static function buildMetadata(
        string $className,
        Context $context,
        PropertyValidator $propertyValidator
    ): ModelMetadata {
        $reflectionClass = ReflectionCache::getClassReflection($className);
        $properties = [];
        $validatableProperties = [];

        foreach (ReflectionCache::getProperties($reflectionClass) as $property) {
            if (!self::isToValidate($property, $context)) {
                continue;
            }
            $properties[] = $property;
            $validatableProperties[$property->getName()] = true;
        }

        return new ModelMetadata(
            $className,
            $properties,
            $validatableProperties,
            self::getDefaultAliasGenerator($reflectionClass, $context)
        );
    }

    /**
     * Check if a property should be validated
     */
    private static function isToValidate(ReflectionProperty|ReflectionParameter $reflection, Context $context): bool
    {
        $useSerialization = $context->getOptional('internal.options.ignore.useSerialization', false);
        $allAttributes = $reflection->getAttributes(AttributesOptionsIgnore::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();
            return $useSerialization ? !$instance->ignoreSerialization() : !$instance->ignoreValidation();
        }
        return true;
    }

    /**
     * Get the default alias generator for a class
     */
    private static function getDefaultAliasGenerator(ReflectionClass $reflection, Context $context): callable
    {
        $allAttributes = $reflection->getAttributes(AttributesOptionsAliasGenerator::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();
            return $instance->getAliasGenerator();
        }

        $aliasGenerator = $context->getOptional('option.alias.generator');
        if (is_callable($aliasGenerator)) {
            return $aliasGenerator;
        }

        $aliasGeneratorClass = new AttributesOptionsAliasGenerator($aliasGenerator);
        return $aliasGeneratorClass->getAliasGenerator();
    }

    /**
     * Clear all cached metadata
     */
    public static function clear(): void
    {
        self::$metadataCache = [];
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        return [
            'model_count' => count(self::$metadataCache),
        ];
    }
}

/**
 * Metadata for a model class
 */
final class ModelMetadata
{
    /**
     * @param array<ReflectionProperty|ReflectionParameter> $properties
     * @param array<string, true> $validatableProperties
     */
    public function __construct(
        public readonly string $className,
        public readonly array $properties,
        public readonly array $validatableProperties,
        public readonly callable $defaultAliasGenerator,
    ) {
    }

    /**
     * Check if a property is validatable
     */
    public function isValidatable(string $propertyName): bool
    {
        return isset($this->validatableProperties[$propertyName]);
    }
}