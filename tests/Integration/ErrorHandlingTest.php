<?php

/**
 * Holds integration tests for error handling
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Validation\Tests\Integration;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Tests\Models\Nested as Models;
use Attributes\Validation\Validator;

test('Error handling - basic', function () {
    $validator = new Validator;
    $rawData = [
        'bool' => 'invalid',
        'int' => 'invalid',
        'float' => 'invalid',
        'string' => ['invalid'],
        'array' => 'invalid',
        'object' => 'invalid',
    ];
    try {
        $validator->validate($rawData, new class
        {
            public bool $bool;

            public int $int;

            public float $float;

            public string $string;

            public array $array;

            public object $object;
        });
    } catch (ValidationException $e) {
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getErrors())
            ->toBeArray()
            ->toBe([
                ['field' => 'bool', 'reason' => '"invalid" must be a boolean value'],
                ['field' => 'int', 'reason' => '"invalid" must be a finite number'],
                ['field' => 'int', 'reason' => '"invalid" must be numeric'],
                ['field' => 'float', 'reason' => '"invalid" must be a finite number'],
                ['field' => 'float', 'reason' => '"invalid" must be a float number'],
                ['field' => 'string', 'reason' => '`{ "invalid" }` must be a string'],
                ['field' => 'array', 'reason' => '"invalid" must be an array value'],
                ['field' => 'object', 'reason' => '"invalid" must be an array value'],
            ]);
    }
})
    ->group('validator', 'error-handling', 'basic');

test('Error handling - nested', function () {
    $validator = new Validator;
    $rawData = [
        'profile' => [
            'firstName' => 'profile.firstName',
            'lastName' => ['profile.lastName'],
            'post' => [
                'id' => 'profile.post.id',
                'title' => ['profile.post.title'],
            ],
        ],
        'userType' => 'userType',
        'createdAt' => 'createdAt',
    ];
    try {
        $validator->validate($rawData, new Models\User);
    } catch (ValidationException $e) {
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getErrors())
            ->toBeArray()
            ->toBe([
                ['field' => 'profile.lastName', 'reason' => '`{ "profile.lastName" }` must be a string'],
                ['field' => 'profile.post.title', 'reason' => '`{ "profile.post.title" }` must be a string'],
                ['field' => 'userType', 'reason' => '"userType" must be in `{ "admin", "moderator", "guest" }`'],
                ['field' => 'createdAt', 'reason' => '"createdAt" must be a valid date/time in the format "2005-12-30T01:02:03+00:00"'],
            ]);
    }
})
    ->group('validator', 'error-handling', 'nested');

// Strict

test('Error handling - strict basic', function () {
    $validator = new Validator(strict: true);
    $rawData = [
        'bool' => 'invalid',
        'int' => 'invalid',
        'float' => 'invalid',
        'string' => ['invalid'],
        'array' => 'invalid',
        'object' => 'invalid',
    ];
    try {
        $validator->validate($rawData, new class
        {
            public bool $bool;

            public int $int;

            public float $float;

            public string $string;

            public array $array;

            public object $object;
        });
    } catch (ValidationException $e) {
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getErrors())
            ->toBeArray()
            ->toBe([
                ['field' => 'bool', 'reason' => '"invalid" must be of type boolean'],
                ['field' => 'int', 'reason' => '"invalid" must be of type integer'],
                ['field' => 'int', 'reason' => '"invalid" must be a finite number'],
                ['field' => 'float', 'reason' => '"invalid" must be of type float'],
                ['field' => 'float', 'reason' => '"invalid" must be a finite number'],
                ['field' => 'string', 'reason' => '`{ "invalid" }` must be of type string'],
                ['field' => 'array', 'reason' => '"invalid" must be of type array'],
                ['field' => 'object', 'reason' => '"invalid" must be an array value'],
            ]);
    }
})
    ->group('validator', 'error-handling', 'basic', 'strict');

test('Error handling - strict nested', function () {
    $validator = new Validator(strict: true);
    $rawData = [
        'profile' => [
            'firstName' => 'profile.firstName',
            'lastName' => ['profile.lastName'],
            'post' => [
                'id' => 'profile.post.id',
                'title' => ['profile.post.title'],
            ],
        ],
        'userType' => 'userType',
        'createdAt' => 'createdAt',
    ];
    try {
        $validator->validate($rawData, new Models\User);
    } catch (ValidationException $e) {
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getErrors())
            ->toBeArray()
            ->toBe([
                ['field' => 'profile.lastName', 'reason' => '`{ "profile.lastName" }` must be of type string'],
                ['field' => 'profile.post.title', 'reason' => '`{ "profile.post.title" }` must be of type string'],
                ['field' => 'userType', 'reason' => '"userType" must be in `{ "admin", "moderator", "guest" }`'],
                ['field' => 'createdAt', 'reason' => '"createdAt" must be a valid date/time in the format "2005-12-30T01:02:03+00:00"'],
            ]);
    }
})
    ->group('validator', 'error-handling', 'nested', 'strict');

// Stop first error

test('Error handling - stop first error basic', function (bool $isStrict) {
    $validator = new Validator(stopFirstError: true, strict: $isStrict);
    $rawData = [
        'bool' => 'invalid',
        'int' => 'invalid',
        'float' => 'invalid',
        'string' => ['invalid'],
        'array' => 'invalid',
        'object' => 'invalid',
    ];
    try {
        $validator->validate($rawData, new class
        {
            public bool $bool;

            public int $int;

            public float $float;

            public string $string;

            public array $array;

            public object $object;
        });
    } catch (ValidationException $e) {
        $expectedError = $isStrict ? '"invalid" must be of type boolean' : '"invalid" must be a boolean value';
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getErrors())
            ->toBeArray()
            ->toBe([['field' => 'bool', 'reason' => $expectedError]]);
    }
})
    ->with([true, false])
    ->group('validator', 'error-handling', 'basic');

test('Error handling - stop first error nested', function (bool $isStrict) {
    $validator = new Validator(stopFirstError: true, strict: $isStrict);
    $rawData = [
        'profile' => [
            'firstName' => 'profile.firstName',
            'lastName' => ['profile.lastName'],
            'post' => [
                'id' => 'profile.post.id',
                'title' => ['profile.post.title'],
            ],
        ],
        'userType' => 'userType',
        'createdAt' => 'createdAt',
    ];
    try {
        $validator->validate($rawData, new Models\User);
    } catch (ValidationException $e) {
        $expectedError = $isStrict ? '`{ "profile.lastName" }` must be of type string' : '`{ "profile.lastName" }` must be a string';
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getErrors())
            ->toBeArray()
            ->toBe([[
                'field' => 'profile.lastName',
                'reason' => $expectedError,
            ]]);
    }
})
    ->with([true, false])
    ->group('validator', 'error-handling', 'nested');
