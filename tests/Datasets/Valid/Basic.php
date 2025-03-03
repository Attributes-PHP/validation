<?php

dataset('datetime', [
    '2025-01-01 15:46:55',
    '2025-01-01',
    '2013-6-23',
    '6/23/2013',
    '23.06. 2013',
    '2013/6/23',
    '2013-06-23',
    '20130623T13:22-0500',
    '2011-10-05T14:48:00.000Z',
    new DateTime,
    new DateTime('2013-6-23'),
    new DateTime('6/23/2013'),
    new DateTime('23.06. 2013'),
    new DateTime('2013/6/23'),
    new DateTime('2013-06-23'),
    new DateTime('20130623T13:22-0500'),
    new DateTime('2011-10-05T14:48:00.000Z'),
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
