<?php

namespace Attributes\Validation\Tests\Models\Nested;

use Respect\Validation\Rules as Rules;

class Profile
{
    #[Rules\Uuid]
    public string $id;

    public string $firstName;

    public string $lastName;

    public Post $post;

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function __construct()
    {
        $this->id = uniqid();
    }
}
