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

use Attributes\Validation\Tests\Integration\Models\Basic\DefaultNullIntModel;
use Attributes\Validation\Tests\Integration\Models\Basic\DefaultNullStringModel;
use Attributes\Validation\Tests\Integration\Models\Basic\NullableStringModel;
use Attributes\Validation\Tests\Integration\Models\Basic\StringModel;
use Attributes\Validation\Validator;

// String validation

test('String type', function () {
    $validator = new Validator;
    $model = $validator->validate(['string' => 'My value'], new StringModel);
    expect($model)
        ->toBeInstanceOf(StringModel::class)
        ->toHaveProperty('string', 'My value');
})->group('validator', 'basic', 'string');

test('Nullable string type', function (?string $value) {
    $validator = new Validator;
    $model = $validator->validate(['string' => $value], new NullableStringModel);
    expect($model)
        ->toBeInstanceOf(NullableStringModel::class)
        ->toHaveProperty('string', $value);
})->with(['My value', null])->group('validator', 'basic', 'string');

test('Default nullable string type', function (?string $value) {
    $validator = new Validator;
    $model = $validator->validate(['string' => $value], new DefaultNullStringModel);
    expect($model)
        ->toBeInstanceOf(DefaultNullStringModel::class)
        ->toHaveProperty('string', $value);
})->with(['My value', null])->group('validator', 'basic', 'string');

test('Using default nullable value - string', function () {
    $validator = new Validator;
    $model = $validator->validate([], new DefaultNullStringModel);
    expect($model)
        ->toBeInstanceOf(DefaultNullStringModel::class)
        ->toHaveProperty('string', null);
})->group('validator', 'basic', 'string');

// Integer validation

test('Integer type', function ($value) {
    $validator = new Validator;
    $model = $validator->validate(['integer' => $value], new StringModel);
    expect($model)
        ->toBeInstanceOf(StringModel::class)
        ->toHaveProperty('integer', (int) $value);
})->with(['123', 123, 123.5])->group('validator', 'basic', 'integer');

test('Nullable integer type', function (?int $value) {
    $validator = new Validator;
    $model = $validator->validate(['string' => $value], new NullableStringModel);
    expect($model)
        ->toBeInstanceOf(NullableStringModel::class)
        ->toHaveProperty('integer', $value);
})->with([123, null])->group('validator', 'basic', 'integer');

test('Default nullable integer type', function (?int $value) {
    $validator = new Validator;
    $model = $validator->validate(['integer' => $value], new DefaultNullIntModel);
    expect($model)
        ->toBeInstanceOf(DefaultNullIntModel::class)
        ->toHaveProperty('integer', $value);
})->with([123, null])->group('validator', 'basic', 'integer');

test('Using default nullable value - integer', function () {
    $validator = new Validator;
    $model = $validator->validate([], new DefaultNullIntModel);
    expect($model)
        ->toBeInstanceOf(DefaultNullIntModel::class)
        ->toHaveProperty('integer', null);
})->group('validator', 'basic', 'integer');
