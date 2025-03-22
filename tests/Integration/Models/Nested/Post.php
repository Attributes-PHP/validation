<?php

namespace Attributes\Validation\Tests\Integration\Models\Nested;

use DateTime;

class Post
{
    public string $title;

    public DateTime $published;

    public function __construct(?DateTime $published = null)
    {
        $this->published = $published ?? new DateTime('2025-03-31T18:00:00+00:00');
    }
}
