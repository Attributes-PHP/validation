<?php

/**
 * Holds logic to save extracted rules into disk
 */

declare(strict_types=1);

namespace Attributes\Validation\Cache;

use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\CacheException;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Attributes\Validation\Validator;
use Attributes\Validation\Validators\RulesExtractors\PropertyRulesExtractor;
use ReflectionClass;

class CacheGenerator
{
    private Validator $validator;

    private array $modelsDir;

    private Cache $cache;

    private int $maxDepth;

    /**
     * @throws ContextPropertyException
     */
    public function __construct(Validator $validator, array $modelsDir, ?Cache $cache = null, int $maxDepth = 12)
    {
        $this->validator = $validator;
        $this->modelsDir = $modelsDir;
        $this->cache = $cache ?? $this->getDefaultCache();
        $this->maxDepth = $maxDepth;
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
                $propertyRules = iterator_to_array($rulesExtractor->getRulesFromProperty($property, $context));
                $this->cache->save($property, $propertyRules);
            }
        }

        $numModels = count($allModels);
        echo "$numModels saved to the cache\n";
    }

    private function getAllModelClasses(): array
    {
        foreach ($this->modelsDir as $dir) {
            $this->includePhpFilesFromDir($dir);
        }

        $allModels = [];
        $allDeclaredClasses = get_declared_classes();
        foreach ($allDeclaredClasses as $model) {
            if (! is_a($model, Model::class, true)) {
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

    /**
     * Autoloads all PHP files from a given directory
     *
     * @throws CacheException
     */
    private function includePhpFilesFromDir(string $dir, int $depth = 0): void
    {
        if ($depth > $this->maxDepth) {
            throw new CacheException('Maximum depth exceeded');
        }

        if (! is_dir($dir)) {
            throw new CacheException("$dir is not a directory");
        }

        $allFiles = glob("$dir/*");
        foreach ($allFiles as $filePath) {
            if (is_dir($filePath)) {
                $this->includePhpFilesFromDir($filePath, $depth + 1);

                continue;
            }

            if (! preg_match('/\.php$/', $filePath)) {
                continue;
            }

            @include_once $filePath;
        }
    }
}
