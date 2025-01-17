<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Property;
use Generator;

interface PropertyRulesExtractor
{
    /**
     * @param  Property  $property  - Property which will hold value if valid
     * @return Generator - Extracted rules from property
     */
    public function getRulesFromProperty(Property $property): Generator;
}
