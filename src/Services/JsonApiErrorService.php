<?php

namespace WtfPhp\JsonApiErrors\Services;

use Lukasoppermann\Httpstatus\Httpstatus;
use Lukasoppermann\Httpstatus\Httpstatuscodes as Status;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorFactory;
use WtfPhp\JsonApiErrors\Factories\JsonApiErrorResponseFactory;
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
    public function buildResponseForSingle(Throwable $t): ResponseInterface
    {
        $jsonApiError = $this->jsonApiErrorFactory->createFromThrowable($t);
        $jsonApiError = $this->jsonApiErrorResponseSchema->getAsJsonApiError($jsonApiError);

        if ($t instanceof JsonApiErrorException) {
            $status = $t->getStatus();
        } else {
            if (empty($t->getCode()) || (!empty($t->getCode()) && !$this->isValidHttpStatusCode($t->getCode()))) {
                $status = Status::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $status = $t->getCode();
            }
        }

        return $this->buildResponse($jsonApiError, $status);
    }

    /**
     * @param array $throwables
     * @return ResponseInterface
     */
    public function buildResponseForMultiple(array $throwables): ResponseInterface
    {
        $jsonErrorObjects = $this->jsonApiErrorFactory->createFromThrowables($throwables);
        $jsonApiErrors = $this->jsonApiErrorResponseSchema->getAsJsonApiErrorList($jsonErrorObjects);

        if (count($throwables) === 1) {
            return $this->buildResponse($jsonApiErrors, $this->getStatusFromThrowable($throwables[0]));
        }

        $statuses = [];
        foreach ($throwables as $t) {
            $statuses[] = $this->getStatusFromThrowable($t);
        }

        if ($this->containsEqualStatuses($statuses)) {
            $status = $statuses[0];
        } else {
            $status = $this->determineStatus($statuses);
        }

        return $this->buildResponse($jsonApiErrors, $status);
    }

    /**
     * @param Throwable $t
     * @return string
     */
    private function getStatusFromThrowable(Throwable $t): string
    {
        if ($t instanceof JsonApiErrorException) {
            $status = $t->getStatus();
        } else {
            if (empty($t->getCode()) || (!empty($t->getCode()) && !$this->isValidHttpStatusCode($t->getCode()))) {
                $status = Status::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $status = $t->getCode();
            }
        }

        return $status;
    }

    /**
     * @param array $statuses
     * @return bool
     */
    private function containsEqualStatuses(array $statuses): bool
    {
        $amountUniqueStatuses = count(array_unique($statuses));
        return ($amountUniqueStatuses === 1);
    }

    /**
     * @param array $statuses
     * @return string
     */
    private function determineStatus(array $statuses): string
    {
        $generalStatus = Status::HTTP_INTERNAL_SERVER_ERROR;

        foreach ($statuses as $status) {
            if ($status >= Status::HTTP_INTERNAL_SERVER_ERROR) {
                break;
            }

            $generalStatus = Status::HTTP_BAD_REQUEST;
            break;
        }

        return $generalStatus;
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

    /**
     * @param string $jsonApiErrors
     * @param string $status
     * @return ResponseInterface
     */
    private function buildResponse(string $jsonApiErrors, string $status): ResponseInterface
    {
        $reasonPhrase = $this->getReasonPhraseForStatusCode($status);

        $response = $this->jsonApiErrorResponseFactory->createResponse($status, $reasonPhrase);
        $response->getBody()->write($jsonApiErrors);

        return $response;
    }
}
