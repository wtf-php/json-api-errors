<?php

namespace WtfPhp\JsonApiErrors\Bags;

use Throwable;
use Tightenco\Collect\Support\Collection;

/**
 * Class ThrowablesBag
 * @package WtfPhp\JsonApiErrors
 */
class ThrowablesBag implements ThrowablesBagInterface
{
    protected Collection $bag;

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

        $throwables->each(
            function ($throwable) {
                if ($throwable instanceof Throwable) {
                    $this->bag->add($throwable);
                }
            }
        );
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
