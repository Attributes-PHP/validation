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
use Attributes\Validation\Validator;
use DateTime;

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
    $expectedValue = is_numeric($value) ? $value + 0 : $value;
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $expectedValue)
        ->and($model->value)
        ->toBe($expectedValue);
})
    ->with([1, -10, 10e10, 1.1, '100', '10.28', '-98e2', 'hello world'])
    ->group('validator', 'union');

test('Union with float/int/string/array', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public float|int|string|array $value;
    });
    $expectedValue = is_numeric($value) ? $value + 0 : $value;
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $expectedValue)
        ->and($model->value)->toBe($expectedValue);
})
    ->with([1, -10, 10e10, 1.1, '100', '10.28', '-98e2', 'hello world', [['hello world', 123]]])
    ->group('validator', 'union');

test('Union with float/int/string/object', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        public float|int|string|object $value;
    });
    $expectedValue = is_numeric($value) ? $value + 0 : $value;
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value');
    if (is_array($expectedValue)) {
        expect($model->value)
            ->toBeObject()
            ->toMatchArray($expectedValue);

        return;
    }
    expect($model)
        ->toHaveProperty('value', $expectedValue)
        ->and($model->value)->toBe($expectedValue);
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
