<?php

namespace WtfPhp\JsonApiErrors\Factories;

use Throwable;
use WtfPhp\JsonApiErrors\Models\JsonApiError;

/**
 * Interface JsonApiErrorFactoryInterface
 * @package WtfPhp\JsonApiErrors\Factories
 */
interface JsonApiErrorFactoryInterface
{
    /**
     * @param Throwable $throwable
     * @return JsonApiError
     */
    public static function createFromThrowable(Throwable $throwable): JsonApiError;

    /**
     * @param Throwable[] $throwables
     * @return JsonApiError[]
     */
    public static function createFromThrowables(array $throwables): array;
}
