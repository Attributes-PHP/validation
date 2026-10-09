<?php

declare(strict_types=1);

namespace Attributes\Validation;

use ArrayAccess;
use Attributes\Validation\Internal\Caller;
use Attributes\Validation\Internal\Hydrator;

/**
 * Validates a raw data array against the type hints and attributes of a
 * BaseModel instance, and returns the hydrated model.
 *
 * @throws Exceptions\ValidationException when any field fails validation
 */
function validate(array $rawData, BaseModel $model): BaseModel
{
    return Hydrator::hydrate($rawData, $model);
}

/**
 * Calls a function or callable with validated models: BaseModel parameters
 * hydrate from the raw data array through the same engine validate() uses,
 * and every other parameter is injected from a dependencies array or
 * ArrayAccess container.
 */
function call(string|callable $function, array $params, array|ArrayAccess $dependencies = []): mixed
{
    return Caller::call($function, $params, $dependencies);
}
