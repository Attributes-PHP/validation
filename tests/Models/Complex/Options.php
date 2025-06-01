<?php

namespace Attributes\Validation\Tests\Models\Complex;

use Attributes\Validation\Options\Alias;
use Attributes\Validation\Options\AliasGenerator;
use Respect\Validation\Rules as Rules;

#[AliasGenerator('camel')]
class Post
{
    #[Alias('postId')]
    public int|string $my_post_id;

    #[Rules\NotEmpty]
    public string $my_title;
}

class Profile
{
    public Post $my_post;
}

#[AliasGenerator('pascal')]
class User
{
    #[Alias('userProfile')]
    public Profile $my_profile;

    public string $full_name;
}
