<?php

namespace Attributes\Validation\Tests\Models\Nested;

enum UserType: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case GUEST = 'guest';
}
