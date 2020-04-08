<?php

namespace WtfPhp\JsonApiErrors;

use Illuminate\Support\Collection;
use Throwable;

class ExceptionBag implements ExceptionBagInterface
{
    protected Collection $bag;

    public function __construct()
    {
        $this->bag = new Collection();
    }

    public function add(Throwable $throwable): void
    {
        $this->bag->add($throwable);
    }

    public function addMultiple(Collection $throwables): void
    {
        $throwables->each(function ($throwable) {
            $this->bag->add($throwable);
        });
    }

    public function getAll(): Collection
    {
        return $this->bag;
    }

    public function isEmpty(): bool
    {
        return $this->bag->isEmpty();
    }

    public function clear(): void
    {
        $this->bag = new Collection(); // TODO NEXT: WTF There's no clear method for Collections…
    }
}
