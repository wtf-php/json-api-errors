<?php

namespace WtfPhp\JsonApiErrors\Factories;

use Throwable;
use WtfPhp\JsonApiErrors\Exceptions\JsonErrorException;
use WtfPhp\JsonApiErrors\Models\JsonError;

/**
 * Class JsonErrorFactory
 * @package WtfPhp\JsonApiErrors\Factories
 */
class JsonErrorFactory implements JsonErrorFactoryInterface
{
    /**
     * @inheritDoc
     */
    public static function createFromThrowable(Throwable $throwable, int $status = 500): JsonError
    {
        $jsonError = new JsonError();
        $jsonError->code = $throwable->getCode();
        $jsonError->title = $throwable->getMessage();
        $jsonError->detail = $throwable->getTraceAsString();
        $jsonError->links = [
            'about' => sprintf('%s at lint %s', $throwable->getFile(), $throwable->getLine())
        ];

        if ($throwable instanceof JsonErrorException) {
            $jsonError->id = $throwable->getId() ?? '';
            $jsonError->status = $throwable->getStatusCode() ?? $status;
            $jsonError->meta = $throwable->getMeta() ?? [];
        }

        return $jsonError;
    }

    /**
     * @inheritDoc
     */
    public static function createFromThrowables(array $throwables, int $status = 500): array
    {
        $jsonErrorObjects = [];

        foreach ($throwables as $item) {
            $jsonErrorObjects[] = self::createFromThrowable($item, $status);
        }

        return $jsonErrorObjects;
    }
}
