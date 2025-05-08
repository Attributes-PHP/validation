<?php

/**
 * Holds logic to save extracted rules into disk
 */

declare(strict_types=1);

namespace Attributes\Validation\Cache;

use Attributes\Validation\Exceptions\CachedRulesNotFoundException;
use Attributes\Validation\Exceptions\CacheException;
use Attributes\Validation\Property;

class FileBasedCache implements Cache
{
    private string $filePath;

    private bool $forceRemoval;

    private ?array $data = null;

    public function __construct(string $filePath, bool $forceRemoval = false)
    {
        $this->filePath = $filePath;
        $this->forceRemoval = $forceRemoval;
    }

    /**
     * Tries to load a cached rules from a given model
     *
     * @param  Property  $property  - The model in question
     * @return array - The loaded cached validation rules
     *
     * @throws CachedRulesNotFoundException - When a given model is not contained in the cache
     * @throws CacheException - When something went wrong while loading the cached rules for the model
     */
    public function load(Property $property): array
    {
        $data = $this->getData();
        if (! $data) {
            throw new CacheException("Cache file $this->filePath is empty");
        }

        $propertyKey = $this->getPropertyKey($property);
        if (! isset($data[$propertyKey])) {
            throw new CachedRulesNotFoundException("Rules for $propertyKey not found in cache");
        }

        $modelData = $data[$propertyKey];
        if (! is_array($modelData)) {
            throw new CacheException("Invalid cached rules for $propertyKey");
        }

        return $modelData;
    }

    /**
     * Checks if a given property is cached
     *
     * @throws CacheException
     */
    public function has(Property $property): bool
    {
        $data = $this->getData();

        return $data && isset($data[$this->getPropertyKey($property)]);
    }

    /**
     * Saves a given set of rules for a model into the cache
     *
     * @param  Property  $property  - The model in question
     * @param  array  $rules  - The validation rules to be associated with the model
     *
     * @throws CacheException - When something went wrong while saving the rules to the cache
     */
    public function save(Property $property, array $rules): void
    {
        if ($this->forceRemoval && file_exists($this->filePath) && ! unlink($this->filePath)) {
            throw new CacheException("Unable to delete existent cache file $this->filePath");
        }

        $this->forceRemoval = false;  // Avoid deleting file multiple times
        $cacheDir = dirname($this->filePath);
        if (! file_exists($cacheDir) && ! mkdir($cacheDir, 0744, true)) {
            throw new CacheException("Unable to create cache directory $cacheDir");
        }

        $propertyKey = $this->getPropertyKey($property);
        $this->data = file_exists($this->filePath) ? $this->getData() : [];
        $this->data[$propertyKey] = $rules;
        $exportedRules = var_export($this->data, true);
        if (is_null($exportedRules)) {
            throw new CacheException("Unable to export rules for $propertyKey");
        }

        $exportedRules = '<?php return '.$exportedRules.';';
        if (file_put_contents($this->filePath, $exportedRules) === false) {
            throw new CacheException("Unable to cache rules for $propertyKey to $this->filePath");
        }
    }

    /**
     * Retrieves all cached data
     *
     * @return array - Cached rules
     *
     * @throws CacheException
     */
    private function getData(): array
    {
        if ($this->data) {
            return $this->data;
        }

        $decodedData = require $this->filePath;
        if (! is_array($decodedData)) {
            throw new CacheException("Invalid cached data from $this->filePath");
        }

        $this->data = $decodedData;

        return $this->data;
    }

    private function getPropertyKey(Property $property): string
    {
        return $property->getModelClass().'.'.$property->getName();
    }
}
