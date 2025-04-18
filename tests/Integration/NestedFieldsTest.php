<?php

/**
 * Holds integration tests for validating nested fields
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Validation\Tests\Integration;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Tests\Integration\Models\Nested as Models;
use Attributes\Validation\Validator;
use DateTime;

test('Valid nested', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $data = [
        'profile' => [
            'firstName' => 'Andre',
            'lastName' => 'Gil',
            'post' => [
                'id' => 1,
                'title' => 'How to validate data with classes in PHP',
            ],
        ],
        'userType' => 'moderator',
        'createdAt' => '2025-03-28T16:00:00+00:00',
    ];
    $model = $validator->validate($data, new Models\User);
    expect($model)
        ->toBeInstanceOf(Models\User::class)
        ->toHaveProperty('userType', Models\UserType::MODERATOR)
        ->toHaveProperty('createdAt', new DateTime('2025-03-28T16:00:00+00:00'))
        ->and($model->profile)
        ->toHaveProperty('firstName', 'Andre')
        ->toHaveProperty('lastName', 'Gil')
        ->and($model->profile->post)
        ->toHaveProperty('title', 'How to validate data with classes in PHP')
        ->toHaveProperty('published', new DateTime('2025-03-31T18:00:00+00:00'))
        ->toHaveProperty('id', 1);

})
    ->with([true, false])
    ->group('validator', 'nested');

test('Invalid strict nested', function () {
    $validator = new Validator(strict: true);
    $data = [
        'profile' => [
            'firstName' => 'Andre',
            'lastName' => 'Gil',
            'post' => [
                'id' => 1.98,
                'title' => 'How to validate data with classes in PHP',
            ],
        ],
        'userType' => 'moderator',
        'createdAt' => '2025-03-28T16:00:00+00:00',
    ];
    $validator->validate($data, new Models\User);
})
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'nested');

test('Invalid nested', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $data = [
        'profile' => [
            'firstName' => 'Andre',
            'post' => [
                'id' => 'Invalid integer',
            ],
        ],
        'userType' => 'moderator',
    ];
    $validator->validate($data, new Models\User);
})
    ->with([true, false])
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'nested');
