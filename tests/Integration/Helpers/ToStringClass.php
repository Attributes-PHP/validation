<?php

namespace Attributes\Validation\Tests\Integration\Helpers;

class ToStringClass
{
    public function __toString()
    {
        return 'My value';
    }
}
