<?php

namespace WtfPhp\JsonApiErrors\Bags;

use Throwable;

/**
 * Interface ThrowablesBagInterface
 * @package WtfPhp\JsonApiErrors
 */
interface ThrowablesBagInterface
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
