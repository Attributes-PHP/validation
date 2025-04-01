<?php

use Attributes\Validation\Tests\Integration\Models\Basic as Models;

dataset('invalid datetime', [
    '2013-6-23',
    '6/23/2013',
    '23.06. 2013',
    '2013/6/23',
    '2013-06-23',
    '20130623T13:22-0500',
    '2011-10-05T14:48:00.000Z',
    '2025-99-99 15:46:55',
    '2025-999-01',
    '2013-6-999',
    '6/23/-2000',
    'Invalid date',
    '2050-12-06T00:00:99+00:00',
    [[123]],
    (object) [[2345]],
    123,
    -124,
    18.2,
    true,
    false,
    null,
]);

dataset('invalid object', [
    'This is a string',
    12345,
    -123,
    92.21,
    true,
    false,
    null,
]);

dataset('invalid array', [
    (object) [[1, 2, 3]],
    new class {},
    123,
    'hello world',
    92.12,
    -19,
    true,
    false,
    null,
]);

dataset('invalid float', [
    'hello world',
    [[5, 2, 1]],
    new class {},
    (object) [[1, 2, 3], [1, 2, 3]],
    new DateTime,
    true,
    false,
    null,
]);

dataset('invalid integer', [
    'hello world',
    [[5, 2, 1]],
    new class {},
    (object) [[1, 2, 3], [1, 2, 3]],
    new DateTime,
    true,
    false,
    null,
    3245e9898989898989898989,
    '3245e9898989898989898989',
]);

dataset('invalid bool', [
    'hello',
    12345,
    -982,
    -10e10,
    10e10,
    [[1, 2, 3]],
    new class {},
    (object) [[1, 2, 3], [1, 2, 3]],
    new DateTime,
    '6/23/2013',
    null,
]);

dataset('invalid string', [
    [[1, 2, 3]],
    (object) [[3, 3]],
    new DateTime,
    new class {},
    null,
]);

dataset('invalid enum', [
    'hello',
    'admin',
    'guest',
    'GuEsT',
    'GUESt',
    1,
    2.4,
    false,
    true,
    (object) [[1, 2, 3], [1, 2, 3], [1, 2, 3]],
    new DateTime,
    '6/23/2013',
    [1, 2, 3],
    new class {},
    Models\RawStrEnum::ADMIN,
]);

dataset('invalid string enum', [
    'hello',
    'GUEST',
    'guesT',
    'guest ',
    ' guest ',
    'ADMIN',
    1,
    2.4,
    false,
    true,
    (object) [[1, 2, 3], [1, 2, 3], [1, 2, 3]],
    new DateTime,
    '6/23/2013',
    [1, 2, 3],
    new class {},
    Models\RawEnum::ADMIN,
]);

dataset('invalid int enum', [
    0.0,
    1.0,
    '1',
    '0',
    'hello',
    'admin',
    'guest',
    'GuEsT',
    'GUESt',
    1.87,
    2.4,
    false,
    true,
    (object) [[1, 2, 3], [1, 2, 3], [1, 2, 3]],
    new DateTime,
    '6/23/2013',
    [[1, 2, 3]],
    new class {},
    Models\RawEnum::ADMIN,
    -294,
    198,
]);
