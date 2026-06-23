<?php

declare(strict_types=1);

namespace Attributes\Validation\Cache;

use ReflectionClass;
use ReflectionProperty;

/**
 * Caches reflection data to improve performance
 */
final class ReflectionCache
{
    private static array $classCache = [];
    private static array $propertyCache = [];

    /**
     * Get cached ReflectionClass for a class name
     */
    public static function getClassReflection(string $className): ReflectionClass
    {
        if (!isset(self::$classCache[$className])) {
            self::$classCache[$className] = new ReflectionClass($className);
        }
        return self::$classCache[$className];
    }

    /**
     * Get cached properties for a ReflectionClass
     *
     * @return array<ReflectionProperty>
     */
    public static function getProperties(ReflectionClass $reflectionClass): array
    {
        $className = $reflectionClass->getName();
        if (!isset(self::$propertyCache[$className])) {
            self::$propertyCache[$className] = $reflectionClass->getProperties();
        }
        return self::$propertyCache[$className];
    }

    /**
     * Clear all cached reflection data
     */
    public static function clear(): void
    {
        self::$classCache = [];
        self::$propertyCache = [];
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        return [
            'class_count' => count(self::$classCache),
            'property_count' => array_sum(array_map('count', self::$propertyCache)),
        ];
    }
}
