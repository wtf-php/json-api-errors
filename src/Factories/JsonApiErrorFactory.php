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
    /**
     * @inheritDoc
     */
    public static function createFromThrowable(Throwable $throwable): JsonApiError
    {
        $jsonError = new JsonApiError();
        $jsonError->code = (string) $throwable->getCode();
        $jsonError->title = $throwable->getMessage();
        // TODO NEXT: Make it configurable if trace should be set or not
        $jsonError->detail = $throwable->getTraceAsString();

        if ($throwable instanceof JsonApiErrorException) {
            $jsonError->id = $throwable->getId();
            $jsonError->status = $throwable->getStatusCode();
            $jsonError->meta = $throwable->getMeta();
            $jsonError->links = [
                'about' => $throwable->getAboutLink(),
            ];
        }

        return $jsonError;
    }

    /**
     * @inheritDoc
     */
    public static function createFromThrowables(array $throwables): array
    {
        $jsonErrorObjects = [];

        foreach ($throwables as $item) {
            $jsonErrorObjects[] = self::createFromThrowable($item);
        }

        return $jsonErrorObjects;
    }
}
