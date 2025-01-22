<?php

namespace Attributes\Validation\Validators\RulesExtractors;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Attributes\Validation\Validators\Rules as TypeRules;
use DateTime;
use DateTimeInterface;
use Generator;
use ReflectionNamedType;
use ReflectionUnionType;
use Respect\Validation\Rules as Rules;
use Respect\Validation\Rules\Core\Simple;

class RespectTypeHintRulesExtractor implements PropertyRulesExtractor
{
    private array $typeHintRules;

    public function __construct(?array $typeHintRules = null, bool $strict = false)
    {
        $this->typeHintRules = $typeHintRules ?? $this->getDefaultRules($strict);
    }

    /**
     * Yields each validation rule of a given property
     *
     * @param  Property  $property  - Property to yield the rules from
     * @return Generator<Simple>
     *
     * @throws ValidationException
     */
    public function getRulesFromProperty(Property $property): Generator
    {
        $reflectionProperty = $property->getReflection();
        if (! $reflectionProperty->hasType()) {
            return;
        }

        $propertyType = $reflectionProperty->getType();
        if ($propertyType instanceof ReflectionNamedType) {
            if (! isset($this->typeHintRules[$propertyType->getName()])) {
                return;
            }
            yield $this->typeHintRules[$propertyType->getName()];
        } elseif ($propertyType instanceof ReflectionUnionType) {
            $rules = [];
            $mapping = [];

            foreach ($propertyType->getTypes() as $type) {
                if (! isset($this->typeHintRules[$type->getName()])) {
                    continue;
                }

                $rule = $this->typeHintRules[$type->getName()];
                $rules[] = $rule;
                $mapping[$rule->getName()] = $type->getName();
            }
            if (! $rules) {
                throw new ValidationException("Missing Union rules for {$property->getName()}");
            }

            yield new TypeRules\Union($mapping, ...$rules);
        }
    }

    /**
     * Retrieves default type hint rules. If strict, retrieves strict type checking otherwise
     * does a loose check.
     */
    private function getDefaultRules(bool $strict): array
    {
        if ($strict) {
            return [
                'bool' => new Rules\BoolType,
                'int' => new Rules\IntType,
                'float' => new Rules\FloatType,
                'string' => new Rules\StringType,
                'array' => new Rules\ArrayType,
                'object' => new Rules\ObjectType,
                DateTime::class => new Rules\DateTime,
                DateTimeInterface::class => new Rules\DateTime,
            ];
        }

        return [
            'bool' => new Rules\BoolVal,
            'int' => new Rules\IntVal,
            'float' => new Rules\FloatVal,
            'string' => new Rules\StringVal,
            'array' => new Rules\ArrayVal,
            'object' => new Rules\ObjectType,
            DateTime::class => new Rules\DateTime,
            DateTimeInterface::class => new Rules\DateTime,
        ];
    }
}
