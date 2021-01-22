<?php

namespace WtfPhp\JsonApiErrors\Factories;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonApiErrorResponseFactory
 * @package WtfPhp\JsonApiErrors
 */
class JsonApiErrorResponseFactory implements ResponseFactoryInterface
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @param int $status
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function createResponse(int $status = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $this->response = $this->response->withStatus($status, $reasonPhrase);
        return $this->response;
    }
}
