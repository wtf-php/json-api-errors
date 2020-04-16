<?php

namespace WtfPhp\JsonApiErrors\Factory;

use Throwable;
use WtfPhp\JsonApiErrors\Models\JsonError;

/**
 * Class JsonErrorFactory
 * @package WtfPhp\JsonApiErrors\Factory
 */
class JsonErrorFactory implements JsonErrorFactoryInterface
{
    /**
     * @inheritDoc
     */
    public static function createFromThrowable(Throwable $throwable): JsonError
    {
        // TODO: Implement createFromThrowable() method.
    }

    /**
     * @inheritDoc
     */
    public static function createFromThrowables(array $throwables): array
    {
        // TODO: Implement createFromMultipleThrowables() method.
    }
}
