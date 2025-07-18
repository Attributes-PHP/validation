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

use Attributes\Options as Options;
use Attributes\Options\Exceptions\InvalidOptionException;
use Attributes\Options\Ignore;
use Attributes\Validation\Context;
use Attributes\Validation\Tests\Models as Models;
use Attributes\Validation\Validator;

// Alias and alias generator

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
    ->group('validator', 'options', 'alias', 'alias-generator');

// Alias generator

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
    ->group('validator', 'options', 'alias-generator');

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
    ->group('validator', 'options', 'alias-generator');

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
    ->group('validator', 'options', 'alias-generator');

test('Invalid AliasGenerator', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $validator->validate(['snake_case_name' => 'snake'], new #[Options\AliasGenerator('invalid')] class
    {
        public string $snake_case_name;
    });
})
    ->with([true, false])
    ->throws(InvalidOptionException::class, 'Invalid alias generator \'invalid\'')
    ->group('validator', 'options', 'alias-generator');

// Ignore

test('Ignore', function (bool $isStrict) {
    $validator = new Validator(strict: $isStrict);
    $rawData = [
        'value' => 'my value',
        'ignore' => 'both',
        'ignoreValidation' => 'validation',
        'ignoreSerialization' => 'serialization',
    ];
    $model = $validator->validate($rawData, new class
    {
        public string $value;

        #[Ignore]
        public string $ignore = 'original';

        #[Ignore(serialization: false)]
        public string $ignoreValidation = 'original';

        #[Ignore(validation: false)]
        public string $ignoreSerialization = 'original';
    });
    expect($model)
        ->toHaveProperty('value', 'my value')
        ->toHaveProperty('ignore', 'original')
        ->toHaveProperty('ignoreValidation', 'original')
        ->toHaveProperty('ignoreSerialization', 'serialization');
})
    ->with([true, false])
    ->group('validator', 'options', 'ignore');

test('Ignore as a serializer', function (bool $isStrict) {
    $context = new Context;
    $context->set('internal.options.ignore.useSerialization', true);
    $validator = new Validator(strict: $isStrict, context: $context);
    $rawData = [
        'value' => 'my value',
        'ignore' => 'both',
        'ignoreValidation' => 'validation',
        'ignoreSerialization' => 'serialization',
    ];
    $model = $validator->validate($rawData, new class
    {
        public string $value;

        #[Ignore]
        public string $ignore = 'original';

        #[Ignore(serialization: false)]
        public string $ignoreValidation = 'original';

        #[Ignore(validation: false)]
        public string $ignoreSerialization = 'original';
    });
    expect($model)
        ->toHaveProperty('value', 'my value')
        ->toHaveProperty('ignore', 'original')
        ->toHaveProperty('ignoreValidation', 'validation')
        ->toHaveProperty('ignoreSerialization', 'original');
})
    ->with([true, false])
    ->group('validator', 'options', 'ignore');
