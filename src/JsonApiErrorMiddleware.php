<?php

namespace WtfPhp\JsonApiErrors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use WtfPhp\JsonApiErrors\Bags\ThrowablesBag;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;

/**
 * Class JsonApiErrorMiddleware
 * @package WtfPhp\JsonApiErrors
 */
class JsonApiErrorMiddleware implements MiddlewareInterface
{
    private JsonApiErrorService $jsonApiErrorService;
    private ?ThrowablesBag $bag;

    public function __construct(JsonApiErrorService $jsonApiErrorService, ?ThrowablesBag $bag = null)
    {
        $this->jsonApiErrorService = $jsonApiErrorService;
        $this->bag = $bag;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $t) {
            // Catch any runtime errors and return them independently
            return $this->jsonApiErrorService->buildResponse($t);
        }

        if (!$this->bag || $this->bag->isEmpty()) {
            return $response;
        }

        // Return bundled custom errors
        return $this->jsonApiErrorService->buildResponseForMultiple($this->bag->getAll());
    }
}
