<?php

declare(strict_types=1);

namespace Attributes\Validation\Options;

use Attribute;
use Attributes\Validation\Exceptions\InvalidOptionException;

#[Attribute(Attribute::TARGET_CLASS)]
class AliasGenerator
{
    private $aliasGenerator;

    /**
     * @param  string|callable  $aliasGenerator  - The alias generator. Either a callable or a string with either 'camel', 'pascal' or 'snake'
     */
    public function __construct(string|callable $aliasGenerator)
    {
        $this->aliasGenerator = $aliasGenerator;
    }

    /**
     * @throws InvalidOptionException
     */
    public function getAliasGenerator(): callable
    {
        if (is_callable($this->aliasGenerator)) {
            return $this->aliasGenerator;
        }

        switch ($this->aliasGenerator) {
            case 'camel':
                return $this->toCamel(...);
            case 'pascal':
                return $this->toPascal(...);
            case 'snake':
                return $this->toSnake(...);
            default:
                throw new InvalidOptionException("Invalid alias generator '$this->aliasGenerator'");
        }
    }

    /**
     * Converts a string into camelCase
     *
     * @taken https://github.com/symfony/string/blob/7.3/ByteString.php#camel
     */
    public function toCamel(string $propertyName): string
    {
        $parts = explode(' ', trim(ucwords(preg_replace('/[^a-zA-Z0-9\x7f-\xff]++/', ' ', $propertyName))));
        $parts[0] = \strlen($parts[0]) !== 1 && ctype_upper($parts[0]) ? $parts[0] : lcfirst($parts[0]);

        return implode('', $parts);
    }

    /**
     * Converts a string into PascalCase
     */
    public function toPascal(string $propertyName): string
    {
        $propertyName = $this->toCamel($propertyName);

        return ucfirst($propertyName);
    }

    /**
     * Converts a string into snake_case
     *
     * @taken https://github.com/symfony/string/blob/7.3/ByteString.php#snake
     */
    public function toSnake(string $propertyName): string
    {
        $propertyName = $this->toCamel($propertyName);

        return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], '\1_\2', $propertyName));
    }
}
