<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiErrorMiddleware;
use WtfPhp\JsonApiErrors\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestRequest;

class JsonApiErrorMiddlewareTest extends TestCase
{
    protected ServerRequestInterface $request;
    protected ResponseFactoryInterface $responseFactory;
    protected JsonApiErrorMiddleware $middleware;
    protected JsonApiErrorFactory $jsonApiErrorFactory;
    protected JsonApiErrorResponseSchema $jsonApiErrorResponseSchema;
    protected JsonApiErrorService $jsonApiErrorService;

    protected function setUp(): void
    {
        $this->request = new TestRequest();
        $this->responseFactory = new JsonApiErrorResponseFactory();
        $this->jsonApiErrorFactory = new JsonApiErrorFactory();
        $this->jsonApiErrorResponseSchema = new JsonApiErrorResponseSchema();
        $this->jsonApiErrorService = new JsonApiErrorService(
            $this->jsonApiErrorFactory,
            $this->responseFactory,
            $this->jsonApiErrorResponseSchema
        );
        $this->middleware = new JsonApiErrorMiddleware($this->jsonApiErrorService);
    }

    /** @test */
    public function dummyTest()
    {
        $this->assertTrue(true);
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
        // TODO NOW: Use default error message and default http status code
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());

        // TODO NOW: Change this: Don't do string comparison!
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/exceptionWithoutCodeAndMessage.json',
            $response->getBody()->getContents()
        );
    }

    // TODO NOW Fetzi: Is the expected json-file really correct? Do we really want to have a text in the status - how do we handle this?
    /** @test */
    public function itShouldHandleAnExceptionWithAnInvalidStatusCode()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new Exception('Some error occurred', 600);
            }
        };

        // We set default to 500 if there is no status!
        // If code == http status code then set the code also as status for default exceptions / errors (not for the JsonApiErrorException).

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Some error occurred', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/exceptionWithInvalidCode.json',
            $response->getBody()->getContents()
        );
    }

    // INFO: Test would work if there wouldn't be the `detail`...string comparison...puke...
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
                throw new JsonApiErrorException('A custom json:api error occurred', 0, null, '', 422);
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
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitle()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO NOW: Think about order of params!
                throw new JsonApiErrorException('The entity was not processable', '123', null, '', '422');
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

    // TODO NOW: What should be mapped as detail trace as string?
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetail()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new JsonApiErrorException('The entity was not processable', '123', null, '', '422');
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

    // TODO NOW: Do we event want to implement the whole source->pointer thing?
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetailAndSource()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiErrorException();
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

    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetailAndSourceAndMeta()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiErrorException();
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

    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetailAndSourceAndMetaAndId()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO: add correct instantiation
                throw new JsonApiErrorException();
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
