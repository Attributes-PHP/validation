<?php

declare(strict_types=1);

namespace Attributes\Validation\Types;

use ArrayObject;
use Attributes\Validation\Exceptions\ValidationException;

abstract class ArrayOf extends ArrayObject
{
    public function __construct(array $array = [])
    {
        if (! property_exists($this, 'type')) {
            throw new ValidationException('Missing property \'type\' in '.self::class);
        }
        parent::__construct($array);
    }
}
