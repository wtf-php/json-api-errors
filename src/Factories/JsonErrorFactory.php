<?php

namespace WtfPhp\JsonApiErrors\Factories;

use Throwable;
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
    public static function createFromThrowable(Throwable $throwable, $status = 500): JsonError
    {
        $jsonError = new JsonError();
        $jsonError->status = $status;
        $jsonError->code = $throwable->getCode();
        $jsonError->title = $throwable->getMessage();
        $jsonError->detail = $throwable->getTraceAsString();
        $jsonError->links = [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine()
        ];

        return $jsonError;
    }

    /**
     * @inheritDoc
     */
    public static function createFromThrowables(array $throwables, $status = 500): array
    {
        $jsonErrorObjects = [];

        foreach ($throwables as $item) {
            $jsonErrorObjects[] = self::createFromThrowable($item, $status);
        }

        return $jsonErrorObjects;
    }
}
