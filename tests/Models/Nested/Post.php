<?php

namespace Attributes\Validation\Tests\Models\Nested;

use DateTime;
use Respect\Validation\Rules as Rules;

class Post
{
    public int|string $id;

    #[Rules\NotEmpty]
    public string $title;

    #[Rules\When(new Rules\IntVal, new Rules\Min(0))]
    public int|DateTime $published;

    public function __construct(?DateTime $published = null)
    {
        $this->published = $published ?? new DateTime('2025-03-31T18:00:00+00:00');
    }
}
