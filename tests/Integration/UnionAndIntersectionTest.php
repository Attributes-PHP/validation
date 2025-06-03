<?php

/**
 * Holds integration tests for loose validation of basic type-hints
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Validation\Tests\Integration;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Tests\Models\Complex as Models;
use Attributes\Validation\Types\IntArr;
use Attributes\Validation\Types\StrArr;
use Attributes\Validation\Validator;
use DateTime;
use stdClass;

/*** Union ***/

// Basics

test('Union with float/int', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public float|int $value;
    });
    $expectedValue = $value + 0; // Cast value to proper type
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $expectedValue)
        ->and($model->value)
        ->toBe($expectedValue);
})
    ->with([1, -10, 10e10, 1.1, '100', '10.28', '-98e2'])
    ->group('validator', 'union');

test('Invalid union with float/int', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new class
    {
        public float|int $value;
    });
})
    ->throws(ValidationException::class, 'Invalid data')
    ->with('invalid float')
    ->group('validator', 'union');

test('Union with float/int/string', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public float|int|string $value;
    });
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $value)
        ->and($model->value)
        ->toBe($value);
})
    ->with([1, -10, 10e10, 1.1, '100', '10.28', '-98e2', 'hello world'])
    ->group('validator', 'union');

test('Union with float/int/string/array', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public float|int|string|array $value;
    });
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $value)
        ->and($model->value)->toBe($value);
})
    ->with([1, -10, 10e10, 1.1, '100', '10.28', '-98e2', 'hello world', [['hello world', 123]]])
    ->group('validator', 'union');

test('Union with float/int/string/object', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public float|int|string|object $value;
    });
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value');
    if (is_array($value)) {
        expect($model->value)
            ->toBeObject()
            ->toMatchArray($value);

        return;
    }
    expect($model)
        ->toHaveProperty('value', $value)
        ->and($model->value)->toBe($value);
})
    ->with([1, -10, 10e10, 1.1, '100', '10.28', '-98e2', 'hello world', [['name' => 'hello world', 'num' => 123]]])
    ->group('validator', 'union');

test('Union with int/DateTime', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public int|DateTime $value;
    });
    $expectedValue = is_numeric($value) ? (int) $value : $value;
    $expectedValue = $expectedValue instanceof Datetime || is_int($expectedValue) ? $expectedValue : new DateTime($expectedValue);
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $expectedValue);
})
    ->with([1, -10, 10e10, 1.1, '100', '10.28', '-98e2', '2050-12-06T00:00:03+00:00', new DateTime])
    ->group('validator', 'union');

test('Union with int/IntArr', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public int|IntArr $value;
    });
    expect($model)
        ->toBeObject();

    if (is_numeric($value)) {
        expect($model->value)->toBe((int) $value);

        return;
    }

    expect($model->value)
        ->toHaveCount(count($value))
        ->each
        ->toBeInt();
})
    ->with([1, -10, 10e10, 1.1, '100', '10.28', '-98e2', [[1, 2, 3]], [[0.22, '90', 19]]])
    ->group('validator', 'union');

test('Union with IntArr/StrArr', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public IntArr|StrArr $value;
    });
    expect($model)
        ->toBeObject()
        ->and($model->value)
        ->toMatchArray($value);
})
    ->with([[[1, 2, 3]], [['hello', 'bro', 'another']]])
    ->group('validator', 'union', 'hey');

/*** Intersection ***/

test('Intersection with Logger&Formatter', function () {
    $validator = new Validator;
    $loggerFormatter = new Models\LoggerFormatter;
    $model = $validator->validate(['value' => $loggerFormatter], new class
    {
        public Models\Logger&Models\Formatter $value;
    });
    expect($model)
        ->toBeObject()
        ->and($model->value)
        ->toBe($loggerFormatter);
})
    ->group('validator', 'intersection');

test('Invalid intersection', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new class
    {
        public Models\Logger&Models\Formatter $value;
    });
})
    ->throws(ValidationException::class, 'Invalid data')
    ->with([10, 1.4, true, false, null, [[123]], new stdClass, new DateTime, new Models\OnlyLogger, new Models\OnlyFormatter])
    ->group('validator', 'intersection');
