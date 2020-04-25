<?php

namespace WtfPhp\JsonApiErrors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;

class JsonApiErrorMiddleware implements MiddlewareInterface
{
    private JsonApiErrorService $jsonApiErrorService;

    public function __construct(JsonApiErrorService $jsonApiErrorService)
    {
        $this->jsonApiErrorService = $jsonApiErrorService;
    }

    // INFO: Currently handles only a single Throwable.
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $t) {
            return $this->jsonApiErrorService->buildResponse($t);
        }
    }
}
