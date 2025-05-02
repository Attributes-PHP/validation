<?php

/**
 * Holds logic to save extracted rules into disk
 */

declare(strict_types=1);

namespace Attributes\Validation\Cache;

use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Attributes\Validation\Validator;
use Attributes\Validation\Validators\RulesExtractors\PropertyRulesExtractor;
use ReflectionClass;

class CacheGenerator
{
    private Validator $validator;

    private Cache $cache;

    /**
     * @throws ContextPropertyException
     */
    public function __construct(Validator $validator, ?Cache $cache = null)
    {
        $this->validator = $validator;
        $this->cache = $cache ?? $this->getDefaultCache();
    }

    public function generate(): void
    {
        $allModels = $this->getAllModelClasses();
        if (! $allModels) {
            echo "No models to cache\n";

            return;
        }
        $context = $this->validator->getContext();
        $errorInfo = new ErrorInfo($context);
        $context->setGlobal(ErrorInfo::class, $errorInfo);
        $rulesExtractor = $context->getGlobal(PropertyRulesExtractor::class);
        foreach ($allModels as $modelClass) {
            $reflectionClass = new ReflectionClass($modelClass);
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $property = new Property($reflectionProperty, null, $modelClass);
                $context->setGlobal(Property::class, $property, override: true);
                $propertyRules = iterator_to_array($rulesExtractor->getRulesFromProperty($property));
                $this->cache->save($property, $propertyRules);
            }
        }

        $numModels = count($allModels);
        echo "$numModels saved to the cache\n";
    }

    private function getAllModelClasses(): array
    {
        $allModels = [];
        $allDeclaredClasses = get_declared_classes();
        foreach ($allDeclaredClasses as $model) {
            if (! ($model instanceof Model)) {
                continue;
            }

            $allModels[] = $model;
        }

        return $allModels;
    }

    /**
     * @throws ContextPropertyException
     */
    private function getDefaultCache(): Cache
    {
        $context = $this->validator->getContext();
        $cache = $context->getOptionalGlobal(Cache::class);
        if ($cache instanceof Cache) {
            return $cache;
        }

        $filePath = $context->getOptionalGlobal('option.cache.filePath', './cache/rules.php');
        $forceRemoval = $context->getOptionalGlobal('option.cache.forceRemoval', true);
        $cache = new FileBasedCache($filePath, $forceRemoval);
        $context->setGlobal(Cache::class, $cache);

        return $cache;
    }
}
