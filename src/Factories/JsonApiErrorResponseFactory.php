<?php

namespace WtfPhp\JsonApiErrors\Factories;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use WtfPhp\JsonApiErrors\JsonApiErrorResponse;

/**
 * Class JsonApiErrorResponseFactory
 * @package WtfPhp\JsonApiErrors
 */
class JsonApiErrorResponseFactory implements ResponseFactoryInterface
{
    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new JsonApiErrorResponse();
        $response->withStatus($code, $reasonPhrase);
        return $response;
    }
}
