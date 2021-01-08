<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use Lukasoppermann\Httpstatus\Httpstatus;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use WtfPhp\JsonApiErrors\Bags\ThrowablesBag;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiErrorMiddleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestRequest;

class JsonApiErrorMultipleMiddlewareTest extends TestCase
{
    protected ServerRequestInterface $request;
    protected ResponseFactoryInterface $responseFactory;
    protected JsonApiErrorMiddleware $middleware;
    protected JsonApiErrorFactory $jsonApiErrorFactory;
    protected JsonApiErrorResponseSchema $jsonApiErrorResponseSchema;
    protected JsonApiErrorService $jsonApiErrorService;
    protected Httpstatus $httpStatusHelper;
    protected ThrowablesBag $bag;

    protected function setUp(): void
    {
        $this->request = new TestRequest();
        $this->responseFactory = new JsonApiErrorResponseFactory();
        $this->jsonApiErrorFactory = new JsonApiErrorFactory(false);
        $this->jsonApiErrorResponseSchema = new JsonApiErrorResponseSchema();
        $this->httpStatusHelper = new Httpstatus();
        $this->jsonApiErrorService = new JsonApiErrorService(
            $this->jsonApiErrorFactory,
            $this->responseFactory,
            $this->jsonApiErrorResponseSchema,
            $this->httpStatusHelper
        );
        $this->bag = new ThrowablesBag();
        $this->middleware = new JsonApiErrorMiddleware($this->jsonApiErrorService, $this->bag);
    }

    /** @test */
    public function itShouldHandleAnExceptionWithoutErrorCodeAndMessage()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new Exception());

                return new Response();
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
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new Exception('Some error occurred', 600));

                return new Response();
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
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new Exception('The entity was not processable', 422));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptionWithValidStatusCodeAndMessage.json', $response);
    }

    /** @test */
    public function itShouldHandleExceptionsWithValidStatusCodeAndMessage()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->addMultiple([
                    new Exception('The entity was not processable', 422),
                    new Exception('Validation error', 403),
                ]);

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptionsWithValidStatusCodeAndMessage.json', $response);
    }

    /** @test */
    public function itShouldHandleClientAndServerExceptionsWithValidStatusCodeAndMessage()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->addMultiple([
                    new Exception('Some server error occurred', 500),
                    new Exception('Validation error', 403),
                ]);

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('clientAndServerExceptionsWithValidStatusCodeAndMessage.json', $response);
    }

    /** @test */
    public function itShouldHandleAnExceptionWithValidStatusCodeMessageAndStacktrace()
    {
        $this->setUpWithMode(true);

        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new Exception('The entity was not processable', 422));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptionWithValidStatusCodeMessageAndStacktrace.json', $response);
    }

    /** @test */
    public function itShouldHandleExceptionsWithValidStatusCodeMessageAndStacktrace()
    {
        $this->setUpWithMode(true);

        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->addMultiple([
                    new Exception('The entity was not processable', 422),
                    new Exception('Validation error', 403),
                ]);

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptionsWithValidStatusCodeMessageAndStacktrace.json', $response);
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusAndTitle()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException('A custom json:api error occurred', 0, null, '422'));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptionWithStatusAndTitle.json', $response);
    }

    /** @test */
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitle()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException('The entity was not processable', '123', null, '422'));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptionWithStatusCodeAndTitle.json', $response);
    }

    // TODO NEXT: Finish this test when detail was implemented properly.
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetail()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException('The entity was not processable', '123', null, '422'));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusCodeTitleAndDetail.json',
            $response->getBody()->getContents()
        );
    }

    // TODO NEXT: Finish this test when detail and source were implemented properly.
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetailAndSource()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException());

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusCodeTitleDetailAndSource.json',
            $response->getBody()->getContents()
        );
    }

    // TODO NEXT: Finish this test when detail was implemented properly.
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetailAndSourceAndMeta()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO NOW: Add correct instantiation
                $this->bag->add(new JsonApiErrorException());

                return new Response();
            }
        };
        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/expectations/jsonApiExceptionWithStatusCodeTitleDetailSourceAndMeta.json',
            $response->getBody()->getContents()
        );
    }

    // TODO NEXT: Finish this test when detail was implemented properly.
    public function itShouldHandleAJsonApiExceptionWithStatusAndCodeAndTitleAndDetailAndSourceAndMetaAndId()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // TODO NOW: Add correct instantiation
                $this->bag->add(new JsonApiErrorException());

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());
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
        // Needed as the body is a stream and the cursor needs to be set back to the start
        $response->getBody()->rewind();
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
        $this->middleware = new JsonApiErrorMiddleware($this->jsonApiErrorService, $this->bag);
    }
}
