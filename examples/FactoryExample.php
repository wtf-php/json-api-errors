<?php

use WtfPhp\JsonApiErrors\Factories\JsonErrorFactory;
use WtfPhp\JsonApiErrors\ThrowablesBag;

require '../vendor/autoload.php';

$bag = new ThrowablesBag();
$exception = new Exception('foobar',123);
$anotherException = new Exception('baz', 400);

$bag->add($exception);

$singleJsonObject = JsonErrorFactory::createFromThrowable($exception);
$multipleJsonObjects = JsonErrorFactory::createFromThrowables($bag->getAll());

print_r($singleJsonObject);
print_r($multipleJsonObjects);
