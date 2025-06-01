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
use Attributes\Validation\Options as Options;
use Attributes\Validation\Tests\Models as Models;
use Attributes\Validation\Validator;

test('Alias/AliasGenerator options', function (bool $isStrict) {
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
    $model = $validator->validate($rawData, new Models\Complex\User);
    expect($model)
        ->toBeInstanceOf(Models\Complex\User::class)
        ->toHaveProperty('full_name', 'Full Name')
        ->and($model->my_profile->my_post)
        ->toHaveProperty('my_post_id', 1)
        ->toHaveProperty('my_title', 'My Post Title');
})
    ->with([true, false])
    ->group('validator', 'options', 'nested');

test('AliasGenerator camelCase', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $rawData = [
        'snakeCaseName' => 'snake',
        'pascalCaseName' => 'pascal',
        'camelCaseName' => 'camel',
    ];
    $model = $validator->validate($rawData, new #[Options\AliasGenerator('camel')] class
    {
        public string $snake_case_name;

        public string $PascalCaseName;

        public string $camelCaseName;
    });
    expect($model)
        ->toHaveProperty('snake_case_name', 'snake')
        ->toHaveProperty('PascalCaseName', 'pascal')
        ->toHaveProperty('camelCaseName', 'camel');
})
    ->with([true, false])
    ->group('validator', 'options', 'basic');

test('AliasGenerator PascalCase', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $rawData = [
        'SnakeCaseName' => 'snake',
        'PascalCaseName' => 'pascal',
        'CamelCaseName' => 'camel',
    ];
    $model = $validator->validate($rawData, new #[Options\AliasGenerator('pascal')] class
    {
        public string $snake_case_name;

        public string $PascalCaseName;

        public string $camelCaseName;
    });
    expect($model)
        ->toHaveProperty('snake_case_name', 'snake')
        ->toHaveProperty('PascalCaseName', 'pascal')
        ->toHaveProperty('camelCaseName', 'camel');
})
    ->with([true, false])
    ->group('validator', 'options', 'basic');

test('AliasGenerator SnakeCase', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $rawData = [
        'snake_case_name' => 'snake',
        'pascal_case_name' => 'pascal',
        'camel_case_name' => 'camel',
    ];
    $model = $validator->validate($rawData, new #[Options\AliasGenerator('snake')] class
    {
        public string $snake_case_name;

        public string $PascalCaseName;

        public string $camelCaseName;
    });
    expect($model)
        ->toHaveProperty('snake_case_name', 'snake')
        ->toHaveProperty('PascalCaseName', 'pascal')
        ->toHaveProperty('camelCaseName', 'camel');
})
    ->with([true, false])
    ->group('validator', 'options', 'basic');

test('Invalid AliasGenerator', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $validator->validate(['snake_case_name' => 'snake'], new #[Options\AliasGenerator('invalid')] class
    {
        public string $snake_case_name;
    });
})
    ->with([true, false])
    ->throws(ValidationException::class, 'Invalid alias generator \'invalid\'')
    ->group('validator', 'options', 'basic');
