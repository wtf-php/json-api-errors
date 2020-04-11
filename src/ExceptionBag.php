<?php

namespace WtfPhp\JsonApiErrors;

use Throwable;
use Tightenco\Collect\Support\Collection;

/**
 * Class ExceptionBag
 * @package WtfPhp\JsonApiErrors
 */
class ExceptionBag implements ExceptionBagInterface
{
    protected Collection $bag;

    /**
     * ExceptionBag constructor.
     */
    public function __construct()
    {
        $this->bag = new Collection();
    }

    /** @inheritDoc */
    public function add(Throwable $throwable): void
    {
        $this->bag->add($throwable);
    }

    /** @inheritDoc */
    public function addMultiple(array $throwables): void
    {
        $throwables = new Collection($throwables);

        $throwables->each(function ($throwable) {
            if($throwable instanceof Throwable) {
                $this->bag->add($throwable);
            }
        });
    }

    /** @inheritDoc */
    public function getAll(): array
    {
        return $this->bag->toArray();
    }

    /** @inheritDoc */
    public function isEmpty(): bool
    {
        return $this->bag->isEmpty();
    }

    /** @inheritDoc */
    public function clear(): void
    {
        $this->bag = new Collection();
    }
}
