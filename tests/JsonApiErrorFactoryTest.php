<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Models\JsonApiError;

// TODO NEXT: Rework after adding source and detail!
class JsonApiErrorFactoryTest extends TestCase
{
    /** @test */
    public function itShouldReturnAJsonApiErrorObjectFromException()
    {
        $e = new Exception('foo', 200);
        $this->assertInstanceOf(JsonApiError::class, JsonApiErrorFactory::createFromThrowable($e));
    }

    /** @test */
    public function itShouldReturnAJsonApiErrorObjectFromJsonErrorException()
    {
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            ['bar' => 'baz'],
            'testlink'
        );
        $this->assertInstanceOf(JsonApiError::class, JsonApiErrorFactory::createFromThrowable($jsonException));
    }

    /** @test */
    public function itShouldContainTheErrorCode()
    {
        $e = new Exception('bar', 2020);
        $jsonApiErrorObject = JsonApiErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals(2020, $jsonApiErrorObject->code);
    }

    /** @test */
    public function itShouldContainTheErrorTitle()
    {
        $e = new Exception('bar', 2020);
        $jsonApiErrorObject = JsonApiErrorFactory::createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertStringContainsString('bar', $jsonApiErrorObject->title);
    }

    /** @test */
    public function itShouldContainTheErrorLinks()
    {
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            ['bar' => 'baz'],
            'testlink'
        );
        $jsonApiErrorObject = JsonApiErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertIsArray($jsonApiErrorObject->links);
        $this->assertArrayHasKey('about', $jsonApiErrorObject->links);
        $this->assertStringContainsString('testlink', $jsonApiErrorObject->links['about']);
    }

    /** @test */
    public function itShouldContainTheHttpStatusCode()
    {
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            '504',
            'foo',
            ['bar' => 'baz'],
            'testlink'
        );
        $jsonApiErrorObject = JsonApiErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals('504', $jsonApiErrorObject->status);
    }

    /** @test */
    public function itShouldContainTheId()
    {
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            '504',
            'foo',
            ['bar' => 'baz'],
            'testlink'
        );
        $jsonApiErrorObject = JsonApiErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals('foo', $jsonApiErrorObject->id);
    }

    /** @test */
    public function itShouldContainTheMetaInfo()
    {
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            ['bar' => 'baz'],
            'testlink'
        );
        $jsonApiErrorObject = JsonApiErrorFactory::createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals(['bar' => 'baz'], $jsonApiErrorObject->meta);
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
