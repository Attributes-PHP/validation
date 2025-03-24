<?php

namespace Attributes\Validation\Tests\Integration\Models\Nested;

use DateTime;

class Post
{
    public string $title;

    public ?DateTime $published = null;

    public function __construct(?DateTime $published = null)
    {
        $this->published = $published ?? new DateTime;
    }
}
