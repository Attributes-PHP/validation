<?php

/**
 * Holds interface for casting a given value
 */

namespace Attributes\Validation\Transformers\Types;

use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Property;
use Attributes\Validation\Transformers\PropertyTransformer;
use ReflectionClass;
use ReflectionException;

class AnyClass implements TypeCast
{
    private string $type;

    private PropertyTransformer $propertyTransformer;

    private bool $stopFirstError;

    public function __construct(string $type, PropertyTransformer $propertyTransformer, bool $stopFirstError)
    {
        $this->type = $type;
        $this->propertyTransformer = $propertyTransformer;
        $this->stopFirstError = $stopFirstError;
    }

    /**
     * Checks if a given value has the expected type
     *
     * @param  mixed  $value  - Value to cast
     * @param  bool  $strict  - Determines if a strict casting should be applied. This is ignored
     * @return mixed - Value properly cast
     *
     * @throws TransformException
     * @throws ReflectionException
     */
    public function cast(mixed $value, bool $strict): mixed
    {
        if (is_a($value, $this->type)) {
            return $value;
        }

        if (! class_exists($this->type)) {
            throw new TransformException("Unable to locate class '$this->type'");
        }

        if (! is_array($value)) {
            throw new TransformException("Unable to cast '$this->type'. Expected an array $value is not an array");
        }

        $class = new $this->type;
        $reflectionClass = new ReflectionClass($class);
        $errorInfo = new ErrorInfo;
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $property = new Property($reflectionProperty, $value[$propertyName]);
            try {
                $propertyValue = $this->propertyTransformer->transform($property);
                $reflectionProperty->setValue($class, $propertyValue);
            } catch (TransformException $error) {
                $errorInfo->addError($error, $propertyName);
                if ($this->stopFirstError) {
                    throw new TransformException("Unable to cast '$this->type'", $errorInfo, $error);
                }
            }
        }

        if ($errorInfo->hasErrors()) {
            throw new TransformException("Unable to cast '$this->type'", $errorInfo);
        }

        return $class;
    }
}
