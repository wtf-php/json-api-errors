<?php

namespace WtfPhp\JsonApiErrors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;

/**
 * Class JsonApiErrorMiddleware
 *
 * @package WtfPhp\JsonApiErrors
 */
class JsonApiSingleErrorMiddleware implements MiddlewareInterface
{
    private JsonApiErrorService $jsonApiErrorService;

    public function __construct(JsonApiErrorService $jsonApiErrorService)
    {
        $this->jsonApiErrorService = $jsonApiErrorService;
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
            return $handler->handle($request);
        } catch (Throwable $t) {
            return $this->jsonApiErrorService->buildResponse($t);
        }
    }
}
