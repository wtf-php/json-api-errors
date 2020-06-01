<?php

use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Bags\ThrowablesBag;

require '../vendor/autoload.php';

$bag = new ThrowablesBag();
$exception = new Exception('foobar', 123);
$anotherException = new Exception('baz', 400);
$jsonErrorException = new JsonApiErrorException(
    'a custom message',
    303,
    null,
    500,
    '504',
    ['bar' => 'baz'],
    'testlink'
);

$bag->add($exception);
$bag->add($anotherException);
$bag->add($jsonErrorException);

$singleJsonObject = JsonApiErrorFactory::createFromThrowable($exception);
$singleJsonObjectFromJsonErrorException = JsonApiErrorFactory::createFromThrowable($jsonErrorException);
$multipleJsonObjects = JsonApiErrorFactory::createFromThrowables($bag->getAll());

print_r($singleJsonObject);
print_r($multipleJsonObjects);
print_r($singleJsonObjectFromJsonErrorException);
