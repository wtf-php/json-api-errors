<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use WtfPhp\JsonApiErrors\Factories\JsonErrorFactory;
use WtfPhp\JsonApiErrors\Models\JsonError;

class JsonErrorFactoryTest extends TestCase
{
    /** @test */
    public function itShouldReturnAJsonErrorObject() {
        $e = new Exception('foo', 200);
        $this->assertInstanceOf(JsonError::class, JsonErrorFactory::createFromThrowable($e));
    }

    /** @test */
    public function itShouldContainTheErrorCode() {
        $e = new Exception('bar', 2020);
        $jsonErrorObject = JsonErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonError::class, $jsonErrorObject);
        $this->assertEquals(2020, $jsonErrorObject->code);
    }

    /** @test */
    public function itShouldContainTheErrorTitle() {
        $e = new Exception('bar', 2020);
        $jsonErrorObject = JsonErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonError::class, $jsonErrorObject);
        $this->assertStringContainsString('bar', $jsonErrorObject->title);
    }

    /** @test */
    public function itShouldContainTheErrorMessage() {
        $e = new Exception('bar', 2020);
        $jsonErrorObject = JsonErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonError::class, $jsonErrorObject);
        $this->assertStringContainsString('{main}', $jsonErrorObject->detail);
    }

    /** @test */
    public function itShouldContainTheErrorLinks() {
        $e = new Exception('bar', 2020);
        $jsonErrorObject = JsonErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonError::class, $jsonErrorObject);
        $this->assertIsArray($jsonErrorObject->links);
        $this->assertArrayHasKey('file', $jsonErrorObject->links);
        $this->assertArrayHasKey('line', $jsonErrorObject->links);
        $this->assertStringContainsString('JsonErrorFactoryTest', $jsonErrorObject->links['file']);
    }

    /** @test */
    public function itShouldContainMultipleErrorObjects() {
        $exceptions = [
            new Exception('foo', 123),
            new Exception('bar', 1234),
            new Exception('baz', 12345)
        ];

        $arrayOfObjects = JsonErrorFactory::createFromThrowables($exceptions);

        $this->assertIsArray($arrayOfObjects);
        $this->assertCount(3, $arrayOfObjects);
        $this->assertInstanceOf(JsonError::class, $arrayOfObjects[0]);
    }
}
