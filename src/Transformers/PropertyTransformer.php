<?php

declare(strict_types=1);

namespace Attributes\Validation\Transformers;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\TransformException;
use Attributes\Validation\Property;

interface PropertyTransformer
{
    /**
     * @throws TransformException - If unable to cast property value
     */
    public function transform(Property $property, Context $context): mixed;

    /**
     * @throws TransformException
     */
    public function getTypeCastInstance(string $propertyTypeName): Types\TypeCast;
}
