<?php

declare(strict_types=1);

namespace Attributes\Validation;

use Attributes\Validation\Exceptions\ContextPropertyException;
use Attributes\Validation\Exceptions\ContinueValidationException;
use Attributes\Validation\Exceptions\StopValidationException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Validators\AttributesValidator;
use Attributes\Validation\Validators\ChainValidator;
use Attributes\Validation\Validators\PropertyValidator;
use Attributes\Validation\Validators\TypeHintValidator;
use ReflectionClass;
use ReflectionException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Factory;

class Validator implements Validatable
{
    private Context $context;

    private PropertyValidator $validator;

    /**
     * @throws ContextPropertyException
     */
    public function __construct(?PropertyValidator $validator = null, bool $stopFirstError = false, bool $strict = false, ?Context $context = null)
    {
        $this->context = $context ?? new Context;
        $this->context->set('option.stopFirstError', $stopFirstError);
        $this->context->set('option.strict', $strict);
        $this->validator = $this->context->getOptional(PropertyValidator::class, $validator) ?? $this->getDefaultPropertyValidator();
        $this->context->set(PropertyValidator::class, $this->validator);

        $factory = $this->context->getOptional(Factory::class) ?: new Factory;
        Factory::setDefaultInstance(
            $factory
                ->withRuleNamespace('Attributes\\Validation\\RulesExtractors\\Rules')
                ->withExceptionNamespace('Attributes\\Validation\\RulesExtractors\\Rules\\Exceptions')
        );
    }

    /**
     * Validates a given data according to a given model
     *
     * @param  array  $data  - Data to validate
     * @param  string|object  $model  - Model to validate against
     * @return object - Model populated with the validated data
     *
     * @throws ValidationException - If validation fails
     * @throws ContextPropertyException - If unable to retrieve a given context property
     * @throws ReflectionException
     */
    public function validate(array $data, string|object $model): object
    {
        $currentLevel = $this->context->getOptional('internal.recursionLevel', 0);
        $maxRecursionLevel = $this->context->getOptional('internal.maxRecursionLevel', 30);
        if ($maxRecursionLevel > 0 && $currentLevel > $maxRecursionLevel) {
            throw new ValidationException("Maximum recursion level reached. Current max recursion level is {$maxRecursionLevel}");
        }

        if (is_string($model) && ! class_exists($model)) {
            throw new ValidationException("Unable to find model class '$model'");
        }

        $validModel = is_string($model) ? new $model : $model;
        $reflectionClass = new ReflectionClass($validModel);
        $errorInfo = $this->context->getOptional(ErrorInfo::class) ?: new ErrorInfo($this->context);
        $this->context->set(ErrorInfo::class, $errorInfo, override: true);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $this->context->push('internal.currentProperty', $propertyName);

            if (! array_key_exists($propertyName, $data)) {
                if (! $reflectionProperty->isInitialized($validModel)) {
                    $errorInfo->addError("Missing required property '$propertyName'");
                }

                $this->context->pop('internal.currentProperty');

                continue;
            }

            $propertyValue = $data[$propertyName];
            $property = new Property($reflectionProperty, $propertyValue, $validModel::class);
            $this->context->set(Property::class, $property, override: true);

            try {
                $this->validator->validate($property, $this->context);
                $reflectionProperty->setValue($validModel, $property->getValue());
            } catch (ValidationException|RespectValidationException $error) {
                if ($error->getMessage() == 'Invalid data' && $this->context->get('option.stopFirstError')) {
                    break;
                }

                $errorInfo->addError($error);
            } catch (ContinueValidationException $error) {
            } catch (StopValidationException $error) {
                break;
            } finally {
                $this->context->pop('internal.currentProperty');
            }
        }

        if ($errorInfo->hasErrors()) {
            throw new ValidationException('Invalid data', $errorInfo);
        }

        return $validModel;
    }

    private function getDefaultPropertyValidator(): PropertyValidator
    {
        $chainRulesExtractor = new ChainValidator;
        $chainRulesExtractor->add(new TypeHintValidator);
        $chainRulesExtractor->add(new AttributesValidator);

        return $chainRulesExtractor;
    }
}
