<?php

namespace WtfPhp\JsonApiErrors\Factories;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponse;

/**
 * Class JsonApiErrorResponseFactory
 * @package WtfPhp\JsonApiErrors
 */
class JsonApiErrorResponseFactory implements ResponseFactoryInterface
{
    /**
     * @param int $status
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function createResponse(int $status = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new JsonApiErrorResponse();
        $response->withStatus($status, $reasonPhrase);
        return $response;
    }
}
