<?php

/**
 * Holds logic validation rules used to verify if a given value is a valid class or has enough properties to build that class
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Property;
use Attributes\Validation\Validator;
use ReflectionException;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validator as v;

final class AnyClass implements BaseType
{
    /**
     * Validates that a given property value is valid array
     *
     * @param  Property  $property  - Property to be validated
     * @param  Context  $context  - Validation context
     *
     * @throws RespectValidationException - If not valid array
     * @throws ContextPropertyException
     */
    public function validate(Property $property, Context $context): void
    {
        $value = $property->getValue();
        $typeHint = $context->getLocal('property.typeHint');
        if ($value instanceof $typeHint) {
            return;
        }

        v::arrayVal()->assert($value);
        $clonedContext = clone $context;
        $clonedContext->resetLocal();
        $recursionLevel = $clonedContext->getOptionalGlobal('internal.recursionLevel', 0);
        $clonedContext->setGlobal('internal.recursionLevel', $recursionLevel + 1, override: true);
        $validator = new Validator(context: $clonedContext);
        $validModel = $validator->validate((array) $value, $typeHint);
        $property->setValue($validModel);
    }

    /**
     * @throws ContextPropertyException - When unable to find context properties
     * @throws ComponentException
     * @throws ReflectionException
     */
    //    private function extractClassPropertiesRules(Context $context): Validatable
    //    {
    //        $typeHint = $context->getLocal('property.typeHint');
    //        $rules = [];
    //        $model = new $typeHint;
    //        $reflectionClass = new ReflectionClass($typeHint);
    //        $rulesExtractor = $context->getGlobal(BaseRulesExtractor::class);
    //        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
    //            $property = new Property($reflectionProperty, null, $typeHint);
    //
    //            foreach ($rulesExtractor->getRulesFromProperty($property, $context) as $rule) {
    //                $isRequired = $this->isRequired($property, $model);
    //                $rules[] = new Rules\Key($property->getName(), $rule, $isRequired);
    //            }
    //        }
    //
    //        $ignoreExtraKeys = $context->getOptionalGlobal('option.ignoreExtraKeys', true);
    //
    //        return $ignoreExtraKeys ? new Rules\AllOf(...$rules) : new Rules\KeySet(...$rules);
    //    }
    //
    //    private function isRequired(Property $property, object $model): bool
    //    {
    //        $reflectionProperty = $property->getReflection();
    //        if ($reflectionProperty->hasDefaultValue()) {
    //            return false;
    //        }
    //
    //        return ! $reflectionProperty->isInitialized($model);
    //    }
}
