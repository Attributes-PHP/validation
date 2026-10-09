<?php

declare(strict_types=1);

/*
 * The Attributes\Validation\{bool,int,float,string,sequence,dict,object}
 * constants hold the matching Type case values, so attribute arguments can
 * use the bare names (#[Union(bool, int, string)]) and combine them with the
 * bitwise OR operator (#[Union(bool | int | string)]) through const imports.
 */

namespace Attributes\Validation;

if (! defined('Attributes\Validation\bool')) {
    define('Attributes\Validation\bool', Type::bool->value);
}

if (! defined('Attributes\Validation\int')) {
    define('Attributes\Validation\int', Type::int->value);
}

if (! defined('Attributes\Validation\float')) {
    define('Attributes\Validation\float', Type::float->value);
}

if (! defined('Attributes\Validation\string')) {
    define('Attributes\Validation\string', Type::string->value);
}

if (! defined('Attributes\Validation\sequence')) {
    define('Attributes\Validation\sequence', Type::sequence->value);
}

if (! defined('Attributes\Validation\dict')) {
    define('Attributes\Validation\dict', Type::dict->value);
}

if (! defined('Attributes\Validation\object')) {
    define('Attributes\Validation\object', Type::object->value);
}
