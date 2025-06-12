<?php

namespace Attributes\Validation\Tests\Benchmark;

use Attributes\Validation\Tests\Models as Models;
use DateTime;
use DateTimeInterface;
use Generator;
use PhpBench\Attributes as Bench;
use Respect\Validation\Validator as v;

class BenchValidator
{
    #[Bench\Iterations(100)]
    public function benchBuiltins()
    {
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
        v::keySet(
            v::key('bool', v::boolVal()->notOptional()),
            v::key('int', v::finite()->numericVal()),
            v::key('float', v::finite()->floatVal()->not(v::boolType())),
            v::key('array', v::arrayVal()),
            v::key('object', v::anyOf(v::objectType(), v::arrayVal())),
        )->assert($data);
        $model = new Models\Complex\MultipleBasic;
        $model->bool = $data['bool'] === 'true';
        $model->int = $data['int'] + 0;
        $model->float = $data['float'] + 0;
        $model->array = $data['array'];
        $model->object = (object) $data['object'];
    }

    #[Bench\Iterations(100)]
    public function benchUnions()
    {
        $data = [
            'boolInt' => '12',
            'intFloat' => '2.9832',
            'floatArray' => ['-10e5'],
            'objectInt' => ['2', '3', '4', '5', '6', '7', '8', '9'],
        ];
        v::keySet(
            v::key('boolInt', v::oneOf(v::boolVal(), v::intVal())),
            v::key('intFloat', v::oneOf(v::intVal(), v::floatVal())),
            v::key('floatArray', v::oneOf(v::floatVal(), v::arrayVal())),
            v::key('objectInt', v::oneOf(v::intVal(), v::arrayVal())),
        )->assert($data);
        $model = new Models\Complex\BasicUnion;
        $model->boolInt = is_bool(filter_var($data['boolInt'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) ?: (int) $data['boolInt'];
        $model->intFloat = $data['intFloat'] + 0;
        $model->floatArray = is_numeric($data['floatArray']) ? (float) $data['floatArray'] : $data['floatArray'];
        $model->objectInt = is_array($data['objectInt']) ? (object) $data['objectInt'] : (int) $data['objectInt'];
    }

    #[Bench\Iterations(100)]
    #[Bench\ParamProviders(['getSampleArray'])]
    public function benchArrays(array $params)
    {
        v::arrayVal()->each(v::intVal())->assert($params['data']);
        foreach ($params['data'] as &$value) {
            $value = (int) $value;
        }
    }

    #[Bench\Iterations(100)]
    public function benchNestedAndAttributes()
    {
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
        v::keySet(
            v::key('profile', v::keySet(
                v::key('id', v::uuid()),
                v::key('firstName', v::stringVal()),
                v::key('lastName', v::stringVal()),
                v::key('post', v::keySet(
                    v::key('id', v::anyOf(v::stringVal(), v::intVal())),
                    v::key('title', v::stringVal()->notBlank()),
                )),
            )),
            v::key('userType', v::in(['admin', 'moderator', 'guest'])),
            v::key('createdAt', v::nullable(v::dateTime(DateTimeInterface::ATOM))),
        )->assert($data);
        $post = new Models\Nested\Post;
        $post->id = is_numeric($data['profile']['post']['id']) ? (int) $data['profile']['post']['id'] : (string) $data['profile']['post']['id'];
        $post->title = (string) $data['profile']['post']['title'];

        $profile = new Models\Nested\Profile;
        $profile->id = $data['profile']['id'];
        $profile->firstName = (string) $data['profile']['firstName'];
        $profile->lastName = (string) $data['profile']['lastName'];
        $profile->post = $post;

        $model = new Models\Nested\User;
        $model->profile = $profile;
        $model->userType = Models\Nested\UserType::from($data['userType']);
        $model->createdAt = DateTime::createFromFormat(DateTimeInterface::ATOM, (string) $data['createdAt']);
    }

    public function getSampleArray(): Generator
    {
        $numbers = [];
        for ($i = 0; $i < 100; $i++) {
            $numbers[$i] = $i;
        }
        yield 'numbers' => ['data' => $numbers];
    }
}
