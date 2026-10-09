<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use ArrayAccess;
use Attributes\Validation\BaseModel;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Calls a callable with validated models: BaseModel parameters hydrate
 * from the raw data array, every other parameter is injected from a
 * dependencies array or ArrayAccess container.
 *
 * @internal
 */
final class Caller
{
    /**
     * @param  array<string|int, mixed>  $params
     * @param  array<int|string, mixed>|ArrayAccess<array-key, mixed>  $dependencies
     */
    public static function call(string|callable $function, array $params, array|ArrayAccess $dependencies): mixed
    {
        $reflection = self::reflect($function);
        $isInternal = $reflection->isInternal();

        $arguments = [];
        $cursor = 0;
        $parameters = $reflection->getParameters();
        $requiredCount = self::requiredCount($parameters);

        foreach ($parameters as $parameter) {
            $modelClass = $isInternal ? null : self::baseModelClass($parameter);

            if ($modelClass !== null) {
                $model = (new ReflectionClass($modelClass))->newInstanceWithoutConstructor();
                $arguments[] = Hydrator::hydrate($params, $model);

                continue;
            }

            $dependency = self::findDependency($dependencies, $isInternal ? null : $parameter->getName(), $cursor);

            if ($dependency !== null) {
                $arguments[] = $dependency['value'];

                continue;
            }

            if ($parameter->getPosition() >= $requiredCount) {
                break;
            }

            if ($isInternal) {
                throw new \Error(sprintf(
                    'No value provided for parameter %d of %s().',
                    $parameter->getPosition() + 1,
                    $reflection->getName()
                ));
            }

            throw new \Error(sprintf(
                'No value provided for parameter $%s of %s().',
                $parameter->getName(),
                $reflection->getName()
            ));
        }

        if ($reflection->isVariadic()) {
            while (($dependency = self::nextPositional($dependencies, $cursor)) !== null) {
                $arguments[] = $dependency['value'];
            }
        }

        return self::invoke($function, $reflection, $arguments);
    }

    /**
     * The reflection of the target callable.
     */
    private static function reflect(string|callable $function): ReflectionFunctionAbstract
    {
        if ($function instanceof \Closure) {
            return new ReflectionFunction($function);
        }

        if (is_string($function) && function_exists($function)) {
            return new ReflectionFunction($function);
        }

        if (is_string($function) && str_contains($function, '::')) {
            [$class, $method] = explode('::', $function, 2);

            return new ReflectionMethod($class, $method);
        }

        if (is_array($function) && count($function) === 2) {
            return new ReflectionMethod($function[0], $function[1]);
        }

        if (is_object($function) && is_callable($function)) {
            return new ReflectionMethod($function, '__invoke');
        }

        throw new \TypeError(sprintf(
            'call(): Argument #1 ($function) must be of type string|callable, %s given',
            Value::typeName($function)
        ));
    }

    /**
     * The number of required (leading) parameters of the callable.
     *
     * @param  array<int, ReflectionParameter>  $parameters
     */
    private static function requiredCount(array $parameters): int
    {
        $required = 0;

        foreach ($parameters as $parameter) {
            if ($parameter->isVariadic() || $parameter->isDefaultValueAvailable()) {
                break;
            }

            $required++;
        }

        return $required;
    }

    /**
     * The BaseModel subclass of a single class-typed parameter, when any.
     */
    private static function baseModelClass(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $class = ltrim($type->getName(), '\\');

        if (strcasecmp($class, 'self') === 0 || strcasecmp($class, 'parent') === 0) {
            return null;
        }

        if (! class_exists($class) || ! is_subclass_of($class, BaseModel::class)) {
            return null;
        }

        return $class;
    }

    /**
     * Finds the dependency for a non-model parameter: matched by parameter
     * name first, then in positional order.
     *
     * @return array{value: mixed}|null
     */
    private static function findDependency(array|ArrayAccess $dependencies, ?string $name, int &$cursor): ?array
    {
        if ($name !== null) {
            if (is_array($dependencies) && array_key_exists($name, $dependencies)) {
                return ['value' => $dependencies[$name]];
            }

            if ($dependencies instanceof ArrayAccess && $dependencies->offsetExists($name)) {
                return ['value' => $dependencies->offsetGet($name)];
            }
        }

        return self::nextPositional($dependencies, $cursor);
    }

    /**
     * Takes the next unused positional entry of the dependencies source.
     * Named entries are matched against parameter names and skipped here.
     *
     * @return array{value: mixed}|null
     */
    private static function nextPositional(array|ArrayAccess $dependencies, int &$cursor): ?array
    {
        if (is_array($dependencies)) {
            $count = count($dependencies);

            while ($cursor < $count) {
                $current = $cursor;
                $cursor++;

                if (array_key_exists($current, $dependencies)) {
                    return ['value' => $dependencies[$current]];
                }
            }

            return null;
        }

        $current = $cursor;
        $cursor++;

        if (! $dependencies->offsetExists($current)) {
            return null;
        }

        return ['value' => $dependencies->offsetGet($current)];
    }

    /**
     * Invokes the callable with the built argument list.
     *
     * @param  array<int, mixed>  $arguments
     */
    private static function invoke(string|callable $function, ReflectionFunctionAbstract $reflection, array $arguments): mixed
    {
        if ($reflection instanceof ReflectionMethod) {
            if (is_array($function)) {
                return $reflection->invokeArgs($function[0], $arguments);
            }

            if (is_string($function) && str_contains($function, '::')) {
                return $reflection->invokeArgs(null, $arguments);
            }

            if (is_object($function)) {
                return $reflection->invokeArgs($function, $arguments);
            }
        }

        return $function(...$arguments);
    }
}
