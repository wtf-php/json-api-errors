<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WtfPhp\JsonApiErrors\JsonApiErrorMiddleware;
use WtfPhp\JsonApiErrors\JsonApiException;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestRequest;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestResponseFactory;

class JsonApiErrorMiddlewareTest extends TestCase
{
    protected ServerRequestInterface $request;

    protected ResponseFactoryInterface $responseFactory;

    protected JsonApiErrorMiddleware $middleware;

    protected function setUp(): void
    {
        $this->request = new TestRequest();
        $this->responseFactory = new TestResponseFactory();
        $this->middleware = new JsonApiErrorMiddleware($this->responseFactory);
    }

    /** @test */
    public function itShouldHandleAnExceptionWithoutErrorCodeAndMessage()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new Exception();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('An exception occurred', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/exceptionWithoutCodeAndMessage.json',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function itShouldHandleAnExceptionWithAnInvalidStatusCode()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new Exception('Some error occurred', 600);
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Some error occurred', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/exceptionWithInvalidCode.json',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function itShouldHandleAnExceptionWithValidStatusCodeAndMessage()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new Exception('The entity was not processable', 422);
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('The entity was not processable', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/exceptionWithValidStatusCodeAndMessage.json',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusAndTitle()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiException();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('A custom json:api error occurred', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusAndTitle.json',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusCodeAndTitle()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiException();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('The entity was not processable', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusCodeAndTitle.json',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusCodeTitleAndDetail()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiException();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('The entity was not processable', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusCodeTitleAndDetail.json',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusCodeTitleDetailAndSource()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiException();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('The entity was not processable', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusCodeTitleDetailAndSource.json',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusCodeTitleDetailSourceAndMeta()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiException();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('The entity was not processable', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusCodeTitleDetailSourceAndMeta.json',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusCodeTitleDetailSourceMetaAndId()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiException();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('The entity was not processable', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusCodeTitleDetailSourceMetaAndId.json',
            $response->getBody()->getContents()
        );
    }
}
