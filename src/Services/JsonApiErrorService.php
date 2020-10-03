<?php

namespace WtfPhp\JsonApiErrors\Services;

use Lukasoppermann\Httpstatus\Httpstatus;
use Lukasoppermann\Httpstatus\Httpstatuscodes as Status;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponse;
use WtfPhp\JsonApiErrors\Responses\JsonApiErrorResponseSchema;

/**
 * Class JsonApiErrorService
 * @package WtfPhp\JsonApiErrors\Services
 */
class JsonApiErrorService
{
    private JsonApiErrorFactory $jsonApiErrorFactory;
    private JsonApiErrorResponseFactory $jsonApiErrorResponseFactory;
    private JsonApiErrorResponseSchema $jsonApiErrorResponseSchema;
    private Httpstatus $httpStatusHelper;

    public function __construct(
        JsonApiErrorFactory $jsonApiErrorFactory,
        JsonApiErrorResponseFactory $jsonApiErrorResponseFactory,
        JsonApiErrorResponseSchema $jsonApiErrorResponseSchema,
        Httpstatus $httpStatusHelper
    ) {
        $this->jsonApiErrorFactory = $jsonApiErrorFactory;
        $this->jsonApiErrorResponseFactory = $jsonApiErrorResponseFactory;
        $this->jsonApiErrorResponseSchema = $jsonApiErrorResponseSchema;
        $this->httpStatusHelper = $httpStatusHelper;
    }

    /**
     * @param Throwable $t
     * @return ResponseInterface
     */
    public function buildResponse(Throwable $t): ResponseInterface
    {
        $jsonApiError = $this->jsonApiErrorFactory->createFromThrowable($t);
        $jsonApiErrors = $this->jsonApiErrorResponseSchema->getAsJsonApiError($jsonApiError);

        if ($t instanceof JsonApiErrorException) {
            $status = $t->getStatus();
        } else {
            if (empty($t->getCode()) || (!empty($t->getCode()) && !$this->isValidHttpStatusCode($t->getCode()))) {
                $status = Status::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $status = $t->getCode();
            }
        }

        $reasonPhrase = $this->getReasonPhraseForStatusCode($status);

        /** @var JsonApiErrorResponse $response */
        $response = $this->jsonApiErrorResponseFactory->createResponse($status, $reasonPhrase);
        $response->getBody()->write($jsonApiErrors);
        return $response;
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    private function isValidHttpStatusCode(int $statusCode): bool
    {
        return $this->httpStatusHelper->hasStatusCode($statusCode);
    }

    /**
     * @param int $statusCode
     * @return string
     */
    private function getReasonPhraseForStatusCode(int $statusCode): string
    {
        return $this->httpStatusHelper->getReasonPhrase($statusCode);
    }
}
