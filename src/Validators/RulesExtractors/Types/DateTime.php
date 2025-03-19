<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid datetime
 */

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\RulesExtractors\PropertiesContainer;
use DateTimeInterface;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class DateTime implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is a valid datetime
     *
     * @param  bool  $strict  - Determines if a strict validation rule should be applied. True for strict validation or else otherwise
     * @param  PropertiesContainer  $propertiesContainer  - Additional properties which could influence the validation rules. Optional 'format'
     *
     * @throws ValidationException
     */
    public function extract(bool $strict, PropertiesContainer $propertiesContainer): Validatable
    {
        $format = $this->getFormat($propertiesContainer);

        return new Rules\AnyOf(new Rules\DateTime(format: $format), new Rules\Instance(DateTimeInterface::class));
    }

    /**
     * @throws ValidationException
     */
    private function getFormat(PropertiesContainer $propertiesContainer): string
    {
        $property = $propertiesContainer->getProperty('property');
        if (! ($property instanceof Property)) {
            throw new ValidationException('Invalid property type');
        }

        $reflectionProperty = $property->getReflection();
        $reflectionProperty->getAttributes();

        return DateTimeInterface::ATOM;
    }
}
