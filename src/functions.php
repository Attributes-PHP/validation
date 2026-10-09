<?php

declare(strict_types=1);

namespace Attributes\Validation;

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
