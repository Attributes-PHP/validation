<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\Context;
use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Property;
use Attributes\Validation\Transformers\PropertyTransformer;
use ReflectionClass;
use ReflectionException;

class AnyClass implements TypeCast
{
    /**
     * Checks if a given value has the expected type
     *
     * @param  mixed  $value  - Value to cast
     * @param  Context  $context  - Validation context
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     * @throws ReflectionException
     * @throws ContextPropertyException - When unable to find context properties
     */
    public function cast(mixed $value, Context $context): mixed
    {
        $typeHint = $context->getLocal('property.typeHint');
        if (is_a($value, $typeHint)) {
            return $value;
        }

        if (! class_exists($typeHint)) {
            throw new TransformException("Unable to locate class '$typeHint'");
        }

        if (! is_array($value)) {
            throw new TransformException("Unable to cast '$typeHint'. Expected an array $value is not an array");
        }

        $class = new $typeHint;
        $reflectionClass = new ReflectionClass($class);
        $errorInfo = new ErrorInfo;
        $propertyTransformer = $context->getLocal(PropertyTransformer::class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $property = new Property($reflectionProperty, $value[$propertyName]);
            try {
                $propertyValue = $propertyTransformer->transform($property, $context);
                $reflectionProperty->setValue($class, $propertyValue);
            } catch (TransformException $error) {
                $errorInfo->addError($error, $propertyName);
                if ($context->getGlobal('option.stopFirstError')) {
                    throw new TransformException("Unable to cast '$typeHint'", $errorInfo, $error);
                }
            }
        }

        if ($errorInfo->hasErrors()) {
            throw new TransformException("Unable to cast '$typeHint'", $errorInfo);
        }

        return $class;
    }
}
