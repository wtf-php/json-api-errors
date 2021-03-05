<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use Lukasoppermann\Httpstatus\Httpstatus;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiErrorMiddleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestRequest;

class JsonApiErrorSingleMiddlewareTest extends BaseMiddlewareTest
{
    protected ServerRequestInterface $request;
    protected ResponseFactoryInterface $responseFactory;
    protected JsonApiErrorMiddleware $middleware;
    protected JsonApiErrorFactory $jsonApiErrorFactory;
    protected JsonApiErrorResponseSchema $jsonApiErrorResponseSchema;
    protected JsonApiErrorService $jsonApiErrorService;
    protected Httpstatus $httpStatusHelper;

    protected function setUp(): void
    {
        $this->request = new TestRequest();
        $this->responseFactory = new JsonApiErrorResponseFactory(new Response());
        $this->jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $this->jsonApiErrorResponseSchema = new JsonApiErrorResponseSchema();
        $this->httpStatusHelper = new Httpstatus();
        $this->jsonApiErrorService = new JsonApiErrorService(
            $this->jsonApiErrorFactory,
            $this->responseFactory,
            $this->jsonApiErrorResponseSchema,
            $this->httpStatusHelper
        );
        $this->middleware = new JsonApiErrorMiddleware($this->jsonApiErrorService);
    }

    /** @test */
    public function itHandlesAnEmptyException()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new Exception();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptions/simpleServerError.json', $response);
    }

    /** @test */
    public function itHandlesAnExceptionWithInvalidCodeAndMessage()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new Exception('Some error occurred', 600);
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptions/invalidCode.json', $response);
    }

    /** @test */
    public function itHandlesAnExceptionWithValidCodeAndMessage()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new Exception('The entity was not processable', 422);
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptions/simpleClientError.json', $response);
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndMessage()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new JsonApiErrorException('A custom json:api error occurred', 0, null, '', '422');
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptions/statusAndTitle.json', $response);
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessage()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new JsonApiErrorException('The entity was not processable', '123', null, '', '422');
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptions/statusTitleAndCode.json', $response);
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessageAndDetail()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new JsonApiErrorException(
                    'Unprocessable Entity',
                    '123',
                    null,
                    'Details about the error',
                    '422',
                );
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptions/statusTitleCodeAndDetail.json', $response);
    }

    // TODO NEXT: Finish this test when detail and source were implemented properly.
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessageAndDetailAndSource()
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

    // TODO NEXT: Finish this test when detail was implemented properly.
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessageAndDetailAndSourceAndMeta()
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

    // TODO NEXT: Finish this test when detail was implemented properly.
    public function itSHandlesJsonApiExceptionWithStatusAndCodeAndMessageAndDetailAndSourceAndMetaAndId()
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
