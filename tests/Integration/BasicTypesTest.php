<?php

/**
 * Holds integration tests for the Validator
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Validation\Tests\Integration;

require_once __DIR__.'/Models/Basic/String.php';
require_once __DIR__.'/Models/Basic/Int.php';
require_once __DIR__.'/Models/Basic/Float.php';
require_once __DIR__.'/Models/Basic/Array.php';
require_once __DIR__.'/Models/Basic/Object.php';
require_once __DIR__.'/Models/Basic/DateTime.php';

use Attributes\Validation\Tests\Integration\Models\Basic as Models;
use Attributes\Validation\Validator;
use DateTime;

// Common

test('Using default optional value', function (object $instance) {
    $validator = new Validator;
    $model = $validator->validate([], $instance);
    expect($model)
        ->toBeInstanceOf($instance::class)
        ->toHaveProperty('value', null);
})->with([
    new Models\DefaultOptionalStr,
    new Models\DefaultOptionalInt,
    new Models\DefaultOptionalFloat,
    new Models\DefaultOptionalArr,
    new Models\DefaultOptionalObj,
])->group('validator', 'basic');

// String validation

test('String type', function () {
    $validator = new Validator;
    $model = $validator->validate(['value' => 'My value'], new Models\Str);
    expect($model)
        ->toBeInstanceOf(Models\Str::class)
        ->toHaveProperty('value', 'My value');
})->group('validator', 'basic', 'string');

test('Nullable string type', function (?string $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalStr);
    expect($model)
        ->toBeInstanceOf(Models\OptionalStr::class)
        ->toHaveProperty('value', $value);
})->with(['My value', null])->group('validator', 'basic', 'string');

test('Default nullable string type', function (?string $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalStr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalStr::class)
        ->toHaveProperty('value', $value);
})->with(['My value', null])->group('validator', 'basic', 'string');

test('Using default value - string', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultStr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultStr::class)
        ->toHaveProperty('value', 'My value');
})->group('validator', 'basic', 'string');

// Integer validation

test('Integer type', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\Integer);
    expect($model)
        ->toBeInstanceOf(Models\Integer::class)
        ->toHaveProperty('value', (int) $value);
})->with(['123', 123, 123.5])->group('validator', 'basic', 'integer');

test('Optional integer type', function (?int $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalInt);
    expect($model)
        ->toBeInstanceOf(Models\OptionalInt::class)
        ->toHaveProperty('value', $value);
})->with([123, null])->group('validator', 'basic', 'integer');

test('Default optional integer type', function (?int $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalInt);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalInt::class)
        ->toHaveProperty('value', $value);
})->with([123, null])->group('validator', 'basic', 'integer');

test('Using default value - integer', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultInt);
    expect($model)
        ->toBeInstanceOf(Models\DefaultInt::class)
        ->toHaveProperty('value', 10);
})->group('validator', 'basic', 'integer');

// Float validation

test('Float type', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\FloatPoint);
    expect($model)
        ->toBeInstanceOf(Models\FloatPoint::class)
        ->toHaveProperty('value', (float) $value);
})->with(['123', 123, 123.5])->group('validator', 'basic', 'float');

test('Optional float type', function (?float $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalFloat);
    expect($model)
        ->toBeInstanceOf(Models\OptionalFloat::class)
        ->toHaveProperty('value', $value);
})->with([123, null])->group('validator', 'basic', 'float');

test('Default optional float type', function (?int $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalFloat);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalFloat::class)
        ->toHaveProperty('value', $value);
})->with([123, null])->group('validator', 'basic', 'float');

test('Using default value - float', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultFloat);
    expect($model)
        ->toBeInstanceOf(Models\DefaultFloat::class)
        ->toHaveProperty('value', 10.5);
})->group('validator', 'basic', 'float');

// Array validation

test('Array type', function (array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\Arr);
    expect($model)
        ->toBeInstanceOf(Models\Arr::class)
        ->toHaveProperty('value', $value);
})->with([[[123]], [['a' => 1, 'b' => 2]]])->group('validator', 'basic', 'array');

test('Optional array type', function (?array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalArr);
    expect($model)
        ->toBeInstanceOf(Models\OptionalArr::class)
        ->toHaveProperty('value', $value);
})->with([[[123]], null])->group('validator', 'basic', 'array');

test('Default optional array type', function (?array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalArr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalArr::class)
        ->toHaveProperty('value', $value);
})->with([[[123]], null])->group('validator', 'basic', 'array');

test('Using default value - array', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultArr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultArr::class)
        ->toHaveProperty('value', [12345]);
})->group('validator', 'basic', 'array');

// Object validation

test('Object type', function (object|array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\Obj);
    expect($model)
        ->toBeInstanceOf(Models\Obj::class)
        ->toHaveProperty('value', (object) $value);
})->with([[[123]], [['a' => 1, 'b' => 2]]])->group('validator', 'basic', 'object');

test('Optional object type', function (null|array|object $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalObj);
    expect($model)
        ->toBeInstanceOf(Models\OptionalObj::class)
        ->toHaveProperty('value', is_null($value) ? null : (object) $value);
})->with([[[123]], null])->group('validator', 'basic', 'object');

test('Default optional object type', function (null|array|object $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalObj);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalObj::class)
        ->toHaveProperty('value', is_null($value) ? null : (object) $value);
})->with([[[123]], null])->group('validator', 'basic', 'object');

test('Using default value - object', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultObj);
    expect($model)
        ->toBeInstanceOf(Models\DefaultObj::class)
        ->toHaveProperty('value', (object) [12345]);
})->group('validator', 'basic', 'object');

// DateTime validation

test('Datetime type', function (string|DateTime $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DateTime);
    expect($model)
        ->toBeInstanceOf(Models\DateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})->with(['2025-01-01 15:46:55', new DateTime])->group('validator', 'basic', 'datetime');

test('Optional datetime type', function (null|string|DateTime $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalDateTime);
    expect($model)
        ->toBeInstanceOf(Models\OptionalDateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})->with([null, '2025-01-01 15:46:55', new DateTime])->group('validator', 'basic', 'datetime');

test('Default optional datetime type', function (null|string|DateTime $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalDateTime);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalDateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})->with(['2025-01-01 15:46:55', new DateTime])->group('validator', 'basic', 'datetime');

test('Using default value - datetime', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultDateTime);
    expect($model)
        ->toBeInstanceOf(Models\DefaultDateTime::class)
        ->toHaveProperty('value', new DateTime('2025-01-01 00:00:00'));
})->group('validator', 'basic', 'datetime');
