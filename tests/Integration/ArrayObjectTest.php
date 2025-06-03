<?php

/**
 * Holds integration tests for ArrayObject rules
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Validation\Tests\Integration;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Tests\Models\Nested\Post;
use Attributes\Validation\Types as Types;
use Attributes\Validation\Validator;

// Array of boolean's

test('Valid array of bool\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $model = $validator->validate(['value' => $allRawValues], new class
    {
        public Types\BoolArr $value;
    });
    $expectedValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $allExpectedValues = array_fill(0, 10, $expectedValue);
    expect($model)
        ->toBeObject()
        ->and((array) $model->value)
        ->toMatchArray($allExpectedValues);
})
    ->with('bool')
    ->group('validator', 'array-object', 'bool');

test('Invalid array of bool\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $validator->validate(['value' => $allRawValues], new class
    {
        public Types\BoolArr $value;
    });
})
    ->with('invalid bool')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'array-object', 'bool');

// Array of integer's

test('Valid array of int\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $model = $validator->validate(['value' => $allRawValues], new class
    {
        public Types\IntArr $value;
    });
    $expectedValue = (int) $value;
    $allExpectedValues = array_fill(0, 10, $expectedValue);
    expect($model)
        ->toBeObject()
        ->and((array) $model->value)
        ->toMatchArray($allExpectedValues);
})
    ->with('integer')
    ->group('validator', 'array-object', 'integer');

test('Invalid array of integer\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $validator->validate(['value' => $allRawValues], new class
    {
        public Types\IntArr $value;
    });
})
    ->with('invalid integer')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'array-object', 'integer');

// Array of float's

test('Valid array of float\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $model = $validator->validate(['value' => $allRawValues], new class
    {
        public Types\FloatArr $value;
    });
    $expectedValue = (float) $value;
    $allExpectedValues = array_fill(0, 10, $expectedValue);
    expect($model)
        ->toBeObject()
        ->and((array) $model->value)
        ->toMatchArray($allExpectedValues);
})
    ->with('float')
    ->group('validator', 'array-object', 'float');

test('Invalid array of float\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $validator->validate(['value' => $allRawValues], new class
    {
        public Types\FloatArr $value;
    });
})
    ->with('invalid float')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'array-object', 'float');

// Array of string's

test('Valid array of string\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $model = $validator->validate(['value' => $allRawValues], new class
    {
        public Types\StrArr $value;
    });
    $expectedValue = (string) $value;
    $allExpectedValues = array_fill(0, 10, $expectedValue);
    expect($model)
        ->toBeObject()
        ->and((array) $model->value)
        ->toMatchArray($allExpectedValues);
})
    ->with('float')
    ->group('validator', 'array-object', 'string');

test('Invalid array of string\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $validator->validate(['value' => $allRawValues], new class
    {
        public Types\StrArr $value;
    });
})
    ->with('invalid string')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'array-object', 'string');

// Array of array's

test('Valid array of array\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $model = $validator->validate(['value' => $allRawValues], new class
    {
        public Types\ArrArr $value;
    });
    $expectedValue = (array) $value;
    $allExpectedValues = array_fill(0, 10, $expectedValue);
    expect($model)
        ->toBeObject()
        ->and((array) $model->value)
        ->toMatchArray($allExpectedValues);
})
    ->with('array')
    ->group('validator', 'array-object', 'array');

test('Invalid array of array\'s', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $validator->validate(['value' => $allRawValues], new class
    {
        public Types\ArrArr $value;
    });
})
    ->with('invalid array')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'array-object', 'array');

// Array of objects

test('Valid array of objects', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $model = $validator->validate(['value' => $allRawValues], new class
    {
        public Types\ObjArr $value;
    });
    $expectedValue = (object) $value;
    $allExpectedValues = array_fill(0, 10, $expectedValue);
    expect($model)
        ->toBeObject()
        ->and((array) $model->value)
        ->toMatchArray($allExpectedValues);
})
    ->with('object')
    ->group('validator', 'array-object', 'object');

test('Invalid array of objects', function (mixed $value) {
    $validator = new Validator;
    $allRawValues = array_fill(0, 10, $value);
    $validator->validate(['value' => $allRawValues], new class
    {
        public Types\ObjArr $value;
    });
    var_dump($value);
})
    ->with('invalid object')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'array-object', 'object');

// Custom object

test('Valid array of a custom class', function () {
    $validator = new Validator;
    $allRawPosts = [];
    for ($i = 0; $i < 10; $i++) {
        $allRawPosts[] = ['id' => $i, 'title' => 'My title'];
    }
    class PostsArr extends Types\ArrayOf
    {
        private Post $type;
    }

    $model = $validator->validate(['value' => $allRawPosts], new class
    {
        public PostsArr $value;
    });

    expect($model)
        ->toBeObject()
        ->and($model->value)
        ->toHaveCount(10)
        ->toContainOnlyInstancesOf(Post::class);
})
    ->group('validator', 'array-object', 'custom-class');

test('Invalid array of a custom class', function (mixed $value) {
    $validator = new Validator;

    $validator->validate(['value' => $value], new class
    {
        public PostsArr $value;
    });
})
    ->with('invalid object')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'array-object', 'custom-class');
