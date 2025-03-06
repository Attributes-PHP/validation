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

require_once __DIR__.'/Models/Basic/Bool.php';
require_once __DIR__.'/Models/Basic/String.php';
require_once __DIR__.'/Models/Basic/Int.php';
require_once __DIR__.'/Models/Basic/Float.php';
require_once __DIR__.'/Models/Basic/Array.php';
require_once __DIR__.'/Models/Basic/Object.php';
require_once __DIR__.'/Models/Basic/DateTime.php';

use Attributes\Validation\Exceptions\ValidationException;
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
})
    ->with([
        new Models\DefaultOptionalBool,
        new Models\DefaultOptionalStr,
        new Models\DefaultOptionalInt,
        new Models\DefaultOptionalFloat,
        new Models\DefaultOptionalArr,
        new Models\DefaultOptionalObj,
    ])
    ->group('validator', 'basic');

// Boolean validation

test('Bool type', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\Boolean);
    expect($model)
        ->toBeInstanceOf(Models\Boolean::class)
        ->toHaveProperty('value', is_string($value) ? strtolower($value) == 'true' : boolval($value));
})
    ->with('bool')
    ->group('validator', 'basic', 'bool');

test('Nullable bool type', function (?bool $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalBool);
    expect($model)
        ->toBeInstanceOf(Models\OptionalBool::class)
        ->toHaveProperty('value', $value);
})
    ->with([true, false, null])
    ->group('validator', 'basic', 'bool');

test('Default nullable bool type', function (?bool $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalBool);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalBool::class)
        ->toHaveProperty('value', $value);
})
    ->with([true, false, null])
    ->group('validator', 'basic', 'bool');

test('Using default value - bool', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultBool);
    expect($model)
        ->toBeInstanceOf(Models\DefaultBool::class)
        ->toHaveProperty('value', false);
})
    ->group('validator', 'basic', 'bool');

test('Invalid bool', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new Models\Boolean);
})
    ->with('invalid bool')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'bool');

// String validation

test('String type', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\Str);
    expect($model)
        ->toBeInstanceOf(Models\Str::class)
        ->toHaveProperty('value', strval($value));
})
    ->with('string')
    ->group('validator', 'basic', 'string');

test('Nullable string type', function (?string $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalStr);
    expect($model)
        ->toBeInstanceOf(Models\OptionalStr::class)
        ->toHaveProperty('value', $value);
})
    ->with(['My value', null])
    ->group('validator', 'basic', 'string');

test('Default nullable string type', function (?string $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalStr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalStr::class)
        ->toHaveProperty('value', $value);
})
    ->with(['My value', null])
    ->group('validator', 'basic', 'string');

test('Using default value - string', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultStr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultStr::class)
        ->toHaveProperty('value', 'My value');
})
    ->group('validator', 'basic', 'string');

test('Invalid string', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new Models\Str);
})
    ->with('invalid string')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'string');

// Integer validation

test('Integer type', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\Integer);
    expect($model)
        ->toBeInstanceOf(Models\Integer::class)
        ->toHaveProperty('value', (int) $value);
})
    ->with('integer')
    ->group('validator', 'basic', 'integer');

test('Optional integer type', function (?int $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalInt);
    expect($model)
        ->toBeInstanceOf(Models\OptionalInt::class)
        ->toHaveProperty('value', $value);
})
    ->with([123, null])
    ->group('validator', 'basic', 'integer');

test('Default optional integer type', function (?int $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalInt);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalInt::class)
        ->toHaveProperty('value', $value);
})
    ->with([123, null])
    ->group('validator', 'basic', 'integer');

test('Using default value - integer', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultInt);
    expect($model)
        ->toBeInstanceOf(Models\DefaultInt::class)
        ->toHaveProperty('value', 10);
})
    ->group('validator', 'basic', 'integer');

test('Invalid integer', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new Models\Integer);
})
    ->with('invalid integer')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'integer');

// Float validation

test('Float type', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\FloatPoint);
    expect($model)
        ->toBeInstanceOf(Models\FloatPoint::class)
        ->toHaveProperty('value', (float) $value);
})
    ->with('float')
    ->group('validator', 'basic', 'float');

