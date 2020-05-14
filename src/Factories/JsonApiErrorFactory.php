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
        $jsonError->title = !empty($throwable->getMessage()) ? $throwable->getMessage() : 'Internal Server Error';
        // TODO NEXT: Make it configurable if trace should be set or not
        // => Put stack trace in `meta` as array.
        $jsonError->detail = ''; // $throwable->getTraceAsString();

        if ($throwable instanceof JsonApiErrorException) {
            $jsonError->code = $throwable->getCode();
            $jsonError->id = $throwable->getId();
            $jsonError->status = $throwable->getStatus();
            $jsonError->meta = $throwable->getMeta();

            if (!empty($throwable->getAboutLink())) {
                $jsonError->links = [
                    'about' => $throwable->getAboutLink(),
                ];
            }
        } else {
            $jsonError->code = ($throwable->getCode() > 0) ? (string) $throwable->getCode() : '500';
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
