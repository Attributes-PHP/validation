<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

use Attributes\Validation\ModelConfigs;

/**
 * The ModelConfigs of a model class, resolved from its class attribute.
 *
 * @internal
 */
final class ResolvedConfigs
{
    private static array $cache = [];

    private function __construct(
        public readonly ?string $aliasGenerator,
        public readonly bool $strToLower,
        public readonly bool $strToUpper,
        public readonly bool $stripWhitespace,
        public readonly string $extra,
        public readonly bool $strict,
        public readonly bool $stopAtFirstError,
    ) {}

    /**
     * Resolves the ModelConfigs of a model class, walking up the
     * inheritance chain for the attribute, parsed once and cached per
     * class.
     */
    public static function resolve(string $class): self
    {
        if (isset(self::$cache[$class])) {
            return self::$cache[$class];
        }

        $generator = null;
        $strToLower = false;
        $strToUpper = false;
        $stripWhitespace = false;
        $extra = 'ignore';
        $strict = true;
        $stopAtFirstError = false;

        $attribute = null;

        for ($current = $class; $current !== false; $current = get_parent_class($current)) {
            $attributes = (new \ReflectionClass($current))->getAttributes(ModelConfigs::class);

            if ($attributes !== []) {
                $attribute = $attributes[0];

                break;
            }
        }

        if ($attribute !== null) {
            foreach ($attribute->getArguments() as $name => $value) {
                $index = is_int($name) ? $name : self::argumentIndex($name);

                switch ($index) {
                    case 0: // aliasGenerator
                        if (is_string($value)) {
                            if (! in_array(strtolower($value), ['pascal', 'camel', 'snake', 'kebab'], true)) {
                                throw new \ValueError('Attributes\Validation\ModelConfigs::__construct(): Argument #1 must be of one of the following options: pascal, camel, snake, kebab');
                            }

                            $generator = strtolower($value);
                        }

                        break;
                    case 1: // strToLower
                        $strToLower = $value === true;

                        break;
                    case 2: // strToUpper
                        $strToUpper = $value === true;

                        break;
                    case 3: // stripWhitespace
                        $stripWhitespace = $value === true;

                        break;
                    case 4: // extra
                        if (is_string($value)) {
                            if (! in_array(strtolower($value), ['ignore', 'forbid', 'allow'], true)) {
                                throw new \ValueError('Attributes\Validation\ModelConfigs::__construct(): Argument #5 must be of one of the following options: ignore, forbid, allow');
                            }

                            $extra = strtolower($value);
                        }

                        break;
                    case 5: // strict
                        $strict = $value === true;

                        break;
                    case 6: // stopAtFirstError
                        $stopAtFirstError = $value === true;

                        break;
                    default:
                        throw new \Error("Unknown named parameter \${$name}");
                }
            }
        }

        return self::$cache[$class] = new self(
            $generator,
            $strToLower,
            $strToUpper,
            $stripWhitespace,
            $extra,
            $strict,
            $stopAtFirstError,
        );
    }

    /**
     * Materializes the configs as a ModelConfigs instance (hook argument).
     */
    public function toModelConfigs(): ModelConfigs
    {
        return new ModelConfigs(
            $this->aliasGenerator,
            $this->strToLower,
            $this->strToUpper,
            $this->stripWhitespace,
            $this->extra,
            $this->strict,
            $this->stopAtFirstError,
        );
    }

    private static function argumentIndex(string $name): int
    {
        return match ($name) {
            'aliasGenerator' => 0,
            'strToLower' => 1,
            'strToUpper' => 2,
            'stripWhitespace' => 3,
            'extra' => 4,
            'strict' => 5,
            'stopAtFirstError' => 6,
            default => throw new \Error("Unknown named parameter \${$name}"),
        };
    }
}