test('Optional float type', function (?float $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalFloat);
    expect($model)
        ->toBeInstanceOf(Models\OptionalFloat::class)
        ->toHaveProperty('value', $value);
})
    ->with([123, null])
    ->group('validator', 'basic', 'float');

test('Default optional float type', function (?int $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalFloat);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalFloat::class)
        ->toHaveProperty('value', $value);
})
    ->with([123, null])
    ->group('validator', 'basic', 'float');

test('Using default value - float', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultFloat);
    expect($model)
        ->toBeInstanceOf(Models\DefaultFloat::class)
        ->toHaveProperty('value', 10.5);
})
    ->group('validator', 'basic', 'float');

test('Invalid float', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new Models\FloatPoint);
})
    ->with('invalid float')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'float');

// Array validation

test('Array type', function (array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\Arr);
    expect($model)
        ->toBeInstanceOf(Models\Arr::class)
        ->toHaveProperty('value', $value);
})
    ->with('array')
    ->group('validator', 'basic', 'array');

test('Optional array type', function (?array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalArr);
    expect($model)
        ->toBeInstanceOf(Models\OptionalArr::class)
        ->toHaveProperty('value', $value);
})
    ->with([[[123]], null])
    ->group('validator', 'basic', 'array');

test('Default optional array type', function (?array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalArr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalArr::class)
        ->toHaveProperty('value', $value);
})
    ->with([[[123]], null])
    ->group('validator', 'basic', 'array');

test('Using default value - array', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultArr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultArr::class)
        ->toHaveProperty('value', [12345]);
})
    ->group('validator', 'basic', 'array');

test('Invalid array', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new Models\Arr);
})
    ->with('invalid array')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'array');

// Object validation

test('Object type', function (object|array $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\Obj);
    expect($model)
        ->toBeInstanceOf(Models\Obj::class)
        ->toHaveProperty('value', (object) $value);
})
    ->with('object')
    ->group('validator', 'basic', 'object');

test('Optional object type', function (null|array|object $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalObj);
    expect($model)
        ->toBeInstanceOf(Models\OptionalObj::class)
        ->toHaveProperty('value', is_null($value) ? null : (object) $value);
})
    ->with([[[123]], null])
    ->group('validator', 'basic', 'object');

test('Default optional object type', function (null|array|object $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalObj);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalObj::class)
        ->toHaveProperty('value', is_null($value) ? null : (object) $value);
})
    ->with([[[123]], null])
    ->group('validator', 'basic', 'object');

test('Using default value - object', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultObj);
    expect($model)
        ->toBeInstanceOf(Models\DefaultObj::class)
        ->toHaveProperty('value', (object) [12345]);
})
    ->group('validator', 'basic', 'object');

test('Invalid object', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new Models\Obj);
})
    ->with('invalid object')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'object');

// DateTime validation

test('Datetime type', function (int|float|string|DateTime $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DateTime);
    if (is_numeric($value)) {
        $format = str_contains((string) $value, '.') ? 'U.u' : 'U';
        $value = DateTime::createFromFormat($format, (string) $value);
    }
    expect($model)
        ->toBeInstanceOf(Models\DateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})
    ->with('datetime')
    ->group('validator', 'basic', 'datetime');

test('Optional datetime type', function (null|string|DateTime $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\OptionalDateTime);
    expect($model)
        ->toBeInstanceOf(Models\OptionalDateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})
    ->with([null, '2025-03-06T08:57:06+00:00', new DateTime('2025-03-06T08:57:06+00:00')])
    ->group('validator', 'basic', 'datetime');

test('Default optional datetime type', function (null|string|DateTime $value) {
    $validator = new Validator;
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalDateTime);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalDateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})
    ->with(['2025-03-06T08:57:06+00:00', new DateTime('2025-03-06T08:57:06+00:00')])
    ->group('validator', 'basic', 'datetime');

test('Using default value - datetime', function () {
    $validator = new Validator;
    $model = $validator->validate([], new Models\DefaultDateTime);
    expect($model)
        ->toBeInstanceOf(Models\DefaultDateTime::class)
        ->toHaveProperty('value', new DateTime('2025-03-06T08:57:06+00:00'));
})
    ->group('validator', 'basic', 'datetime');

test('Invalid datetime', function ($value) {
    $validator = new Validator;
    $validator->validate(['value' => $value], new Models\DateTime);
})
    ->with('invalid datetime')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'datetime');
