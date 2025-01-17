<?php

namespace Attributes\Validation\Validators;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\RulesExtractors\ChainRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\PropertyRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\RespectAttributesRulesExtractor;
use Attributes\Validation\Validators\RulesExtractors\RespectTypeHintRulesExtractor;
use ReflectionException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;

class RespectPropertyValidator implements PropertyValidator
{
    private PropertyRulesExtractor $extractor;

    public function __construct(?PropertyRulesExtractor $extractor = null)
    {
        $this->extractor = $extractor ?? new ChainRulesExtractor(
            new RespectTypeHintRulesExtractor,
            new RespectAttributesRulesExtractor,
        );
    }

    /**
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function validate(Property $property): void
    {
        $validationResult = new PropertyValidationResult($property);
        foreach ($this->extractor->getRulesFromProperty($property) as $rule) {
            try {
                $rule->assert($property->getValue());
            } catch (RespectValidationException $error) {
                $validationResult->addError($error);
            }
        }

        if ($validationResult->hasErrors()) {
            $propertyName = $property->getName();
            throw new ValidationException("Invalid property '$propertyName'", $validationResult->getErrors());
        }
    }
}
