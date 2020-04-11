<?php

namespace WtfPhp\JsonApiErrors;

use Throwable;

/**
 * Interface ExceptionBagInterface
 * @package WtfPhp\JsonApiErrors
 */
interface ExceptionBagInterface
{
    /**
     * @param Throwable $throwable
     * @return void
     */
    public function add(Throwable $throwable): void;

    /**
     * @param array $throwables
     * @return void
     */
    public function addMultiple(array $throwables): void;

    /**
     * @return array
     */
    public function getAll(): array;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return void
     */
    public function clear(): void;
}
