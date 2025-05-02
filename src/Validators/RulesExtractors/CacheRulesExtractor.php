<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Cache\Cache;
use Attributes\Validation\Cache\FileBasedCache;
use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Generator;

class CacheRulesExtractor implements PropertyRulesExtractor
{
    private PropertyRulesExtractor $mainRuleExtractor;

    private ?Cache $cache = null;

    public function __construct(PropertyRulesExtractor $mainRuleExtractor)
    {
        $this->mainRuleExtractor = $mainRuleExtractor;
    }

    /**
     * Yields all validation rules from multiple rules extractors
     *
     * @param  Property  $property  - Property to yield the rules from
     *
     * @throws ContextPropertyException
     */
    public function getRulesFromProperty(Property $property, Context $context): Generator
    {
        $cache = $this->getCache($context);
        if (! $cache->has($property)) {
            yield from $this->mainRuleExtractor->getRulesFromProperty($property, $context);

            return;
        }

        $allRules = $cache->load($property);
        foreach ($allRules as $rule) {
            yield $rule;
        }
    }

    /**
     * @throws ContextPropertyException
     */
    private function getCache(Context $context): Cache
    {
        if (! is_null($this->cache)) {
            return $this->cache;
        }

        $this->cache = $context->getOptionalGlobal(Cache::class);
        if (! is_null($this->cache)) {
            return $this->cache;
        }

        $filePath = $context->getOptionalGlobal('option.cache.filePath', './cache/rules.php');
        $forceRemoval = $context->getOptionalGlobal('option.cache.forceRemoval', true);
        $this->cache = new FileBasedCache($filePath, $forceRemoval);
        $context->setGlobal(Cache::class, $this->cache);

        return $this->cache;
    }
}
