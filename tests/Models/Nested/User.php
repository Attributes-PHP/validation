<?php

namespace Attributes\Validation\Tests\Models\Nested;

use DateTime;

class User
{
    public Profile $profile;

    public UserType $userType;

    public DateTime $createdAt;

    public function __construct(?DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt ?? new DateTime;
    }
}
