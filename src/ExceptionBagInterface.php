<?php

namespace WtfPhp\JsonApiErrors;

use Illuminate\Support\Collection;
use Throwable;

interface ExceptionBagInterface
{
    public function add(Throwable $throwable): void;

    public function addMultiple(Collection $throwables): void;

    public function getAll(): Collection;

    public function isEmpty(): bool;

    public function clear(): void;
}
