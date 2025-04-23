<?php

/**
 * Holds integration tests for additional respect rules via attributes
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Validation\Tests\Integration;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Validator;
use Respect\Validation\Rules as Rules;
use stdClass;

test('Valid uuid', function (string $uuid) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $uuid], new class
    {
        #[Rules\Uuid]
        public string $value;
    });
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $uuid);
})
    ->with(['7dd1eab6-e8ed-40c9-910c-57dc725066d5', '569f5e32-1f4f-11f0-9cd2-0242ac120002'])
    ->group('validator', 'additional rules');

test('Invalid uuid', function ($uuid) {
    $validator = new Validator;
    $validator->validate(['value' => $uuid], new class
    {
        #[Rules\Uuid]
        public string $value;
    });
})
    ->throws(ValidationException::class, 'Invalid data')
    ->with(['7dd1eab6-e8ed', '', null, 'Hello world', '112', 123, 15.23, [['hello']], new stdClass])
    ->group('validator', 'additional rules');

test('Valid array of numbers', function (array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        #[Rules\NotEmpty]
        #[Rules\Each(new Rules\Number)]
        public array $value;
    });
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $value);
})
    ->with([[[1, -15, 1.569]], [['1', '1.89', '-13', 18, -5.1]]])
    ->group('validator', 'additional rules');

test('Invalid array of numbers', function (array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new class
    {
        #[Rules\NotEmpty]
        #[Rules\Each(new Rules\Number)]
        public array $value;
    });
    expect($model)
        ->toBeObject()
        ->toHaveProperty('value', $value);
})
    ->throws(ValidationException::class, 'Invalid data')
    ->with([[[1, 'hello world', 1.569]], [[['another array'], '1.89', '-13', 18, -5.1]], [[]]])
    ->group('validator', 'additional rules');
