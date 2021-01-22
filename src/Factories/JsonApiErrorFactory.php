<?php

namespace WtfPhp\JsonApiErrors\Factories;

use Throwable;
use WtfPhp\JsonApiErrors\Exceptions\JsonApiErrorException;
use WtfPhp\JsonApiErrors\Models\JsonApiError;

/**
 * Class JsonApiErrorFactory
 * @package WtfPhp\JsonApiErrors\Factories
 */
class JsonApiErrorFactory implements JsonApiErrorFactoryInterface
{
    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * @inheritDoc
     */
    public function createFromThrowable(Throwable $throwable): JsonApiError
    {
        $jsonError = new JsonApiError();
        $jsonError->title = !empty($throwable->getMessage()) ? $throwable->getMessage() : 'Internal Server Error';

        if ($throwable instanceof JsonApiErrorException) {
            $jsonError->code = $throwable->getCode();
            $jsonError->id = $throwable->getId();
            $jsonError->status = $throwable->getStatus();
            $jsonError->detail = $throwable->getDetail();

            if ($this->debug) {
                $jsonError->meta = $throwable->getMeta();

                if (!empty($throwable->getAboutLink())) {
                    $jsonError->links = [
                        'about' => $throwable->getAboutLink(),
                    ];
                }
            }
        } else {
            $jsonError->code = ($throwable->getCode() > 0) ? (string) $throwable->getCode() : '500';
        }

        return $jsonError;
    }

    /**
     * @inheritDoc
     */
    public function createFromThrowables(array $throwables): array
    {
        $jsonErrorObjects = [];

        foreach ($throwables as $item) {
            $jsonErrorObjects[] = $this->createFromThrowable($item);
        }

        return $jsonErrorObjects;
    }
}
