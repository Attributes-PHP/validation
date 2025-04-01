<?php

namespace Attributes\Validation\Tests\Integration\Models\Nested;

use DateTime;

class Post
{
    public int|string $id;

    public string $title;

    public int|DateTime $published;

    public function __construct(?DateTime $published = null)
    {
        $this->published = $published ?? new DateTime('2025-03-31T18:00:00+00:00');
    }
}
