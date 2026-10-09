<?php

declare(strict_types=1);

namespace Attributes\Validation;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ModelConfigs
{
    private const ALIAS_GENERATORS = ['pascal', 'camel', 'snake', 'kebab'];

    private const EXTRAS = ['ignore', 'forbid', 'allow'];

    private ?string $aliasGenerator = null;

    private bool $strToLower = false;

    private bool $strToUpper = false;

    private bool $stripWhitespace = false;

    private string $extra = 'ignore';

    private bool $strict = true;

    private bool $stopAtFirstError = false;

    private static mixed $defaultErrorMessages = null;

    public function __construct(
        ?string $aliasGenerator = null,
        bool $strToLower = false,
        bool $strToUpper = false,
        bool $stripWhitespace = false,
        string $extra = 'ignore',
        bool $strict = true,
        bool $stopAtFirstError = false,
    ) {
        if ($aliasGenerator !== null) {
            $normalized = strtolower($aliasGenerator);
            if (! in_array($normalized, self::ALIAS_GENERATORS, true)) {
                throw new \ValueError(sprintf(
                    'Attributes\Validation\ModelConfigs::__construct(): Argument #1 must be of one of the following options: %s',
                    implode(', ', self::ALIAS_GENERATORS)
                ));
            }

            $this->aliasGenerator = $normalized;
        }

        if (! in_array(strtolower($extra), self::EXTRAS, true)) {
            throw new \ValueError(sprintf(
                'Attributes\Validation\ModelConfigs::__construct(): Argument #5 must be of one of the following options: %s',
                implode(', ', self::EXTRAS)
            ));
        }

        $this->strToLower = $strToLower;
        $this->strToUpper = $strToUpper;
        $this->stripWhitespace = $stripWhitespace;
        $this->extra = strtolower($extra);
        $this->strict = $strict;
        $this->stopAtFirstError = $stopAtFirstError;
    }

    public function getAliasGenerator(): ?string
    {
        return $this->aliasGenerator;
    }

    public function isStrToLower(): bool
    {
        return $this->strToLower;
    }

    public function isStrToUpper(): bool
    {
        return $this->strToUpper;
    }

    public function isStripWhitespace(): bool
    {
        return $this->stripWhitespace;
    }

    public function getExtra(): string
    {
        return $this->extra;
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }

    public function isStopAtFirstError(): bool
    {
        return $this->stopAtFirstError;
    }

    public static function setDefaultErrorMessages(mixed $setErrorMessages): void
    {
        if (! is_callable($setErrorMessages)) {
            throw new \TypeError('Argument 1 passed to Attributes\Validation\ModelConfigs::setDefaultErrorMessages() must be callable');
        }

        self::$defaultErrorMessages = $setErrorMessages;
    }
}
