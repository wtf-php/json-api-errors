<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Models\JsonApiError;

class JsonErrorFactoryTest extends TestCase
{
    /** @test */
    public function itShouldReturnAJsonErrorObjectFromException()
    {
        $e = new Exception('foo', 200);
        $this->assertInstanceOf(JsonApiError::class, JsonApiErrorFactory::createFromThrowable($e));
    }

    /** @test */
    public function itShouldReturnAJsonErrorObjectFromJsonErrorException()
    {
        $jsonException = new JsonApiErrorException('a custom message', 0, null, 'foo', '504', ['bar' => 'baz'], 'testlink');
        $this->assertInstanceOf(JsonApiError::class, JsonApiErrorFactory::createFromThrowable($jsonException));
    }

    /** @test */
    public function itShouldContainTheErrorCode()
    {
        $e = new Exception('bar', 2020);
        $jsonErrorObject = JsonApiErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonErrorObject);
        $this->assertEquals(2020, $jsonErrorObject->code);
    }

    /** @test */
    public function itShouldContainTheErrorTitle()
    {
        $e = new Exception('bar', 2020);
        $jsonErrorObject = JsonApiErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonErrorObject);
        $this->assertStringContainsString('bar', $jsonErrorObject->title);
    }

    /** @test */
    public function itShouldContainTheErrorMessage()
    {
        $e = new Exception('bar', 2020);
        $jsonErrorObject = JsonApiErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonErrorObject);
        $this->assertStringContainsString('{main}', $jsonErrorObject->detail);
    }

    /** @test */
    public function itShouldContainTheErrorLinks()
    {
        $jsonException = new JsonApiErrorException('a custom message', 0, null, 'foo', '504', ['bar' => 'baz'], 'testlink');
        $jsonErrorObject = JsonApiErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonErrorObject);
        $this->assertIsArray($jsonErrorObject->links);
        $this->assertArrayHasKey('about', $jsonErrorObject->links);
        $this->assertStringContainsString('testlink', $jsonErrorObject->links['about']);
    }

    /** @test */
    public function itShouldContainTheHttpStatusCode()
    {
        $jsonException = new JsonApiErrorException('a custom message', 0, null, 'foo', '504', ['bar' => 'baz'], 'testlink');
        $jsonErrorObject = JsonApiErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonErrorObject);
        $this->assertEquals('504', $jsonErrorObject->status);
    }

    /** @test */
    public function itShouldContainTheId()
    {
        $jsonException = new JsonApiErrorException('a custom message', 0, null, 'foo', '504', ['bar' => 'baz'], 'testlink');
        $jsonErrorObject = JsonApiErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonErrorObject);
        $this->assertEquals('foo', $jsonErrorObject->id);
    }

    /** @test */
    public function itShouldContainTheMetaInfo()
    {
        $jsonException = new JsonApiErrorException('a custom message', 0, null, 'foo', '504', ['bar' => 'baz'], 'testlink');
        $jsonErrorObject = JsonApiErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonErrorObject);
        $this->assertEquals(['bar' => 'baz'], $jsonErrorObject->meta);
    }

    /** @test */
    public function itShouldContainMultipleErrorObjects()
    {
        $exceptions = [
            new Exception('foo', 123),
            new Exception('bar', 1234),
            new Exception('baz', 12345),
            new JsonApiErrorException('a custom message', 0, null, 'foo', '504', ['bar' => 'baz'], 'testlink')
        ];

        $arrayOfObjects = JsonApiErrorFactory::createFromThrowables($exceptions);

        $this->assertIsArray($arrayOfObjects);
        $this->assertCount(4, $arrayOfObjects);
        $this->assertInstanceOf(JsonApiError::class, $arrayOfObjects[0]);
    }
}
