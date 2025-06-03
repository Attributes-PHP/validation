<?php

namespace Attributes\Validation\Tests\Models\Complex;

use Attributes\Validation\Tests\Models\Nested\Post;
use Attributes\Validation\Types as Types;

class PostsArr extends Types\ArrayOf
{
    private Post $type;
}
