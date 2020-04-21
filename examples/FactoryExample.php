<?php

use WtfPhp\JsonApiErrors\Exceptions\JsonErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonErrorFactory;
use WtfPhp\JsonApiErrors\ThrowablesBag;

require '../vendor/autoload.php';

$bag = new ThrowablesBag();
$exception = new Exception('foobar',123);
$anotherException = new Exception('baz', 400);
$jsonErrorException = new JsonErrorException('a custom message', 0, null, 'foo', 504, ['bar' => 'baz']);

$bag->add($exception);
$bag->add($anotherException);
$bag->add($jsonErrorException);

$singleJsonObject = JsonErrorFactory::createFromThrowable($exception);
$singleJsonObjectFromJsonErrorException = JsonErrorFactory::createFromThrowable($jsonErrorException);
$multipleJsonObjects = JsonErrorFactory::createFromThrowables($bag->getAll());

print_r($singleJsonObject);
print_r($multipleJsonObjects);
print_r($singleJsonObjectFromJsonErrorException);
