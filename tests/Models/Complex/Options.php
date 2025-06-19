<?php

namespace Attributes\Validation\Tests\Models\Complex;

use Attributes\Options\Alias;
use Attributes\Options\AliasGenerator;
use Attributes\Options\Ignore;
use Respect\Validation\Rules as Rules;

#[AliasGenerator('camel')]
class Post
{
    #[Alias('postId')]
    public int|string $my_post_id;

    #[Rules\NotEmpty]
    public string $my_title;

    #[Ignore]
    public string $privateData;
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
