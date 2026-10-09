# Attributes Validation

<p align="center">
    <a href="https://codecov.io/gh/Attributes-PHP/validation"><img src="https://codecov.io/gh/Attributes-PHP/validation/graph/badge.svg?token=9W2JHIDQ2V"/></a>
    <a href="https://packagist.org/packages/Attributes-PHP/validation"><img alt="Latest Version" src="https://img.shields.io/packagist/v/Attributes-PHP/validation"></a>
    <a href="https://packagist.org/packages/Attributes-PHP/validation"><img alt="Software License" src="https://img.shields.io/packagist/l/Attributes-PHP/validation"></a>
</p>

**Attributes Validation** is a Pydantic-inspired PHP library for validating data via type hints. Your model types are the
schema: no configuration files, no YAML.

```php
<?php

use function Attributes\Validation\validate;
use Attributes\Validation\BaseModel;

class User extends BaseModel
{
    public float|int $age;
    public ?DateTime $birthday;
}

$rawData = [
    'age' => 30,
    'birthday' => '1994-01-01T09:00:00+00:00',
];

$user = validate($rawData, new User());

var_dump($user->age);      // int(30)
var_dump($user->birthday); // object(DateTime) "1994-01-01 09:00:00.000000" (+00:00)
```

## Features

- Validates data via type-hinting and hydrates raw arrays into models
- Strict by default, with opt-in loose scalar coercion via `#[ModelConfigs(strict: false)]`
- Nested models hydrate recursively, with dot-notation error paths (`users.0.email`)
- Typed `array` properties declare their element shape with the `Sequence`, `Union`, `Intersection` and `Dict`
  attributes
- `Alias` attribute and `ModelConfigs` alias generators map input keys to properties
- Custom error messages per field with the `ErrorMessage` attribute
- `beforeValidation`/`afterValidation` hooks on the model
- `call()` invokes any callable with hydrated models and injected dependencies

## Requirements

- PHP 8.2 or later

## Installation

```bash
composer require attributes-php/validation
```

## Usage

```php
<?php

use function Attributes\Validation\call;
use function Attributes\Validation\validate;
use Attributes\Validation\BaseModel;
use Attributes\Validation\Fields\Sequence;
use Attributes\Validation\ModelConfigs;
use Attributes\Validation\Type;

#[ModelConfigs(strict: false)]
class Blog extends BaseModel
{
    public string $title;

    #[Sequence(Type::string)]
    public array $tags;

    public ?DateTime $publishedAt = null;
}

$blog = validate([
    'title' => 'Hello',
    'tags' => ['php', 'validation'],
    'publishedAt' => '2025-03-06T08:57:06+00:00',
], new Blog());

// Call any callable with validated models and injected dependencies
$result = call('registerBlog', $rawData, $dependencies);
```

Validation failures throw `Attributes\Validation\Exceptions\ValidationException` with the error list keyed by field path:

```php
use Attributes\Validation\Exceptions\ValidationException;

try {
    validate($rawData, new Blog());
} catch (ValidationException $e) {
    $e->getErrors();
    // [
    //     'title' => ['Field is required'],
    //     'tags.1' => ['Must be string'],
    // ]
}
```

Attributes Validation was created by **[André Gil](https://www.linkedin.com/in/andre-gil/)** and is open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
