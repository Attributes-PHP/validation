<?php

declare(strict_types=1);

namespace Attributes\Validation;

use Attributes\Options;
use Attributes\Options\Exceptions\InvalidOptionException;
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
use ReflectionProperty;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Factory;

class Validator implements Validatable
{
    protected Context $context;

    protected PropertyValidator $validator;

    /**
     * @throws ContextPropertyException
     */
    public function __construct(?PropertyValidator $validator = null, bool $stopFirstError = false, bool $strict = false, ?Context $context = null)
    {
        $this->context = $context ?? new Context;
        $this->context->set('option.stopFirstError', $stopFirstError);
        $this->context->set('option.strict', $strict);
        $this->context->set('option.alias.generator', fn (string $name) => $name);
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
     * @throws InvalidOptionException
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
        $errorInfo = $this->context->getOptional(ErrorHolder::class) ?: new ErrorHolder($this->context);
        $this->context->set(ErrorHolder::class, $errorInfo, override: true);
        $defaultAliasGenerator = $this->getDefaultAliasGenerator($reflectionClass);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (! $this->isToValidate($reflectionProperty)) {
                continue;
            }

            $propertyName = $reflectionProperty->getName();
            $aliasName = $this->getAliasName($reflectionProperty, $defaultAliasGenerator);
            $this->context->push('internal.currentProperty', $propertyName);

            if (! array_key_exists($aliasName, $data)) {
                if (! $reflectionProperty->isInitialized($validModel)) {
                    $errorInfo->addError("Missing required property '$aliasName'");
                }

                $this->context->pop('internal.currentProperty');

                continue;
            }

            $propertyValue = $data[$aliasName];
            $property = new Property($reflectionProperty, $propertyValue);
            $this->context->set(Property::class, $property, override: true);

            try {
                $this->validator->validate($property, $this->context);
                $reflectionProperty->setValue($validModel, $property->getValue());
            } catch (ValidationException|RespectValidationException $error) {
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

    protected function getDefaultPropertyValidator(): PropertyValidator
    {
        $chainRulesExtractor = new ChainValidator;
        $chainRulesExtractor->add(new TypeHintValidator);
        $chainRulesExtractor->add(new AttributesValidator);

        return $chainRulesExtractor;
    }

    /**
     * Retrieves the default alias generator for a given class
     *
     * @throws ContextPropertyException
     * @throws InvalidOptionException
     */
    protected function getDefaultAliasGenerator(ReflectionClass $reflectionClass): callable
    {
        $allAttributes = $reflectionClass->getAttributes(Options\AliasGenerator::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();

            return $instance->getAliasGenerator();
        }

        $aliasGenerator = $this->context->get('option.alias.generator');
        if (is_callable($aliasGenerator)) {
            return $aliasGenerator;
        }

        $aliasGenerator = new Options\AliasGenerator($aliasGenerator);

        return $aliasGenerator->getAliasGenerator();
    }

    /**
     * Retrieves the alias for a given property
     */
    protected function getAliasName(ReflectionProperty $reflectionProperty, callable $defaultAliasGenerator): string
    {
        $propertyName = $reflectionProperty->getName();
        $allAttributes = $reflectionProperty->getAttributes(Options\Alias::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();

            return $instance->getAlias($propertyName);
        }

        return $defaultAliasGenerator($propertyName);
    }

    /**
     * Checks if a given property is to be ignored
     */
    protected function isToValidate(ReflectionProperty $reflectionProperty): bool
    {
        $allAttributes = $reflectionProperty->getAttributes(Options\Ignore::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();

            return ! $instance->ignoreValidation();
        }

        return true;
    }
}
