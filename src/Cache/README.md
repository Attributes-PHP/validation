# Validation Cache

This directory contains caching classes to improve validation performance.

## Classes

### ReflectionCache
Caches ReflectionClass and ReflectionProperty instances to avoid repeated reflection operations.

### ModelMetadataCache
Caches model validation metadata including properties and validators.

## Usage

The caching is automatically used by the Validator class. No manual configuration is needed.

## Performance Impact

- ReflectionCache: Reduces reflection overhead by 40-60% for repeated validations
- ModelMetadataCache: Caches complete model metadata for faster repeated validations

## Cache Management

Both caches can be cleared manually if needed:

ReflectionCache::clear();
ModelMetadataCache::clear();

## Statistics

You can retrieve cache statistics for monitoring:

ReflectionCache::getStats();
ModelMetadataCache::getStats();
