<?php

namespace Attributes\Validation;

use Attributes\Validation\Exceptions\BaseException;
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

    private bool $stopAtFirstError;

    public function __construct(?PropertyValidator $validator = null, ?PropertyTransformer $transformer = null, bool $stopAtFirstError = false, bool $strict = false)
    {
        $this->validator = $validator ?? new RespectPropertyValidator(strict: $strict);
        $this->transformer = $transformer ?? new CastPropertyTransformer(strict: $strict);
        $this->stopAtFirstError = $stopAtFirstError;
    }

    /**
     * Validates a given data according to a given model
     *
     * @param  array  $data  - Data to validate
     * @param  object  $model  - Model to validate against
     * @return object - Model populated with the validated data
     *
     * @throws ValidationException - If validation fails
     */
    public function validate(array $data, object $model): object
    {
        if (! $data) {
            throw new ValidationException('No data to validate');
        }

        $validModel = clone $model;
        $reflectionClass = new ReflectionClass($validModel);
        $validationResult = new ValidationResult;
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();

            if (! array_key_exists($propertyName, $data)) {
                if (! $reflectionProperty->isInitialized($model)) {
                    $validationResult->addErrorMessage("Missing required property '$propertyName'", $propertyName);
                    if ($this->stopAtFirstError) {
                        throw new ValidationException('Validation failed', $validationResult);
                    }
                }

                continue;
            }

            $propertyValue = $data[$propertyName];
            $property = new Property($reflectionProperty, $propertyValue);
            try {
                $this->validator->validate($property);
                $value = $this->transformer->transform($property);
                $reflectionProperty->setValue($validModel, $value);
            } catch (BaseException $error) {
                $validationResult->addError($error, $propertyName);
                if ($this->stopAtFirstError) {
                    throw new ValidationException('Invalid data', $validationResult, previous: $error);
                }
            } catch (ReflectionException $error) {
                throw new ValidationException('Invalid base model property attributes', previous: $error);
            }
        }

        if ($validationResult->hasErrors()) {
            throw new ValidationException('Invalid data', $validationResult);
        }

        return $validModel;
    }
}
