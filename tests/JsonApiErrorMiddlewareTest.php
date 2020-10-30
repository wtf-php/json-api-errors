<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use Lukasoppermann\Httpstatus\Httpstatus;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiSingleErrorMiddleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestRequest;

class JsonApiErrorMiddlewareTest extends TestCase
{
    protected ServerRequestInterface $request;
    protected ResponseFactoryInterface $responseFactory;
    protected JsonApiSingleErrorMiddleware $middleware;
    protected JsonApiErrorFactory $jsonApiErrorFactory;
    protected JsonApiErrorResponseSchema $jsonApiErrorResponseSchema;
    protected JsonApiErrorService $jsonApiErrorService;
    protected Httpstatus $httpStatusHelper;

    protected function setUp(): void
    {
        $this->request = new TestRequest();
        $this->responseFactory = new JsonApiErrorResponseFactory();
        $this->jsonApiErrorFactory = new JsonApiErrorFactory();
        $this->jsonApiErrorResponseSchema = new JsonApiErrorResponseSchema();
        $this->httpStatusHelper = new Httpstatus();
        $this->jsonApiErrorService = new JsonApiErrorService(
            $this->jsonApiErrorFactory,
            $this->responseFactory,
            $this->jsonApiErrorResponseSchema,
            $this->httpStatusHelper
        );
        $this->middleware = new JsonApiSingleErrorMiddleware($this->jsonApiErrorService);
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
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptionWithoutCodeAndMessage.json', $response);
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
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptionWithInvalidCode.json', $response);
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
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptionWithValidStatusCodeAndMessage.json', $response);
    }

    /** @test */
    public function itShouldHandleAnExceptionWithValidStatusCodeMessageAndStacktrace()
    {
        $this->setUpWithMode(true);

        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new Exception('The entity was not processable', 422);
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptionWithValidStatusCodeMessageAndStacktrace.json', $response);
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusAndTitle()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new JsonApiErrorException('A custom json:api error occurred', 0, null, '422');
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptionWithStatusAndTitle.json', $response);
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitle()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new JsonApiErrorException('The entity was not processable', '123', null, '422');
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptionWithStatusCodeAndTitle.json', $response);
    }

    // TODO NEXT: Finish this test when detail was implemented properly.
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetail()
    {
        $nextHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new JsonApiErrorException('The entity was not processable', '123', null, '422');
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

    // TODO NEXT: Finish this test when detail and source were implemented properly.
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

    // TODO NEXT: Finish this test when detail was implemented properly.
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

    // TODO NEXT: Finish this test when detail was implemented properly.
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

    /**
     * Currently is only set out for a single error!
     *
     * @param string $expectedDataFile
     * @param ResponseInterface $response
     */
    private function assertExpectedWithResponse(string $expectedDataFile, ResponseInterface $response)
    {
        $expected = $this->decodeJsonFile(__DIR__ . '/expectations/' . $expectedDataFile);
        $actual = json_decode($response->getBody()->getContents(), true);

        $this->assertCount(count($expected['errors']), $actual['errors']);
        $this->assertCount(count($expected['errors'][0]), $actual['errors'][0]);

        foreach ($expected['errors'][0] as $key => $value) {
            if ($key === 'detail') {
                $this->assertGreaterThanOrEqual(strlen($expected['errors'][0][$key]), strlen($actual['errors'][0][$key]));
            } else {
                $this->assertEquals($expected['errors'][0][$key], $actual['errors'][0][$key]);
            }
        }
    }

    /**
     * @param string $path
     * @return array
     */
    private function decodeJsonFile(string $path): array
    {
        $content = file_get_contents($path);
        return json_decode($content, true);
    }

    /**
     * @param bool $debugMode 
     * @return void 
     */
    private function setUpWithMode(bool $debugMode = false): void 
    {
        $this->request = new TestRequest();
        $this->responseFactory = new JsonApiErrorResponseFactory();
        $this->jsonApiErrorFactory = new JsonApiErrorFactory($debugMode);
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
}
