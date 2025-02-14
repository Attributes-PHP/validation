<?php

namespace Attributes\Validation\Transformers;

use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Property;

interface PropertyTransformer
{
    /**
     * @throws TransformException - If unable to cast property value
     */
    public function transform(Property $property): mixed;
}
