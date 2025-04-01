<?php

dataset('strict invalid datetime', [
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

dataset('strict invalid object', [
    'This is a string',
    12345,
    -123,
    92.21,
    true,
    false,
    null,
]);

dataset('strict invalid array', [
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

dataset('strict invalid float', [
    'hello world',
    [[5, 2, 1]],
    new class {},
    (object) [[1, 2, 3], [1, 2, 3]],
    new DateTime,
    true,
    false,
    null,
    '123',
    '24.98',
    123,
    -12,
    '2e3',
    '-4e4',
    '000',
    3245e9898989898989898989,
    '3245e9898989898989898989',
]);

dataset('strict invalid integer', [
    'hello world',
    [[5, 2, 1]],
    new class {},
    (object) [[1, 2, 3], [1, 2, 3]],
    new DateTime,
    true,
    false,
    null,
    '123',
    123.5,
    -0.05,
    '2e3',
    '-4e4',
    '000',
    3245e9898989898989898989,
    '3245e9898989898989898989',
]);

dataset('strict invalid bool', [
    'true',
    'false',
    'True',
    'False',
    'TrUe',
    'FaLsE',
    'TRUE',
    'FALSE',
    0,
    1,
    'hello',
    12345,
    -982,
    [[1, 2, 3]],
    new class {},
    (object) [[1, 2, 3], [1, 2, 3]],
    new DateTime,
    '6/23/2013',
    null,
]);

dataset('strict invalid string', [
    1,
    2.94,
    -30,
    -3.3,
    false,
    true,
    [[1, 2, 3]],
    (object) [[3, 3]],
    new DateTime,
    new class {},
    null,
]);
