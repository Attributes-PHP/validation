<?php

namespace Attributes\Validation\Validators;

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

class RespectPropertyValidator implements PropertyValidator
{
    private PropertyRulesExtractor $extractor;

    public function __construct(?PropertyRulesExtractor $extractor = null, bool $strict = false)
    {
        $this->extractor = $extractor ?? new ChainRulesExtractor(
            new RespectTypeHintRulesExtractor(strict: $strict),
            new RespectAttributesRulesExtractor,
        );
    }

    /**
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function validate(Property $property): void
    {
        $validationResult = new ValidationResult;
        foreach ($this->extractor->getRulesFromProperty($property) as $rule) {
            try {
                $rule->assert($property->getValue());
                if ($rule instanceof Union) {
                    $property->addTypeHintSuggestion($rule->getValidTypeHint());
                }
            } catch (RespectValidationException $error) {
                $validationResult->addError($error, $property->getName());
            }
        }

        if ($validationResult->hasErrors()) {
            $propertyName = $property->getName();
            throw new ValidationException("Invalid property '$propertyName'", $validationResult);
        }
    }
}
