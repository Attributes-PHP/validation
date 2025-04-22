<?php

$composer = __DIR__.'/../vendor/autoload.php';
require_once $composer;

require_once __DIR__.'/Integration/Models/Basic/Bool.php';
require_once __DIR__.'/Integration/Models/Basic/String.php';
require_once __DIR__.'/Integration/Models/Basic/Int.php';
require_once __DIR__.'/Integration/Models/Basic/Float.php';
require_once __DIR__.'/Integration/Models/Basic/Array.php';
require_once __DIR__.'/Integration/Models/Basic/Object.php';
require_once __DIR__.'/Integration/Models/Basic/DateTime.php';
require_once __DIR__.'/Integration/Models/Basic/Enum.php';
require_once __DIR__.'/Integration/Models/Complex/Intersection.php';

uses()->group('integration')->in('Integration');
uses()->group('unit')->in('Unit');
