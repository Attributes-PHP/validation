<?php

namespace Attributes\Validation\Validators;

use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\ValidationResult;
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

    public function __construct(?PropertyRulesExtractor $extractor = null, bool $strict = false)
    {
        $this->extractor = $extractor ?? new ChainRulesExtractor(
            new RespectTypeHintRulesExtractor(strict: $strict),
            new RespectAttributesRulesExtractor,
        );

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
        $errorInfo = new ErrorInfo;
        foreach ($this->extractor->getRulesFromProperty($property) as $rule) {
            try {
                $rule->assert($property->getValue());
                if ($rule instanceof Union) {
                    $property->addTypeHintSuggestions($rule->getValidTypeHints());
                }
            } catch (RespectValidationException $error) {
                $errorInfo->addError($error, $property->getName());
            }
        }

        if ($errorInfo->hasErrors()) {
            $propertyName = $property->getName();
            throw new ValidationException("Invalid property '$propertyName'", $errorInfo);
        }
    }
}
