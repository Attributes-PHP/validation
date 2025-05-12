# Attributes Validation

<p align="center">
    <a href="https://codecov.io/gh/Attributes-PHP/validation"><img src="https://codecov.io/gh/Attributes-PHP/validation/graph/badge.svg?token=9W2JHIDQ2V"/></a>
    <a href="https://packagist.org/packages/Attributes-PHP/validation"><img alt="Latest Version" src="https://img.shields.io/packagist/v/Attributes-PHP/validation"></a>
    <a href="https://packagist.org/packages/Attributes-PHP/validation"><img alt="Software License" src="https://img.shields.io/packagist/l/Attributes-PHP/validation"></a>
</p>

**Attributes Validation** is the Pydantic validation library for PHP which allows you to validate data via type hints

## Features

- Validates data via type-hinting
- Converts raw dictionaries into classes
- Support for custom validation rules

## Requirements

- PHP 8.1+
- [respect/validation](https://github.com/Respect/Validation)

We aim to support versions that haven't reached their end-of-life.

## How it works?

```php
<?php

use Attributes\Validation\Validator;
use Respect\Validation\Rules as Rules;

class Person
{
    #[Rules\Min(0)]
    public float|int $age;
    public ?DateTime $birthday;
}

$rawData = [
    'age' => '30',
    'birthday' => '1994-01-01T09:00:00+00:00',
];

$validator = new Validator();
$person = $validator->validate($rawData, new Person);

var_dump($person->age);      // int(100)
var_dump($person->birthday); // object(DateTime) { ["date"] => string(26) "1994-01-01 09:00:00.000000", (...) }
```

## Installation

```bash
composer require attributes-php/validation
```

Attributes Validation was created by **[André Gil](https://www.linkedin.com/in/andre-gil/)** and is open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
