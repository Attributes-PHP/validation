<?php

declare(strict_types=1);

namespace Attributes\Validation;

use Attributes\Validation\Exceptions\BaseException;
use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Transformers\CastPropertyTransformer;
use Attributes\Validation\Transformers\PropertyTransformer;
use Attributes\Validation\Validators\PropertyValidator;
use Attributes\Validation\Validators\RespectPropertyValidator;
use ReflectionClass;
use ReflectionException;

class Validator implements Validatable
{
    private PropertyTransformer $transformer;

    private PropertyValidator $validator;

    private Context $context;

    /**
     * @throws ContextPropertyException
     */
    public function __construct(?PropertyValidator $validator = null, ?PropertyTransformer $transformer = null, bool $stopFirstError = false, bool $strict = false, ?Context $context = null)
    {
        $this->validator = $validator ?? new RespectPropertyValidator;
        $this->transformer = $transformer ?? new CastPropertyTransformer;
        $this->context = $context ?? new Context;
        $this->context->setGlobal('option.stopFirstError', $stopFirstError);
        $this->context->setGlobal('option.strict', $strict);
    }

    /**
     * Validates a given data according to a given model
     *
     * @param  array  $data  - Data to validate
     * @param  object  $model  - Model to validate against
     * @return object - Model populated with the validated data
     *
     * @throws ValidationException - If validation fails
     * @throws ContextPropertyException - If unable to retrieve a given context property
     */
    public function validate(array $data, object $model): object
    {
        $validModel = clone $model;
        $reflectionClass = new ReflectionClass($validModel);
        $errorInfo = new ErrorInfo($this->context);
        $this->context->setGlobal(ErrorInfo::class, $errorInfo);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();

            if (! array_key_exists($propertyName, $data)) {
                if (! $reflectionProperty->isInitialized($model)) {
                    $errorInfo->addError("Missing required property '$propertyName'");
                }

                continue;
            }

            $propertyValue = $data[$propertyName];
            $property = new Property($reflectionProperty, $propertyValue);
            $this->context->setGlobal(Property::class, $property, override: true);
            try {
                $this->validator->validate($property, $this->context);
                $this->context->resetLocal();
                $value = $this->transformer->transform($property, $this->context);
                $reflectionProperty->setValue($validModel, $value);
            } catch (BaseException $error) {
                $errorInfo->addError($error);
            } catch (ReflectionException $error) {
                throw new ValidationException('Invalid base model property attributes', previous: $error);
            }
        }

        if ($errorInfo->hasErrors()) {
            throw new ValidationException('Invalid data', $errorInfo);
        }

        return $validModel;
    }
}
