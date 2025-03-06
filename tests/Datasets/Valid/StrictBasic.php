<?php

dataset('strict datetime', [
    '2025-03-06T08:57:06+00:00',
    '2050-12-06T00:00:03+00:00',
    new DateTime,
    new DateTime('2013-6-23'),
    new DateTime('2025-03-06T08:57:06+00:00'),
]);

dataset('strict object', [
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

dataset('strict array', [
    [[123]],
    [['a' => 1, 'b' => 2]],
    [[['a' => 1, 'b' => 2], [1, 2, 3]]],
]);

dataset('strict float', [
    123.5,
    -12.82,
    -0.05,
    -10e5,
]);

dataset('strict integer', [
    123,
    -12,
    2000,
]);

dataset('strict bool', [
    true,
    false,
]);

dataset('strict string', [
    'Hello world!',
    '/[a-z]+/g',
    'test@test.com',
    'https://www.myspanishnow.com/',
    '',
]);
