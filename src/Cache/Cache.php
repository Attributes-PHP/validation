<?php

/**
 * Holds interface for loading cached model rules
 */

declare(strict_types=1);

namespace Attributes\Validation\Cache;

use Attributes\Validation\Property;
use Respect\Validation\Validatable;

interface Cache
{
    /**
     * @param  Property  $property  - The property to retrieve the validation rules
     * @return array<Validatable>
     */
    public function load(Property $property): array;

    /**
     * Checks if the cache holds any validation rules regarding this property
     *
     * @param  Property  $property  - The property in question
     */
    public function has(Property $property): bool;

    /**
     * Saves a set of rules for a given property into the cache
     *
     * @param  Property  $property  - The property in question
     * @param  array  $rules  - The validation rules to be associated with the model
     */
    public function save(Property $property, array $rules): void;
}
