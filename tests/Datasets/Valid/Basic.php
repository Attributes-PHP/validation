<?php

use Attributes\Validation\Tests\Integration\Models\Basic as Models;

dataset('datetime', [
    '2025-03-06T08:57:06+00:00',
    '2050-12-06T00:00:03+00:00',
    new DateTime,
    new DateTime('2013-6-23'),
    new DateTime('2025-03-06T08:57:06+00:00'),
]);

dataset('object', [
    [[123]],
    [['a' => 1, 'b' => 2]],
    [[['a' => 1, 'b' => 2], [1, 2, 3]]],
    (object) [123],
    (object) ['a' => 1, 'b' => 2],
    (object) [['a' => 1, 'b' => 2], [1, 2, 3]],
    new class
    {
        public string $name;

        public string $age;
    },
]);

dataset('array', [
    [[123]],
    [['a' => 1, 'b' => 2]],
    [[['a' => 1, 'b' => 2], [1, 2, 3]]],
]);

dataset('float', [
    '123',
    123,
    123.5,
    -12,
    -0.05,
    -10e5,
    2e3,
    '2e3',
    '-4e4',
    '000',
]);

dataset('integer', [
    '123',
    123,
    123.5,
    -12,
    -0.05,
    -10e5,
    2e3,
    '2e3',
    '-4e4',
    '000',
]);

dataset('bool', [
    'true',
    'false',
    'True',
    'False',
    'TrUe',
    'FaLsE',
    'TRUE',
    'FALSE',
    true,
    false,
    0,
    1,
]);

dataset('string', [
    'Hello world!',
    '/[a-z]+/g',
    'test@test.com',
    'https://www.myspanishnow.com/',
    123,
    123.5,
    -12,
    -0.05,
    -10e5,
    2e3,
    '',
]);

dataset('enum', [
    'GUEST',
    'ADMIN',
    Models\RawEnum::GUEST,
    Models\RawEnum::ADMIN,
]);

dataset('string enum', [
    'guest',
    'admin',
    Models\RawStrEnum::GUEST,
    Models\RawStrEnum::ADMIN,
]);

dataset('int enum', [
    0,
    1,
    Models\RawIntEnum::GUEST,
    Models\RawIntEnum::ADMIN,
]);
