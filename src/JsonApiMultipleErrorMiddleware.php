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
class JsonApiMultipleErrorMiddleware implements MiddlewareInterface
{
    private JsonApiErrorService $jsonApiErrorService;
    private ThrowablesBag $bag;

    public function __construct(JsonApiErrorService $jsonApiErrorService, ThrowablesBag $bag)
    {
        $this->jsonApiErrorService = $jsonApiErrorService;
        $this->bag = $bag;
    }

    /**
     * Currently handles only a single Throwable.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $t) {
            dd($t);
            return $this->jsonApiErrorService->buildResponse($t);
        }

        if ($this->bag->isEmpty()) {
            return $response;
        }

        return $this->jsonApiErrorService->buildResponseForMultiple($this->bag->getAll());
    }
}
