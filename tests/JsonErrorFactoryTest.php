<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use WtfPhp\JsonApiErrors\Exceptions\JsonErrorException;
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
    public function itShouldReturnAJsonErrorObjectFromJsonErrorException() {
        $jsonException = new JsonErrorException('a custom message', 0, null, 'foo', 504, ['bar' => 'baz']);
        $this->assertInstanceOf(JsonError::class, JsonErrorFactory::createFromThrowable($jsonException));
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
        $this->assertArrayHasKey('about', $jsonErrorObject->links);
        $this->assertStringContainsString('JsonErrorFactoryTest', $jsonErrorObject->links['about']);
    }

    /** @test */
    public function itShouldContainTheHttpStatusCode() {
        $jsonException = new JsonErrorException('a custom message', 0, null, 'foo', 504, ['bar' => 'baz']);
        $jsonErrorObject = JsonErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonError::class, $jsonErrorObject);
        $this->assertEquals(504, $jsonErrorObject->status);
    }

    /** @test */
    public function itShouldContainTheId() {
        $jsonException = new JsonErrorException('a custom message', 0, null, 'foo', 504, ['bar' => 'baz']);
        $jsonErrorObject = JsonErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonError::class, $jsonErrorObject);
        $this->assertEquals('foo', $jsonErrorObject->id);
    }

    /** @test */
    public function itShouldContainTheMetaInfo() {
        $jsonException = new JsonErrorException('a custom message', 0, null, 'foo', 504, ['bar' => 'baz']);
        $jsonErrorObject = JsonErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonError::class, $jsonErrorObject);
        $this->assertEquals(['bar' => 'baz'], $jsonErrorObject->meta);
    }

    /** @test */
    public function itShouldContainMultipleErrorObjects() {
        $exceptions = [
            new Exception('foo', 123),
            new Exception('bar', 1234),
            new Exception('baz', 12345),
            new JsonErrorException('a custom message', 0, null, 'foo', 504, ['bar' => 'baz'])
        ];

        $arrayOfObjects = JsonErrorFactory::createFromThrowables($exceptions);

        $this->assertIsArray($arrayOfObjects);
        $this->assertCount(4, $arrayOfObjects);
        $this->assertInstanceOf(JsonError::class, $arrayOfObjects[0]);
    }
}
