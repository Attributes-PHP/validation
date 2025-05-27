<?php

/**
 * Holds logic to validate that a given value is a valid datetime
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use DateTime as BaseDateTime;
use DateTimeInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validator as v;

final class DateTime implements BaseType
{
    /**
     * Validates that a given property value is valid object
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid
     * @throws ContextPropertyException
     * @throws ReflectionException
     */
    public function validate(Property $property, Context $context): void
    {
        $value = $property->getValue();
        if ($value instanceof BaseDateTime) {
            return;
        }

        if ($value instanceof DateTimeInterface) {
            $value = BaseDateTime::createFromInterface($value);
            $property->setValue($value);

            return;
        }

        $format = $context->getOptional('datetime.format', DateTimeInterface::ATOM);
        $reflection = $property->getReflection();
        $datetimeAttributes = $reflection->getAttributes(Rules\DateTime::class);
        if (count($datetimeAttributes) == 0) {
            v::dateTime($format)->assert($value);
        } else {
            $reflectionClass = new ReflectionClass(Rules\DateTime::class);
            $reflectionProperty = new ReflectionProperty(Rules\DateTime::class, 'format');
            foreach ($datetimeAttributes as $attribute) {
                $rule = $reflectionClass->newInstanceArgs($attribute->getArguments());
                $rule->assert($value);
                $format = $reflectionProperty->getValue($rule);
            }
        }

        $timezone = $context->getOptional('datetime.timezone');
        $value = BaseDateTime::createFromFormat($format, (string) $value, $timezone);
        $property->setValue($value);
    }
}
