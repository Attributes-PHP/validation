<?php

declare(strict_types=1);

namespace Attributes\Validation\Cache;

use Attributes\Options\AliasGenerator;
use Attributes\Options\Ignore;
use Attributes\Validation\Context;
use Attributes\Validation\Validators\PropertyValidator;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

final class ModelMetadataCache
{
    private static array $metadataCache = [];

    public static function getMetadata(
        string $className,
        Context $context,
        PropertyValidator $propertyValidator
    ): ModelMetadata {
        if (! isset(self::$metadataCache[$className])) {
            self::$metadataCache[$className] = self::buildMetadata($className, $context, $propertyValidator);
        }

        return self::$metadataCache[$className];
    }

    private static function buildMetadata(
        string $className,
        Context $context,
        PropertyValidator $propertyValidator
    ): ModelMetadata {
        $reflectionClass = ReflectionCache::getClassReflection($className);
        $properties = [];
        $validatableProperties = [];

        foreach (ReflectionCache::getProperties($reflectionClass) as $property) {
            if (! self::isToValidate($property, $context)) {
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

    private static function isToValidate(ReflectionProperty|ReflectionParameter $reflection, Context $context): bool
    {
        $useSerialization = $context->getOptional('internal.options.ignore.useSerialization', false);
        $allAttributes = $reflection->getAttributes(Ignore::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();

            return $useSerialization ? ! $instance->ignoreSerialization() : ! $instance->ignoreValidation();
        }

        return true;
    }

    private static function getDefaultAliasGenerator(ReflectionClass $reflection, Context $context): callable
    {
        $allAttributes = $reflection->getAttributes(AliasGenerator::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();

            return $instance->getAliasGenerator();
        }

        $aliasGenerator = $context->getOptional('option.alias.generator');
        if (is_callable($aliasGenerator)) {
            return $aliasGenerator;
        }

        $aliasGeneratorClass = new AliasGenerator($aliasGenerator);

        return $aliasGeneratorClass->getAliasGenerator();
    }

    public static function clear(): void
    {
        self::$metadataCache = [];
    }

    public static function getStats(): array
    {
        return [
            'model_count' => count(self::$metadataCache),
        ];
    }
}

final class ModelMetadata
{
    public function __construct(
        public readonly string $className,
        public readonly array $properties,
        public readonly array $validatableProperties,
        public readonly callable $defaultAliasGenerator,
    ) {}

    public function isValidatable(string $propertyName): bool
    {
        return isset($this->validatableProperties[$propertyName]);
    }
}
