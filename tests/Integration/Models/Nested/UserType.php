<?php

namespace Attributes\Validation\Tests\Integration\Models\Nested;

enum UserType: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case GUEST = 'guest';
}
