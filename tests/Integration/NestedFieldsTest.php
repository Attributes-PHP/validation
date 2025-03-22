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

use Attributes\Validation\Tests\Integration\Models\Nested as Models;
use Attributes\Validation\Validator;
use DateTime;

test('Valid nested', function () {
    $validator = new Validator;
    $data = [
        'profile' => [
            'firstName' => 'Andre',
            'lastName' => 'Gil',
            'post' => [
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
        ->toHaveProperty('published', new DateTime('2025-03-31T18:00:00+00:00'));

})
    ->group('validator', 'nested');

//test('Invalid bool', function ($value) {
//    $validator = new Validator;
//    $validator->validate(['value' => $value], new Models\Boolean);
//})
//    ->with('invalid bool')
//    ->throws(ValidationException::class, 'Invalid data')
//    ->group('validator', 'nested');
