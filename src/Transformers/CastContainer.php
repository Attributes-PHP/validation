<?php

namespace Attributes\Validation\Transformers;

use Attributes\Validation\Transformers\Types\TypeCast;

interface CastContainer
{
    public function getTypeCastInstance(string $propertyTypeName): TypeCast;
}
