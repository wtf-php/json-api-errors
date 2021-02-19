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
    public function itReturnsAJsonApiErrorObjectFromException()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $e = new Exception('foo', 200);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorFactory->createFromThrowable($e));
    }

    /** @test */
    public function itReturnsAJsonApiErrorObjectFromJsonErrorException()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(true);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            '1',
            ['bar' => 'baz'],
            'testlink'
        );
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorFactory->createFromThrowable($jsonException));
    }

    /** @test */
    public function itContainsTheErrorCode()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $e = new Exception('bar', 2020);
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals(2020, $jsonApiErrorObject->code);
    }

    /** @test */
    public function itContainsTheErrorTitle()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $e = new Exception('bar', 2020);
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertStringContainsString('bar', $jsonApiErrorObject->title);
    }

    /** @test */
    public function itContainsTheErrorLinks()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(true);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            '1',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);

        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertIsArray($jsonApiErrorObject->links);
        $this->assertArrayHasKey('about', $jsonApiErrorObject->links);
        $this->assertStringContainsString('testlink', $jsonApiErrorObject->links['about']);
    }

    /** @test */
    public function itContainsTheErrorLinksAndStackTrace()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(true);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            '1',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);

        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertIsArray($jsonApiErrorObject->links);
        $this->assertArrayHasKey('about', $jsonApiErrorObject->links);
        $this->assertStringContainsString('testlink', $jsonApiErrorObject->links['about']);
        $this->assertEquals('foo', $jsonApiErrorObject->detail);
    }

    /** @test */
    public function itContainsTheErrorLinksStackTraceAndMeta()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(true);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            '1',
            ['bar' => 'baz'],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);

        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertIsArray($jsonApiErrorObject->links);
        $this->assertArrayHasKey('about', $jsonApiErrorObject->links);
        $this->assertStringContainsString('testlink', $jsonApiErrorObject->links['about']);
        $this->assertEquals('foo', $jsonApiErrorObject->detail);
        $this->assertArrayHasKey('bar', $jsonApiErrorObject->meta);
    }

    /** @test */
    public function itContainsTheHttpStatusCode()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            '1',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals('504', $jsonApiErrorObject->status);
    }

    /** @test */
    public function itContainsTheId()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            '1',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals('1', $jsonApiErrorObject->id);
    }

    /** @test */
    public function itContainsTheMetaInfo()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            '1',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals([], $jsonApiErrorObject->meta);
    }

    /** @test */
    public function itContainsMultipleErrorObjects()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $exceptions = [
            new Exception('foo', 123),
            new Exception('bar', 1234),
            new Exception('baz', 12345),
            new JsonApiErrorException('a custom message', 0, null, 'foo', '504', '1', ['bar' => 'baz'], 'testlink')
        ];

        $arrayOfObjects = $jsonApiErrorFactory->createFromThrowables($exceptions);

        $this->assertIsArray($arrayOfObjects);
        $this->assertCount(4, $arrayOfObjects);
        $this->assertInstanceOf(JsonApiError::class, $arrayOfObjects[0]);
    }
}
