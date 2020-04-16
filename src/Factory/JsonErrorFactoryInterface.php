<?php

namespace WtfPhp\JsonApiErrors\Factory;

use Throwable;
use WtfPhp\JsonApiErrors\Models\JsonError;

/**
 * Interface JsonErrorFactoryInterface
 * @package WtfPhp\JsonApiErrors\Factory
 */
interface JsonErrorFactoryInterface
{
    /**
     * @param Throwable $throwable
     * @return JsonError
     */
    public static function createFromThrowable(Throwable $throwable): JsonError;

    /**
     * @param Throwable[] $throwables
     * @return JsonError[]
     */
    public static function createFromThrowables(array $throwables): array;
}
