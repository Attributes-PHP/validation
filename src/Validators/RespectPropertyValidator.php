<?php

namespace Attributes\Validation\Validators;

use Attributes\Validation\Context;
use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\Rules\Union;
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

    public function __construct(?PropertyRulesExtractor $extractor = null)
    {
        $this->extractor = $extractor ?? $this->getDefaultExtractor();

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
        $context->setLocal(PropertyRulesExtractor::class, $this->extractor, override: true);
        $errorInfo = new ErrorInfo($property->getName());
        foreach ($this->extractor->getRulesFromProperty($property, $context) as $rule) {
            try {
                $rule->assert($property->getValue());
                if ($rule instanceof Union) {
                    $property->addTypeHintSuggestions($rule->getValidTypeHints());
                }
            } catch (RespectValidationException $error) {
                $errorInfo->addErrorMessage($error->getMessage());
            }
        }

        if ($errorInfo->hasErrors()) {
            $propertyName = $property->getName();
            throw new ValidationException("Invalid property '$propertyName'", $errorInfo);
        }
    }

    private function getDefaultExtractor(): PropertyRulesExtractor
    {
        $chainRulesExtractor = new ChainRulesExtractor;
        $chainRulesExtractor->add(new RespectTypeHintRulesExtractor);
        $chainRulesExtractor->add(new RespectAttributesRulesExtractor);

        return $chainRulesExtractor;
    }
}
