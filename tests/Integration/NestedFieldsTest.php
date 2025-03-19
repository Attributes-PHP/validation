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

test('Valid nested', function () {
    $validator = new Validator;
    $data = [
//        'profile' => [
//            'firstName' => 'Andre',
//            'lastName' => 'Gil',
//            'post' => [
//                'title' => 'How to validate data with classes in PHP',
//            ],
//        ],
        'userType' => 'moderator',
    ];
    $model = $validator->validate($data, new Models\User);
    expect($model)
        ->toBeInstanceOf(Models\User::class);
})
    ->group('validator', 'nested');

//test('Invalid bool', function ($value) {
//    $validator = new Validator;
//    $validator->validate(['value' => $value], new Models\Boolean);
//})
//    ->with('invalid bool')
//    ->throws(ValidationException::class, 'Invalid data')
//    ->group('validator', 'nested');
