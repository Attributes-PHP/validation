<?php

namespace Attributes\Validation\Transformers;

use Attributes\Validation\Property;

interface PropertyTransformer
{
    public function transform(Property $property): mixed;
}
