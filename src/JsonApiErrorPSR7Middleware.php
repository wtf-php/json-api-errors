<?php

namespace WtfPhp\JsonApiErrors;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use WtfPhp\JsonApiErrors\Bags\ThrowablesBag;
use WtfPhp\JsonApiErrors\Services\JsonApiErrorService;

class JsonApiErrorPSR7Middleware
{
    private JsonApiErrorService $jsonApiErrorService;
    private ?ThrowablesBag $bag;

    public function __construct(JsonApiErrorService $jsonApiErrorService, ?ThrowablesBag $bag = null)
    {
        $this->jsonApiErrorService = $jsonApiErrorService;
        $this->bag = $bag;
    }

    /**
     * @throws Exception
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        try {
            return $next($request, $response);
        } catch (Throwable $t) {
            // Catch any runtime errors and return them independently
            return $this->jsonApiErrorService->buildResponseForSingle($t);
        }

        if (!$this->bag || $this->bag->isEmpty()) {
            return $next($request, $response);
        }

        // Return bundled custom errors
        return $this->jsonApiErrorService->buildResponseForMultiple($this->bag->getAll());
    }
}
