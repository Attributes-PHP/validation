<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators;

use Attributes\Validation\Context;
use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use ReflectionClass;
use ReflectionException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
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
        $allAttributes = $property->getReflection()->getAttributes();
        if (! $allAttributes) {
            return;
        }

        $errorInfo = $context->getGlobal(ErrorInfo::class);
        foreach ($allAttributes as $attribute) {
            if (! is_subclass_of($attribute->getName(), Validatable::class)) {
                continue;
            }

            // This should be changed in the future with $attribute->newInstance once each rule is marked with #[Attribute]
            $reflectionClass = new ReflectionClass($attribute->getName());
            $rule = $reflectionClass->newInstanceArgs($attribute->getArguments());
            try {
                $rule->assert($property->getValue());
            } catch (RespectValidationException $error) {
                $errorInfo->addError($error);
            }
        }
    }
}
