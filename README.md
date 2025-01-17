# Attributes Validation

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

use Attributes\Validation\BaseModel;
use Attributes\Validation\Validator;

class Person extends BaseModel
{
    private float|int $age = 100;
    private DateTime $birthday;

    public function getAge() {
        return $this->age;
    }

    public function getBirthday() {
        return $this->birthday;
    }
}

$rawData = [
    'age' => '100',
    'birthday' => '2025-01-01 15:46:55',
];

$validator = new Validator();
$person = $validator->validate($rawData, new Person);

echo "Age: {$person->getAge()}\n";                                   // Age: 100
echo "Birthday: {$person->getBirthday()->format('Y-m-d H-i-s')}\n";  // Birthday: 2025-01-01 15-46-55
```

## Installation

```bash
composer require attributes/validation
```

FastEndpoints was created by **[André Gil](https://www.linkedin.com/in/andre-gil/)** and is open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
