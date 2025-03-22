<?php

/**
 * Holds integration tests for strict validation of basic type-hints
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Validation\Tests\Integration;

use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Tests\Integration\Models\Basic as Models;
use Attributes\Validation\Validator;
use DateTime;

// Common

test('Using default optional value', function (object $instance) {
    $validator = new Validator(strict: true);
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
    ->group('validator', 'basic', 'strict');

// Boolean validation

test('Bool type', function ($value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\Boolean);
    expect($model)
        ->toBeInstanceOf(Models\Boolean::class)
        ->toHaveProperty('value', is_string($value) ? strtolower($value) == 'true' : boolval($value));
})
    ->with('strict bool')
    ->group('validator', 'basic', 'bool', 'strict');

test('Nullable bool type', function (?bool $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\OptionalBool);
    expect($model)
        ->toBeInstanceOf(Models\OptionalBool::class)
        ->toHaveProperty('value', $value);
})
    ->with([true, false, null])
    ->group('validator', 'basic', 'bool', 'strict');

test('Default nullable bool type', function (?bool $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalBool);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalBool::class)
        ->toHaveProperty('value', $value);
})
    ->with([true, false, null])
    ->group('validator', 'basic', 'bool', 'strict');

test('Using default value - bool', function () {
    $validator = new Validator(strict: true);
    $model = $validator->validate([], new Models\DefaultBool);
    expect($model)
        ->toBeInstanceOf(Models\DefaultBool::class)
        ->toHaveProperty('value', false);
})
    ->group('validator', 'basic', 'bool', 'strict');

test('Invalid bool', function ($value) {
    $validator = new Validator(strict: true);
    $validator->validate(['value' => $value], new Models\Boolean);
})
    ->with('strict invalid bool')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'bool', 'strict');

// String validation

test('String type', function ($value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\Str);
    expect($model)
        ->toBeInstanceOf(Models\Str::class)
        ->toHaveProperty('value', strval($value));
})
    ->with('strict string')
    ->group('validator', 'basic', 'string', 'strict');

test('Nullable string type', function (?string $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\OptionalStr);
    expect($model)
        ->toBeInstanceOf(Models\OptionalStr::class)
        ->toHaveProperty('value', $value);
})
    ->with(['My value', null])
    ->group('validator', 'basic', 'string', 'strict');

test('Default nullable string type', function (?string $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalStr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalStr::class)
        ->toHaveProperty('value', $value);
})
    ->with(['My value', null])
    ->group('validator', 'basic', 'string', 'strict');

test('Using default value - string', function () {
    $validator = new Validator(strict: true);
    $model = $validator->validate([], new Models\DefaultStr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultStr::class)
        ->toHaveProperty('value', 'My value');
})
    ->group('validator', 'basic', 'string', 'strict');

test('Invalid string', function ($value) {
    $validator = new Validator(strict: true);
    $validator->validate(['value' => $value], new Models\Str);
})
    ->with('strict invalid string')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'string', 'strict');

// Integer validation

test('Integer type', function ($value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\Integer);
    expect($model)
        ->toBeInstanceOf(Models\Integer::class)
        ->toHaveProperty('value', (int) $value);
})
    ->with('strict integer')
    ->group('validator', 'basic', 'integer', 'strict');

test('Optional integer type', function (?int $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\OptionalInt);
    expect($model)
        ->toBeInstanceOf(Models\OptionalInt::class)
        ->toHaveProperty('value', $value);
})
    ->with([123, null])
    ->group('validator', 'basic', 'integer', 'strict');

test('Default optional integer type', function (?int $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalInt);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalInt::class)
        ->toHaveProperty('value', $value);
})
    ->with([123, null])
    ->group('validator', 'basic', 'integer', 'strict');

test('Using default value - integer', function () {
    $validator = new Validator(strict: true);
    $model = $validator->validate([], new Models\DefaultInt);
    expect($model)
        ->toBeInstanceOf(Models\DefaultInt::class)
        ->toHaveProperty('value', 10);
})
    ->group('validator', 'basic', 'integer', 'strict');

test('Invalid integer', function ($value) {
    $validator = new Validator(strict: true);
    $validator->validate(['value' => $value], new Models\Integer);
})
    ->with('strict invalid integer')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'integer', 'strict');

// Float validation

test('Float type', function ($value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\FloatPoint);
    expect($model)
        ->toBeInstanceOf(Models\FloatPoint::class)
        ->toHaveProperty('value', (float) $value);
})
    ->with('strict float')
    ->group('validator', 'basic', 'float', 'strict');

test('Optional float type', function (?float $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\OptionalFloat);
    expect($model)
        ->toBeInstanceOf(Models\OptionalFloat::class)
        ->toHaveProperty('value', $value);
})
    ->with([123.0, null])
    ->group('validator', 'basic', 'float', 'strict');

test('Default optional float type', function (?float $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalFloat);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalFloat::class)
        ->toHaveProperty('value', $value);
})
    ->with([123.0, null])
    ->group('validator', 'basic', 'float', 'strict');

test('Using default value - float', function () {
    $validator = new Validator(strict: true);
    $model = $validator->validate([], new Models\DefaultFloat);
    expect($model)
        ->toBeInstanceOf(Models\DefaultFloat::class)
        ->toHaveProperty('value', 10.5);
})
    ->group('validator', 'basic', 'float', 'strict');

test('Invalid float', function ($value) {
    $validator = new Validator(strict: true);
    $validator->validate(['value' => $value], new Models\FloatPoint);
})
    ->with('strict invalid float')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'float', 'strict');

// Array validation

test('Array type', function (array $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\Arr);
    expect($model)
        ->toBeInstanceOf(Models\Arr::class)
        ->toHaveProperty('value', $value);
})
    ->with('strict array')
    ->group('validator', 'basic', 'array', 'strict');

test('Optional array type', function (?array $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\OptionalArr);
    expect($model)
        ->toBeInstanceOf(Models\OptionalArr::class)
        ->toHaveProperty('value', $value);
})
    ->with([[[123]], null])
    ->group('validator', 'basic', 'array', 'strict');

test('Default optional array type', function (?array $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalArr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalArr::class)
        ->toHaveProperty('value', $value);
})
    ->with([[[123]], null])
    ->group('validator', 'basic', 'array', 'strict');

test('Using default value - array', function () {
    $validator = new Validator(strict: true);
    $model = $validator->validate([], new Models\DefaultArr);
    expect($model)
        ->toBeInstanceOf(Models\DefaultArr::class)
        ->toHaveProperty('value', [12345]);
})
    ->group('validator', 'basic', 'array', 'strict');

test('Invalid array', function ($value) {
    $validator = new Validator(strict: true);
    $validator->validate(['value' => $value], new Models\Arr);
})
    ->with('strict invalid array')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'array', 'strict');

// Object validation

test('Object type', function (object|array $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\Obj);
    expect($model)
        ->toBeInstanceOf(Models\Obj::class)
        ->toHaveProperty('value', (object) $value);
})
    ->with('strict object')
    ->group('validator', 'basic', 'object', 'strict');

test('Optional object type', function (null|array|object $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\OptionalObj);
    expect($model)
        ->toBeInstanceOf(Models\OptionalObj::class)
        ->toHaveProperty('value', is_null($value) ? null : (object) $value);
})
    ->with([[[123]], null])
    ->group('validator', 'basic', 'object', 'strict');

test('Default optional object type', function (null|array|object $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalObj);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalObj::class)
        ->toHaveProperty('value', is_null($value) ? null : (object) $value);
})
    ->with([[[123]], null])
    ->group('validator', 'basic', 'object', 'strict');

test('Using default value - object', function () {
    $validator = new Validator(strict: true);
    $model = $validator->validate([], new Models\DefaultObj);
    expect($model)
        ->toBeInstanceOf(Models\DefaultObj::class)
        ->toHaveProperty('value', (object) [12345]);
})
    ->group('validator', 'basic', 'object', 'strict');

test('Invalid object', function ($value) {
    $validator = new Validator(strict: true);
    $validator->validate(['value' => $value], new Models\Obj);
})
    ->with('strict invalid object')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'object', 'strict');

// DateTime validation

test('Datetime type', function (int|float|string|DateTime $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\DateTime);
    if (is_numeric($value)) {
        $format = str_contains((string) $value, '.') ? 'U.u' : 'U';
        $value = DateTime::createFromFormat($format, (string) $value);
    }
    expect($model)
        ->toBeInstanceOf(Models\DateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})
    ->with('strict datetime')
    ->group('validator', 'basic', 'datetime', 'strict');

test('Optional datetime type', function (null|string|DateTime $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\OptionalDateTime);
    expect($model)
        ->toBeInstanceOf(Models\OptionalDateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})
    ->with([null, '2025-03-06T08:57:06+00:00', new DateTime('2025-03-06T08:57:06+00:00')])
    ->group('validator', 'basic', 'datetime', 'strict');

test('Default optional datetime type', function (null|string|DateTime $value) {
    $validator = new Validator(strict: true);
    $model = $validator->validate(['value' => $value], new Models\DefaultOptionalDateTime);
    expect($model)
        ->toBeInstanceOf(Models\DefaultOptionalDateTime::class)
        ->toHaveProperty('value', is_string($value) ? new DateTime($value) : $value);
})
    ->with(['2025-03-06T08:57:06+00:00', new DateTime('2025-03-06T08:57:06+00:00')])
    ->group('validator', 'basic', 'datetime', 'strict');

test('Using default value - datetime', function () {
    $validator = new Validator(strict: true);
    $model = $validator->validate([], new Models\DefaultDateTime);
    expect($model)
        ->toBeInstanceOf(Models\DefaultDateTime::class)
        ->toHaveProperty('value', new DateTime('2025-03-06T08:57:06+00:00'));
})
    ->group('validator', 'basic', 'datetime', 'strict');

test('Invalid datetime', function ($value) {
    $validator = new Validator(strict: true);
    $validator->validate(['value' => $value], new Models\DateTime);
})
    ->with('strict invalid datetime')
    ->throws(ValidationException::class, 'Invalid data')
    ->group('validator', 'basic', 'datetime', 'strict');
