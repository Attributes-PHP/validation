<?php

/**
 * Holds logic validation rules used to verify if a given value is an enum
 */

declare(strict_types=1);

namespace Attributes\Validation\Validators\RulesExtractors\Types;

use Attributes\Validation\Context;
use Attributes\Validation\Exceptions\ContextPropertyException;
use ReflectionEnum;
use ReflectionException;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Validatable;

class RawEnum implements TypeRespectExtractor
{
    /**
     * Retrieves the validation rules to check if a value is null
     *
     * @param  Context  $context  - Validation context
     *
     * @throws ContextPropertyException - When unable to find required context property
     * @throws ReflectionException - When given type-hint property is not an enum
     * @throws ComponentException
     */
    public function extract(Context $context): Validatable
    {
        $typeHint = $context->getLocal('property.typeHint');
        $reflectionEnum = new ReflectionEnum($typeHint);
        $validOptions = [];
        foreach ($typeHint::cases() as $case) {
            $validOptions[] = $reflectionEnum->getBackingType() ? $case->value : $case->name;
        }

        $isStrict = $context->getGlobal('option.strict');
        // Int enum's do fail equals validation with some invalid data e.g. ['this is an array of strings']
        $isStrict = $reflectionEnum->getBackingType() && $reflectionEnum->getBackingType()->getName() == 'int' ? true : $isStrict;

        return new Rules\AnyOf(new Rules\Instance($typeHint), new Rules\In($validOptions, compareIdentical: $isStrict));
    }
}
