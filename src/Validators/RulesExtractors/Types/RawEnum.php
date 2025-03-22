<?php

/**
 * Holds logic validation rules used to verify if a given value is null
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Validators\RulesExtractors\RespectTypeHintRulesExtractor;
use ReflectionEnum;
use ReflectionException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawEnum implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is null
     *
     * @param  Context  $context  - Validation context
     *
     * @throws ContextPropertyException - When unable to find required context property
     * @throws ReflectionException - When given type-hint property is not an enum
     */
    public function extract(Context $context): Validatable
    {
        $typeHint = $context->getLocal('property.typeHint');
        $typeHintExtractor = $context->getLocal(RespectTypeHintRulesExtractor::class);

        $reflectionEnum = new ReflectionEnum($typeHint);
        $typeHintName = $reflectionEnum->getBackingType()->getName() ?: 'string';
        $ruleExtractor = $allRules[$typeHintName] ?? $typeHintExtractor->getRules()['default'];
        $rule = $ruleExtractor->extract($context);

        return $context->getGlobal('option.strict') ? new Rules\Nullable($rule) : new Rules\Optional($rule);
    }
}
