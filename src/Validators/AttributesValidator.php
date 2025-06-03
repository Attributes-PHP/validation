<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators;

use Attributes\Validation\Context;
use Attributes\Validation\ErrorHolder;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class AttributesValidator implements PropertyValidator
{
    /**
     * Yields each validation rule of a given property
     *
     * @param  Property  $property  - Property to yield the rules from
     * @param  Context  $context  - The current validation context
     *
     * @throws ReflectionException
     * @throws ContextPropertyException
     */
    public function validate(Property $property, Context $context): void
    {
        $allAttributes = $property->getReflection()->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! $allAttributes) {
            return;
        }

        $errorInfo = $context->get(ErrorHolder::class);
        foreach ($allAttributes as $attribute) {
            $className = $attribute->getName();
            if ($className == Rules\DateTime::class) {
                continue;
            }

            // This should be changed in the future with $attribute->newInstance once each rule is marked with #[Attribute]
            $reflectionClass = new ReflectionClass($className);
            $rule = $reflectionClass->newInstanceArgs($attribute->getArguments());
            try {
                $rule->assert($property->getValue());
            } catch (RespectValidationException $error) {
                $errorInfo->addError($error);
            }
        }
    }
}
