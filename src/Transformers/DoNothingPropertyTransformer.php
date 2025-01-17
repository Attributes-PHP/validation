<?php

namespace Attributes\Validation\Transformers;

use Attributes\Validation\Property;

class DoNothingPropertyTransformer implements PropertyTransformer
{
    /**
     * Simply returns the same value. Can be useful if we want to keep the original value
     *
     * @param  Property  $property  - Property to cast
     * @param  mixed  $value  - Value to be cast
     * @return mixed - Cast value
     */
    public function transform(Property $property, mixed $value): mixed
    {
        return $value;
    }
}
