<?php

namespace Attributes\Validation\Tests\Benchmark;

use Attributes\Validation\Tests\Models as Models;
use Attributes\Validation\Validator;
use Generator;
use PhpBench\Attributes as Bench;

class BenchValidator
{
    #[Bench\Iterations(100)]
    public function benchBuiltins()
    {
        $validator = new Validator;
        $data = [
            'bool' => 'true',
            'int' => '2',
            'float' => '-10e5',
            'array' => ['2', '3', '4', '5', '6', '7', '8', '9'],
            'object' => [
                'first' => 1,
                'second' => 2,
            ],
        ];
        $validator->validate($data, new Models\Complex\MultipleBasic);
    }

    #[Bench\Iterations(100)]
    public function benchUnions()
    {
        $validator = new Validator;
        $data = [
            'boolInt' => '12',
            'intFloat' => '2.9832',
            'floatArray' => ['-10e5'],
            'objectInt' => ['2', '3', '4', '5', '6', '7', '8', '9'],
        ];
        $validator->validate($data, new Models\Complex\BasicUnion);
    }

    #[Bench\Iterations(100)]
    public function benchNestedAndAttributes()
    {
        $validator = new Validator;
        $data = [
            'profile' => [
                'id' => '6d4238fd-eedf-4e79-b322-2b9f13a34a3f',
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
        $validator->validate($data, new Models\Nested\User);
    }

    public function getStrictOption(): Generator
    {
        yield 'strict' => ['isStrict' => true];
        yield 'loose' => ['isStrict' => false];
    }
}
