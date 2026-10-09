<?php

declare(strict_types=1);

namespace Attributes\Validation\Internal;

/**
 * Property name case transformations used by the ModelConfigs alias
 * generators.
 *
 * @internal
 */
final class CaseConvert
{
    public static function pascal(string $name): string
    {
        $result = '';
        $nextUpper = true;

        foreach (str_split($name) as $char) {
            if ($char === '_' || $char === '-' || $char === ' ') {
                $nextUpper = true;

                continue;
            }

            $result .= $nextUpper ? strtoupper($char) : $char;
            $nextUpper = false;
        }

        return $result;
    }

    public static function camel(string $name): string
    {
        $result = '';
        $nextUpper = false;
        $first = true;

        foreach (str_split($name) as $char) {
            if ($char === '_' || $char === '-' || $char === ' ') {
                $nextUpper = true;

                continue;
            }

            if ($first) {
                $result .= strtolower($char);
                $first = false;
                $nextUpper = false;

                continue;
            }

            $result .= $nextUpper ? strtoupper($char) : $char;
            $nextUpper = false;
        }

        return $result;
    }

    public static function snake(string $name): string
    {
        $result = '';

        foreach (str_split($name) as $char) {
            if ($char === '-' || $char === ' ') {
                $result .= '_';

                continue;
            }

            if (ctype_upper($char)) {
                if ($result !== '' && ! str_ends_with($result, '_')) {
                    $result .= '_';
                }

                $result .= strtolower($char);

                continue;
            }

            $result .= $char;
        }

        return $result;
    }

    public static function kebab(string $name): string
    {
        $result = '';

        foreach (str_split($name) as $char) {
            if ($char === '_' || $char === ' ') {
                $result .= '-';

                continue;
            }

            if (ctype_upper($char)) {
                if ($result !== '' && ! str_ends_with($result, '-')) {
                    $result .= '-';
                }

                $result .= strtolower($char);

                continue;
            }

            $result .= $char;
        }

        return $result;
    }
}
