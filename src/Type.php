<?php

declare(strict_types=1);

namespace Attributes\Validation;

enum Type: int
{
    case bool = 12;

    case int = 16;

    case float = 32;

    case string = 64;

    case sequence = 128;

    case dict = 268435456;

    case object = 256;
}
