<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Property;
use Generator;

class ChainRulesExtractor implements PropertyRulesExtractor
{
    private array $rulesExtractors;

    public function __construct(...$rulesExtractors)
    {
        $this->rulesExtractors = $rulesExtractors;
    }

    /**
     * Yields all validation rules from multiple rules extractors
     *
     * @param  Property  $property  - Property to yield the rules from
     */
    public function getRulesFromProperty(Property $property): Generator
    {
        foreach ($this->rulesExtractors as $rulesExtractor) {
            foreach ($rulesExtractor->getRulesFromProperty($property) as $rule) {
                yield $rule;
            }
        }
    }

    public function add(PropertyRulesExtractor $rulesExtractor): void
    {
        $this->rulesExtractors[] = $rulesExtractor;
    }
}
