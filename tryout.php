<?php

$composer = __DIR__.'/vendor/autoload.php';
require_once "$composer";

use Attributes\Validation\Validator;
use Respect\Validation\Rules as Rules;

$validator = new Validator;
$generator = new \Attributes\Validation\Cache\CacheGenerator($validator);
$generator->generate();

// class Person
// {
//    #[Rules\Min(0)]
//    public float|int $age;
//
//    public ?DateTime $birthday;
// }
//
// $rawData = [
//    'age' => '30',
//    'birthday' => '1994-01-01T09:00:00+00:00',
// ];
//
// $validator = new Validator;
// $person = $validator->validate($rawData, new Person);
//
// var_dump($person->age);      // int(100)
// var_dump($person->birthday); // object(DateTime) { ["date"] => string(26) "1994-01-01 09:00:00.000000", (...) }

// v::key('field1', v::optional(v::dateTime()), false)->assert($data);

// class Person extends BaseModel
// {
//    public float|int $age;
//
//    public DateTime $birthday;
// }
//
// $rawData = [
//    'age' => 100.0,
//    'birthday' => '2025-01-01 15:46:55',
// ];
//
// $validator = new Validator(strict: false);
// try {
//    $person = $validator->validate($rawData, new Person);
//    var_dump($person);
// } catch (ValidationException $error) {
//    echo $error->getMessage()."\n\r";
//    var_dump($error->getValidationResult()->getErrors());
// }

// interface Test
// {
//    public function test();
// }
//
// interface Another
// {
//    public function another();
// }
//
// class A implements Another
// {
//    public int $letterA;
//
//    public function another() {}
// }
//
// class T implements Test
// {
//    public string $letterT;
//
//    public function test() {}
// }
//
// class Both
// {
//    public T $t;
//
//    public A $a;
// }

// class Email
// {
//    private string $username;
//
//    private string $host;
// }
//
// class Profile
// {
//    private Email $email;
//
//    #[Rules\Positive]
//    private float $age;
// }
//
// enum Hello: string
// {
//    case DOLLY = 'dolly';
//    case ANOTHER = 'another';
// }
//
// class Person
// {
//    public Hello $hello;
//    //    public Profile $profile;
//    // public ?DateTimeInterface $birthday;
//
// }
//
// // class Test
// // {
// //    public ?int $id;
// // }
// //
// // $rawData = [
// //    'profile' => [
// //        'email' => ['username', 'email'],
// //        'age' => '-10',
// //    ],
// //    // 'birthday' => null,
// // ];
//
// $rawData = ['hello' => 'dolly'];

// $validator = new Validator(stopFirstError: false, strict: false);
// try {
//    $person = $validator->validate($rawData, new Test);
//    var_dump($person);
// } catch (ValidationException $e) {
//    var_dump($e->getInfo()->getErrors());
// }

// $value = new DateTime('2025-03-06T08:57:06+00:00');
// $rawData = ['value' => $value];

// $validator = new Validator(stopFirstError: false, strict: false);
// $person = $validator->validate($rawData, new Person);
// try {
//
//    var_dump($person);
// } catch (ValidationException $e) {
//    var_dump($e->getInfo()->getErrors());
// }

// var_dump(DateTime::createFromFormat('U', (string) $value)->format('Y-m-d H:i:s'));
