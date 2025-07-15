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

use Attributes\Options;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Tests\Models as Models;
use Attributes\Validation\Validator;

// Alias and alias generator

test('Alias/AliasGenerator options - validate callable', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $rawData = [
        'FullName' => 'Full Name',
        'userProfile' => [
            'my_post' => [
                'postId' => 1,
                'myTitle' => 'My Post Title',
            ],
        ],
    ];
    $call = #[Options\AliasGenerator('camel')] function (#[Options\Alias('FullName')] string $full_name, Models\Complex\Profile $user_profile, int $default = 1) {
        return 'success';
    };
    $args = $validator->validateCallable($rawData, $call);
    expect($args)
        ->toBeArray()
        ->toHaveCount(2)
        ->and($args['full_name'])
        ->toBe('Full Name')
        ->and($args['user_profile'])
        ->toBeInstanceOf(Models\Complex\Profile::class)
        ->and($args['user_profile']->my_post->my_post_id)
        ->toBe(1)
        ->and($args['user_profile']->my_post->my_title)
        ->toBe('My Post Title')
        ->and(call_user_func_array($call, $args))
        ->toBe('success');
})
    ->with([true, false])
    ->group('validator', 'validate-callable', 'options', 'alias', 'alias-generator');

// Ignore

test('Ignore - validate callable', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $rawData = [
        'fullName' => 'My full name',
        'profile' => [
            'my_post' => [
                'postId' => 1,
                'myTitle' => 'My Post Title',
            ],
        ],
        'default' => 5,
    ];
    $call = function (string $fullName, #[Options\Ignore] Models\Complex\Profile $profile, int $default = 1) {
        return 'success';
    };
    $args = $validator->validateCallable($rawData, $call);
    expect($args)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray([
            'fullName' => 'My full name',
            'default' => 5,
        ]);
})
    ->with([true, false])
    ->group('validator', 'validate-callable', 'options', 'ignore');

// Error handling

test('Error handling - validate callable', function (array $rawData, array $expectedErrorMessages) {
    $validator = new Validator;
    $call = function (string $fullName, Models\Complex\Profile $profile, int $default = 1) {
        return 'success';
    };
    try {
        $validator->validateCallable($rawData, $call);
    } catch (ValidationException $e) {
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getErrors())
            ->toBeArray()
            ->toBe($expectedErrorMessages);
    }
})
    ->with([
        [['fullName' => 'My full name'], [['field' => 'profile', 'reason' => 'Missing required argument \'profile\'']]],
        [['default' => 'invalid'], [
            ['field' => 'fullName', 'reason' => 'Missing required argument \'fullName\''],
            ['field' => 'profile', 'reason' => 'Missing required argument \'profile\''],
            ['field' => 'default', 'reason' => '"invalid" must be a finite number'],
            ['field' => 'default', 'reason' => '"invalid" must be numeric'],
        ]],
        [['profile' => ['my_post' => ['myTitle' => '']]], [
            ['field' => 'fullName', 'reason' => 'Missing required argument \'fullName\''],
            ['field' => 'profile.my_post.my_post_id', 'reason' => 'Missing required property \'postId\''],
            ['field' => 'profile.my_post.my_title', 'reason' => 'The value must not be empty'],
        ]],
    ])
    ->group('validator', 'validate-callable', 'error-handling');

test('Error handling - stop first error - validate callable', function (array $rawData, array $expectedErrorMessages) {
    $validator = new Validator(strict: true, stopFirstError: true);
    $call = function (string $fullName, Models\Complex\Profile $profile, int $default = 1) {
        return 'success';
    };
    try {
        $validator->validateCallable($rawData, $call);
    } catch (ValidationException $e) {
        expect($e->getMessage())
            ->toBe('Invalid data')
            ->and($e->getErrors())
            ->toBeArray()
            ->toBe($expectedErrorMessages);
    }
})
    ->with([
        [['fullName' => 'My full name'], [['field' => 'profile', 'reason' => 'Missing required argument \'profile\'']]],
        [['default' => 'invalid'], [
            ['field' => 'fullName', 'reason' => 'Missing required argument \'fullName\''],
        ]],
        [['fullName' => 'My full name', 'profile' => ['my_post' => ['myTitle' => '']]], [
            ['field' => 'profile.my_post.my_post_id', 'reason' => 'Missing required property \'postId\''],
        ]],
    ])
    ->group('validator', 'validate-callable', 'error-handling');

// Numbered arguments

test('Numbered arguments - validate callable', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $rawData = [
        'fullName' => 'My full name',
        1 => [
            'my_post' => [
                'postId' => 1,
                'myTitle' => 'My Post Title',
            ],
        ],
        'profile' => [],  // Numbered arguments should have precedence
        'default' => 5,
    ];
    $call = function (string $fullName, Models\Complex\Profile $profile, int $default = 1) {
        return 'success';
    };
    $args = $validator->validateCallable($rawData, $call);
    expect($args)
        ->toBeArray()
        ->toHaveCount(3)
        ->toMatchArray([
            'fullName' => 'My full name',
            'default' => 5,
        ])
        ->and($args['profile'])
        ->toBeInstanceOf(Models\Complex\Profile::class)
        ->and($args['profile']->my_post)
        ->toBeInstanceOf(Models\Complex\Post::class)
        ->and($args['profile']->my_post->my_post_id)
        ->toBe(1)
        ->and($args['profile']->my_post->my_title)
        ->toBe('My Post Title');
})
    ->with([true, false])
    ->group('validator', 'validate-callable', 'numbered-arguments');
