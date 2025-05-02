<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators;

use Attributes\Validation\Context;
use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\RulesExtractors\CacheRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\ChainRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\PropertyRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\RespectAttributesRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\RespectTypeHintRulesExtractor;
use ReflectionException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Factory;

class RespectPropertyValidator implements PropertyValidator
{
    private PropertyRulesExtractor $extractor;

    public function __construct(Context $context, ?PropertyRulesExtractor $extractor = null)
    {
        $this->extractor = $extractor ?? $this->getDefaultExtractor($context);
        $context->setGlobal(PropertyRulesExtractor::class, $this->extractor, override: true);

        Factory::setDefaultInstance(
            (new Factory)
                ->withRuleNamespace('Attributes\\Validation\\Validators\\Rules')
                ->withExceptionNamespace('Attributes\\Validation\\Validators\\Rules\\Exceptions')
        );
    }

    /**
     * @throws ValidationException
     * @throws ReflectionException
     * @throws ContextPropertyException
     */
    public function validate(Property $property, Context $context): void
    {
        $errorInfo = $context->getGlobal(ErrorInfo::class);
        foreach ($this->extractor->getRulesFromProperty($property, $context) as $rule) {
            try {
                $rule->assert($property->getValue());
            } catch (RespectValidationException $error) {
                $errorInfo->addError($error);
            }
        }

        if ($errorInfo->hasErrors()) {
            $propertyName = $property->getName();
            throw new ValidationException("Invalid property '$propertyName'", $errorInfo);
        }
    }

    /**
     * @throws ContextPropertyException
     */
    private function getDefaultExtractor(Context $context): PropertyRulesExtractor
    {
        $chainRulesExtractor = new ChainRulesExtractor;
        $chainRulesExtractor->add(new RespectTypeHintRulesExtractor);
        $chainRulesExtractor->add(new RespectAttributesRulesExtractor);

        if (! $context->getGlobal('option.cache.enabled')) {
            return $chainRulesExtractor;
        }

        return new CacheRulesExtractor($chainRulesExtractor);
    }
}
