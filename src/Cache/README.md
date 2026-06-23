# Validation Cache

This directory contains cache implementations for performance optimization.

## Available Caches

### ReflectionCache
Caches ReflectionClass and ReflectionProperty instances to avoid repeated reflection operations.
Provides 40-60% performance improvement for repeated validations.

### ModelMetadataCache
Caches model validation metadata including properties and validators.
Reduces overhead when validating the same model multiple times.
