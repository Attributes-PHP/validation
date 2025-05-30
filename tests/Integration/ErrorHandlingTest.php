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
            ->and($e->getInfo()->getErrors())
            ->toBeArray()
            ->toBe([
                'bool' => ['"invalid" must be a boolean value'],
                'int' => ['"invalid" must be a finite number', '"invalid" must be numeric'],
                'float' => ['"invalid" must be a finite number', '"invalid" must be a float number'],
                'string' => ['`{ "invalid" }` must be a string'],
                'array' => ['"invalid" must be an array value'],
                'object' => ['"invalid" must be an array value'],
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
            ->and($e->getInfo()->getErrors())
            ->toBeArray()
            ->toBe([
                'profile' => [
                    'lastName' => ['`{ "profile.lastName" }` must be a string'],
                    'post' => [
                        'title' => ['`{ "profile.post.title" }` must be a string'],
                    ],
                ],
                'userType' => ['"userType" must be in `{ "admin", "moderator", "guest" }`'],
                'createdAt' => ['"createdAt" must be a valid date/time in the format "2005-12-30T01:02:03+00:00"'],
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
            ->and($e->getInfo()->getErrors())
            ->toBeArray()
            ->toBe([
                'bool' => ['"invalid" must be of type boolean'],
                'int' => ['"invalid" must be of type integer', '"invalid" must be a finite number'],
                'float' => ['"invalid" must be of type float', '"invalid" must be a finite number'],
                'string' => ['`{ "invalid" }` must be of type string'],
                'array' => ['"invalid" must be of type array'],
                'object' => ['"invalid" must be an array value'],
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
            ->and($e->getInfo()->getErrors())
            ->toBeArray()
            ->toBe([
                'profile' => [
                    'lastName' => ['`{ "profile.lastName" }` must be of type string'],
                    'post' => [
                        'title' => ['`{ "profile.post.title" }` must be of type string'],
                    ],
                ],
                'userType' => ['"userType" must be in `{ "admin", "moderator", "guest" }`'],
                'createdAt' => ['"createdAt" must be a valid date/time in the format "2005-12-30T01:02:03+00:00"'],
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
        $expectedErrors = $isStrict ? ['"invalid" must be of type boolean'] : ['"invalid" must be a boolean value'];
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getInfo()->getErrors())
            ->toBeArray()
            ->toBe(['bool' => $expectedErrors]);
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
        $expectedErrors = $isStrict ? ['`{ "profile.lastName" }` must be of type string'] : ['`{ "profile.lastName" }` must be a string'];
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getInfo()->getErrors())
            ->toBeArray()
            ->toBe([
                'profile' => [
                    'lastName' => $expectedErrors,
                ],
            ]);
    }
})
    ->with([true, false])
    ->group('validator', 'error-handling', 'nested', 'strict');
