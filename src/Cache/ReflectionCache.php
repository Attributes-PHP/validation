<?php

declare(strict_types=1);

namespace Attributes\Validation\Cache;

use ReflectionClass;

final class ReflectionCache
{
    private static array $classCache = [];

    private static array $propertyCache = [];

    public static function getClassReflection(string $className): ReflectionClass
    {
        if (! isset(self::$classCache[$className])) {
            self::$classCache[$className] = new ReflectionClass($className);
        }

        return self::$classCache[$className];
    }

    public static function getProperties(ReflectionClass $reflectionClass): array
    {
        $className = $reflectionClass->getName();
        if (! isset(self::$propertyCache[$className])) {
            self::$propertyCache[$className] = $reflectionClass->getProperties();
        }

        return self::$propertyCache[$className];
    }

    public static function clear(): void
    {
        self::$classCache = [];
        self::$propertyCache = [];
    }

    public static function getStats(): array
    {
        return [
            'class_count' => count(self::$classCache),
            'property_count' => array_sum(array_map('count', self::$propertyCache)),
        ];
    }
}
