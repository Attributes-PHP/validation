# Attributes Validation

<p align="center">
    <a href="https://codecov.io/gh/Attributes-PHP/validation"><img src="https://codecov.io/gh/Attributes-PHP/validation/graph/badge.svg?token=9W2JHIDQ2V"/></a>
    <a href="https://packagist.org/packages/Attributes-PHP/validation"><img alt="Latest Version" src="https://img.shields.io/packagist/v/Attributes-PHP/validation"></a>
    <a href="https://packagist.org/packages/Attributes-PHP/validation"><img alt="Software License" src="https://img.shields.io/packagist/l/Attributes-PHP/validation"></a>
</p>

**Attributes Validation** is an easy way to validate and convert raw array's into classes.

## Features

- Validates data via classes
- Type hint validation support

## Requirements

- PHP 8.1+
- [respect/validation](https://github.com/Respect/Validation)

We aim to support versions that haven't reached their end-of-life.

## How it works?

```php
<?php

use Attributes\Validation\Validator;

class Person
{
    public float|int $age;
    public ?DateTime $birthday;
}

$rawData = [
    'age' => '100',
    'birthday' => '2025-01-01 15:46:55',
];

$validator = new Validator();
$person = $validator->validate($rawData, new Person);

var_dump($person->age);      // int(100)
var_dump($person->birthday); // object(DateTime) { ["date"] => string(26) "2025-01-01 15:46:55.000000", (...) }
```

## Installation

```bash
composer require attributes/validation
```

FastEndpoints was created by **[André Gil](https://www.linkedin.com/in/andre-gil/)** and is open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
