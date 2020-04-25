<?php

namespace WtfPhp\JsonApiErrors\Services;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\JsonApiErrorResponse;
use WtfPhp\JsonApiErrors\JsonApiErrorResponseSchema;

/**
 * Class JsonApiErrorService
 * @package WtfPhp\JsonApiErrors\Services
 */
class JsonApiErrorService
{
    public const HTTP_STATUS_CODES = [
        '100', '101', '200', '201', '202', '203', '204', '205', '206', '300', '301', '302', '303', '304', '305',
        '306', '307', '400', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412',
        '413', '414', '415', '416', '417', '422', '500', '501', '502', '503', '504', '505',
    ];

    private JsonApiErrorFactory $jsonApiErrorFactory;
    private JsonApiErrorResponseFactory $jsonApiErrorResponseFactory;
    private JsonApiErrorResponseSchema $jsonApiErrorResponseSchema;

    public function __construct(
        JsonApiErrorFactory $jsonApiErrorFactory,
        JsonApiErrorResponseFactory $jsonApiErrorResponseFactory,
        JsonApiErrorResponseSchema $jsonApiErrorResponseSchema
    ) {
        $this->jsonApiErrorFactory = $jsonApiErrorFactory;
        $this->jsonApiErrorResponseFactory = $jsonApiErrorResponseFactory;
        $this->jsonApiErrorResponseSchema = $jsonApiErrorResponseSchema;
    }

    /**
     * @param Throwable $t
     * @return ResponseInterface
     */
    public function buildResponse(Throwable $t): ResponseInterface
    {
        $jsonApiError = $this->jsonApiErrorFactory::createFromThrowable($t);
        $jsonApiErrors = $this->jsonApiErrorResponseSchema->getAsJsonApiError($jsonApiError);

        if ($t instanceof JsonApiErrorException) {
            $status = $t->getStatusCode();
        } else {
            if (empty($t->getStatusCode() ||
                (!empty($t->getStatusCode()) && !$this->isValidHttpStatus($t->getStatusCode())))
            ) {
                $status = 500;
            } else {
                $status = $t->getStatusCode();
            }
        }

        // TODO NEXT WITTI: If status-code is valid then the message should match the code!
        $reasonPhrase = !empty($t->getMessage()) ? $t->getMessage() : 'Internal Server Error';

        /** @var JsonApiErrorResponse $response */
        $response = $this->jsonApiErrorResponseFactory->createResponse($status, $reasonPhrase);
        $response->getBody()->write($jsonApiErrors);
        return $response;
    }

    /**
     * @param string $statusCode
     * @return bool
     */
    private function isValidHttpStatus(string $statusCode): bool
    {
        return in_array($statusCode, self::HTTP_STATUS_CODES);
    }
}
