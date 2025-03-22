<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Context;
use Attributes\Validation\Property;
use Generator;
use ReflectionClass;
use Respect\Validation\Validatable;

class RespectAttributesRulesExtractor implements PropertyRulesExtractor
{
    /**
     * Yields each validation rule of a given property
     *
     * @param  Property  $property  - Property to yield the rules from
     * @param  Context  $context  - The current validation context
     * @return Generator<Rules\AbstractRule>
     */
    public function getRulesFromProperty(Property $property, Context $context): Generator
    {
        $allAttributes = $property->getReflection()->getAttributes();
        if (! $allAttributes) {
            return;
        }

        foreach ($allAttributes as $attribute) {
            if (! is_subclass_of($attribute->getName(), Validatable::class)) {
                continue;
            }

            // This could be changed in the future with $attribute->newInstance once each rule is marked as Attribute
            $reflectionClass = new ReflectionClass($attribute->getName());
            yield $reflectionClass->newInstanceArgs($attribute->getArguments());
        }
    }
}
