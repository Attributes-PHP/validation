<?php

namespace Attributes\Validation\Validators;

use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\Rules\Union;
use Attributes\Validation\Validators\RulesExtractors\ChainRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\PropertyRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\RespectAttributesRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\RespectTypeHintRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\Types as TypeExtractors;
use ReflectionException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Factory;

class RespectPropertyValidator implements PropertyValidator
{
    private PropertyRulesExtractor $extractor;

    public function __construct(?PropertyRulesExtractor $extractor = null, bool $strict = false)
    {
        $this->extractor = $extractor ?? $this->getDefaultExtractor($strict);

        Factory::setDefaultInstance(
            (new Factory)
                ->withRuleNamespace('Attributes\\Validation\\Validators\\Rules')
                ->withExceptionNamespace('Attributes\\Validation\\Validators\\Rules\\Exceptions')
        );
    }

    /**
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function validate(Property $property): void
    {
        $errorInfo = new ErrorInfo($property->getName());
        foreach ($this->extractor->getRulesFromProperty($property) as $rule) {
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

    private function getDefaultExtractor(bool $strict): PropertyRulesExtractor
    {
        $chainRulesExtractor = new ChainRulesExtractor;
        $typeHintRules = ['default' => new TypeExtractors\AnyClass($chainRulesExtractor)];
        $chainRulesExtractor->add(new RespectTypeHintRulesExtractor(typeHintRules: $typeHintRules, strict: $strict));
        $chainRulesExtractor->add(new RespectAttributesRulesExtractor);

        return $chainRulesExtractor;
    }
}
