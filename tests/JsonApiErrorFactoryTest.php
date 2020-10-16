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
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $e = new Exception('foo', 200);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorFactory->createFromThrowable($e));
    }

    /** @test */
    public function itShouldReturnAJsonApiErrorObjectFromJsonErrorException()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(true);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            ['bar' => 'baz'],
            'testlink'
        );
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorFactory->createFromThrowable($jsonException));
    }

    /** @test */
    public function itShouldContainTheErrorCode()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $e = new Exception('bar', 2020);
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals(2020, $jsonApiErrorObject->code);
    }

    /** @test */
    public function itShouldContainTheErrorTitle()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $e = new Exception('bar', 2020);
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($e);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertStringContainsString('bar', $jsonApiErrorObject->title);
    }

    /** @test */
    public function itShouldContainTheErrorLinks()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(true);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
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
    public function itShouldContainTheErrorLinksAndStackTrace()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(true);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);

        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertIsArray($jsonApiErrorObject->links);
        $this->assertArrayHasKey('about', $jsonApiErrorObject->links);
        $this->assertStringContainsString('testlink', $jsonApiErrorObject->links['about']);
        $this->assertStringStartsWith('#0', $jsonApiErrorObject->detail);
    }

    /** @test */
    public function itShouldContainTheErrorLinksStackTraceAndMeta()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(true);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            ['bar' => 'baz'],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);

        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertIsArray($jsonApiErrorObject->links);
        $this->assertArrayHasKey('about', $jsonApiErrorObject->links);
        $this->assertStringContainsString('testlink', $jsonApiErrorObject->links['about']);
        $this->assertStringStartsWith('#0', $jsonApiErrorObject->detail);
        $this->assertArrayHasKey('bar', $jsonApiErrorObject->meta);
    }

    /** @test */
    public function itShouldContainTheHttpStatusCode()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            '504',
            'foo',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals('504', $jsonApiErrorObject->status);
    }

    /** @test */
    public function itShouldContainTheId()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            '504',
            'foo',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals('foo', $jsonApiErrorObject->id);
    }

    /** @test */
    public function itShouldContainTheMetaInfo()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $jsonException = new JsonApiErrorException(
            'a custom message',
            0,
            null,
            'foo',
            '504',
            [],
            'testlink'
        );
        $jsonApiErrorObject = $jsonApiErrorFactory->createFromThrowable($jsonException);
        $this->assertInstanceOf(JsonApiError::class, $jsonApiErrorObject);
        $this->assertEquals([], $jsonApiErrorObject->meta);
    }

    /** @test */
    public function itShouldContainMultipleErrorObjects()
    {
        $jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $exceptions = [
            new Exception('foo', 123),
            new Exception('bar', 1234),
            new Exception('baz', 12345),
            new JsonApiErrorException('a custom message', 0, null, 'foo', '504', ['bar' => 'baz'], 'testlink')
        ];

        $arrayOfObjects = $jsonApiErrorFactory->createFromThrowables($exceptions);

        $this->assertIsArray($arrayOfObjects);
        $this->assertCount(4, $arrayOfObjects);
        $this->assertInstanceOf(JsonApiError::class, $arrayOfObjects[0]);
    }
}
