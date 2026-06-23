<?php

declare(strict_types=1);

namespace Attributes\Validation;

use ArrayObject;
use Attributes\Options;
use Attributes\Validation\Cache\ReflectionCache;
use Attributes\Validation\Exceptions\ContinueValidationException;
use Attributes\Validation\Exceptions\StopValidationException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Validators\AttributesValidator;
use Attributes\Validation\Validators\ChainValidator;
use Attributes\Validation\Validators\PropertyValidator;
use Attributes\Validation\Validators\TypeHintValidator;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionProperty;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Factory;

class Validator implements Validatable
{
    protected Context $context;

    protected PropertyValidator $validator;

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

    public function validate(array|ArrayObject $data, string|object $model): object
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

        $className = is_string($model) ? $model : $validModel::class;

        $reflectionClass = ReflectionCache::getClassReflection($className);
        $properties = ReflectionCache::getProperties($reflectionClass);

        $errorInfo = $this->context->getOptional(ErrorHolder::class) ?: new ErrorHolder($this->context);
        $this->context->set(ErrorHolder::class, $errorInfo, override: true);
        $defaultAliasGenerator = $this->getDefaultAliasGenerator($reflectionClass);

        foreach ($properties as $reflectionProperty) {
            if (! $this->isToValidate($reflectionProperty)) {
                continue;
            }

            $propertyName = $reflectionProperty->getName();
            $aliasName = $this->getAliasName($reflectionProperty, $defaultAliasGenerator);
            $this->context->push('internal.currentProperty', $propertyName);

            if (! array_key_exists($aliasName, (array) $data)) {
                if (! $reflectionProperty->isInitialized($validModel)) {
                    try {
                        $errorInfo->addError("Missing required property '$aliasName'");
                    } catch (StopValidationException $e) {
                        break;
                    }
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

    public function validateCallable(array|ArrayObject $data, callable $call): array
    {
        $arguments = [];
        $reflectionFunction = new ReflectionFunction($call);
        $errorInfo = $this->context->getOptional(ErrorHolder::class) ?: new ErrorHolder($this->context);
        $this->context->set(ErrorHolder::class, $errorInfo, override: true);
        $defaultAliasGenerator = $this->getDefaultAliasGenerator($reflectionFunction);

        $parameters = $reflectionFunction->getParameters();

        foreach ($parameters as $index => $parameter) {
            if (! $this->isToValidate($parameter)) {
                continue;
            }

            $propertyName = $parameter->getName();
            $aliasName = $this->getAliasName($parameter, $defaultAliasGenerator);
            $this->context->push('internal.currentProperty', $propertyName);

            $propertyValue = $data[$index] ?? $data[$aliasName] ?? null;
            if (! array_key_exists($index, (array) $data) && ! array_key_exists($aliasName, (array) $data)) {
                if (! $parameter->isDefaultValueAvailable()) {
                    try {
                        $errorInfo->addError("Missing required argument '$aliasName'");
                    } catch (StopValidationException $error) {
                        break;
                    }
                }

                $this->context->pop('internal.currentProperty');

                continue;
            }

            $property = new Property($parameter, $propertyValue);
            $this->context->set(Property::class, $property, override: true);

            try {
                $this->validator->validate($property, $this->context);
                $arguments[$parameter->getName()] = $property->getValue();
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

        return $arguments;
    }

    protected function getDefaultPropertyValidator(): PropertyValidator
    {
        $chainRulesExtractor = new ChainValidator;
        $chainRulesExtractor->add(new TypeHintValidator);
        $chainRulesExtractor->add(new AttributesValidator);

        return $chainRulesExtractor;
    }

    protected function getDefaultAliasGenerator(ReflectionClass|ReflectionFunction $reflection): callable
    {
        $allAttributes = $reflection->getAttributes(Options\AliasGenerator::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();

            return $instance->getAliasGenerator();
        }

        $aliasGenerator = $this->context->get('option.alias.generator');
        if (is_callable($aliasGenerator)) {
            return $aliasGenerator;
        }

        $aliasGeneratorClass = new Options\AliasGenerator($aliasGenerator);

        return $aliasGeneratorClass->getAliasGenerator();
    }

    protected function getAliasName(ReflectionProperty|ReflectionParameter $reflection, callable $defaultAliasGenerator): string
    {
        $propertyName = $reflection->getName();
        $allAttributes = $reflection->getAttributes(Options\Alias::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();

            return $instance->getAlias($propertyName);
        }

        return $defaultAliasGenerator($propertyName);
    }

    protected function isToValidate(ReflectionProperty|ReflectionParameter $reflection): bool
    {
        $useSerialization = $this->context->getOptional('internal.options.ignore.useSerialization', false);
        $allAttributes = $reflection->getAttributes(Options\Ignore::class);
        foreach ($allAttributes as $attribute) {
            $instance = $attribute->newInstance();

            return $useSerialization ? ! $instance->ignoreSerialization() : ! $instance->ignoreValidation();
        }

        return true;
    }
}
