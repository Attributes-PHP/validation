<?php

declare(strict_types=1);

namespace Attributes\Validation;

abstract class BaseModel
{
    protected function beforeValidation(array $rawData, ModelConfigs $configs): array
    {
        return $rawData;
    }

    protected function afterValidation(array $rawData, ModelConfigs $configs): void {}
}
