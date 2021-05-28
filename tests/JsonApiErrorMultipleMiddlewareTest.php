<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use Lukasoppermann\Httpstatus\Httpstatus;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use WtfPhp\JsonApiErrors\Bags\ThrowablesBag;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiErrorPSR15Middleware;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestRequest;

class JsonApiErrorMultipleMiddlewareTest extends BaseMiddlewareTest
{
    protected ServerRequestInterface $request;
    protected ResponseFactoryInterface $responseFactory;
    protected JsonApiErrorPSR15Middleware $middleware;
    protected JsonApiErrorFactory $jsonApiErrorFactory;
    protected JsonApiErrorResponseSchema $jsonApiErrorResponseSchema;
    protected JsonApiErrorService $jsonApiErrorService;
    protected Httpstatus $httpStatusHelper;
    protected ThrowablesBag $bag;

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
        $this->bag = new ThrowablesBag();
        $this->middleware = new JsonApiErrorPSR15Middleware($this->jsonApiErrorService, $this->bag);
    }

    /** @test */
    public function itHandlesEmptyException()
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

        $this->assertExpectedWithResponse('exceptions/simpleServerError.json', $response);
    }

    /** @test */
    public function itHandlesExceptionWithInvalidCodeAndMessage()
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

        $this->assertExpectedWithResponse('exceptions/invalidCode.json', $response);
    }

    /** @test */
    public function itHandlesSingleExceptionWithValidCodeAndMessage()
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

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('exceptions/simpleClientError.json', $response);
    }

    /** @test */
    public function itHandlesMultipleExceptionsWithValidCodeAndMessage()
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

        $this->assertExpectedWithResponse('exceptions/statusTitleAndCode.json', $response);
    }

    /** @test */
    public function itHandlesClientAndServerExceptionsWithValidCodeAndMessage()
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

        $this->assertExpectedWithResponse('exceptions/simpleClientAndServerError.json', $response);
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndMessage()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException('A custom json:api error occurred', 0, null, '', '422'));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptions/statusAndTitle.json', $response);
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithMultipleEqualStatusesAndMessages()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException('The entity was not processable', 0, null, '', '422'));
                $this->bag->add(new JsonApiErrorException('The entity was not processable', 0, null, '', '422'));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptions/multipleEqualStatusesAndTitles.json', $response);
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessage()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException('The entity was not processable', '123', null, '', '422'));

                return new Response();
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
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException(
                    'Unprocessable Entity',
                    '123',
                    null,
                    'Details about the error',
                    '422'
                ));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptions/statusTitleCodeAndDetail.json', $response);
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessageAndDetailAndSource()
    {
        $nextHandler = new class($this->bag) implements RequestHandlerInterface {
            public ThrowablesBag $bag;

            public function __construct(ThrowablesBag $bag)
            {
                $this->bag = $bag;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->bag->add(new JsonApiErrorException(
                    'A custom json:api error occurred',
                    '123',
                    null,
                    'Details about the error',
                    '422',
                    '',
                    [],
                    '',
                    ['pointer' => '/data/attributes/first-name']
                ));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse('jsonApiExceptions/statusTitleCodeDetailAndSource.json', $response);
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessageAndDetailAndSourceAndMeta()
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
                $this->bag->add(new JsonApiErrorException(
                    'A custom json:api error occurred',
                    '123',
                    null,
                    'Details about the error',
                    '422',
                    '',
                    [],
                    '',
                    ['pointer' => '/data/attributes/first-name']
                ));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse(
            'jsonApiExceptions/statusTitleCodeDetailSourceAndMetaForMultiple.json',
            $response
        );
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessageAndDetailAndAboutAndMetaAndId()
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
                $this->bag->add(new JsonApiErrorException(
                    'A custom json:api error occurred',
                    '123',
                    null,
                    'Details about the error',
                    '422',
                    '123456',
                    [],
                    'http://example.com',
                ));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse(
            'jsonApiExceptions/statusTitleCodeDetailAboutMetaAndIdForMultiple.json',
            $response
        );
    }

    /** @test */
    public function itHandlesJsonApiExceptionWithStatusAndCodeAndMessageAndDetailAndSourceAndMetaAndId()
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
                $this->bag->add(new JsonApiErrorException(
                    'A custom json:api error occurred',
                    '123',
                    null,
                    'Details about the error',
                    '422',
                    '123456',
                    [],
                    '',
                    ['pointer' => '/data/attributes/first-name']
                ));

                return new Response();
            }
        };

        $response = $this->middleware->process($this->request, $nextHandler);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());

        $this->assertExpectedWithResponse(
            'jsonApiExceptions/statusTitleCodeDetailSourceMetaAndIdForMultiple.json',
            $response
        );
    }
}
