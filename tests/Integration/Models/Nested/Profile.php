<?php

namespace Attributes\Validation\Tests\Integration\Models\Nested;

class Profile
{
    public string $firstName;

    public string $lastName;

    public Post $post;

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
