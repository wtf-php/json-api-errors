<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Error;
use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;
use WtfPhp\JsonApiErrors\JsonApiErrorResponse;
use WtfPhp\JsonApiErrors\ThrowablesBag;

class ThrowablesBagTest extends TestCase
{
    protected ThrowablesBag $bag;

    public function setUp(): void
    {
        parent::setUp();
        $this->bag = new ThrowablesBag();
    }

    /** @test */
    public function itShouldAddOneException()
    {
        $this->assertTrue($this->bag->isEmpty());

        $exception = new Exception('Something went wrong.');
        $this->bag->add($exception);
        $this->assertIsArray($this->bag->getAll());
        $this->assertFalse($this->bag->isEmpty());
        $this->assertCount(1, $this->bag->getAll());
        $this->assertInstanceOf(Throwable::class, $this->bag->getAll()[0]);
    }

    /** @test */
    public function itShouldAddOneError()
    {
        $this->assertTrue($this->bag->isEmpty());

        $error = new Error();
        $this->bag->add($error);
        $this->assertIsArray($this->bag->getAll());
        $this->assertFalse($this->bag->isEmpty());
        $this->assertCount(1, $this->bag->getAll());
        $this->assertInstanceOf(Throwable::class, $this->bag->getAll()[0]);
    }

    /** @test */
    public function itShouldAddMultipleThrowables()
    {
        $this->assertTrue($this->bag->isEmpty());

        $this->bag->addMultiple($this->getThrowables());
        $this->assertIsArray($this->bag->getAll());
        $this->assertFalse($this->bag->isEmpty());
        $this->assertCount(6, $this->bag->getAll());
        foreach ($this->bag->getAll() as $throwable) {
            $this->assertInstanceOf(Throwable::class, $throwable);
        }
    }

    /** @test */
    public function itShouldAddMultipleExceptions()
    {
        $this->assertTrue($this->bag->isEmpty());

        $this->bag->addMultiple($this->getExceptions());
        $this->assertIsArray($this->bag->getAll());
        $this->assertFalse($this->bag->isEmpty());
        $this->assertCount(3, $this->bag->getAll());
        foreach ($this->bag->getAll() as $exception) {
            $this->assertInstanceOf(Throwable::class, $exception);
        }
    }

    /** @test */
    public function itShouldAddMultipleErrors()
    {
        $this->assertTrue($this->bag->isEmpty());

        $this->bag->addMultiple($this->getErrors());
        $this->assertIsArray($this->bag->getAll());
        $this->assertFalse($this->bag->isEmpty());
        $this->assertCount(3, $this->bag->getAll());
        foreach ($this->bag->getAll() as $error) {
            $this->assertInstanceOf(Throwable::class, $error);
        }
    }

    /** @test */
    public function itShouldClearBag()
    {
        $this->assertTrue($this->bag->isEmpty());

        $this->bag->addMultiple($this->getExceptions());
        $this->assertFalse($this->bag->isEmpty());
        $this->bag->clear();
        $this->assertTrue($this->bag->isEmpty());
    }

    /** @test */
    public function itShouldNotAddNonThrowablesOnMultipleAdditions()
    {
        $this->assertTrue($this->bag->isEmpty());

        $this->bag->addMultiple($this->getThrowablesAndNonThrowables());
        $this->assertFalse($this->bag->isEmpty());
        $this->assertCount(3, $this->bag->getAll());
        foreach ($this->bag->getAll() as $throwable) {
            $this->assertInstanceOf(Throwable::class, $throwable);
        }
    }

    /**
     * @return Throwable[]
     */
    private function getThrowables(): array
    {
        return array_merge($this->getExceptions(), $this->getErrors());
    }

    /**
     * @return Exception[]
     */
    private function getExceptions(): array
    {
        return [
            new Exception('Something went wrong.'),
            new Exception('Something went wrong.'),
            new Exception('Something went wrong.'),
        ];
    }

    /**
     * @return Error[]
     */
    private function getErrors(): array
    {
        return [
            new Error(),
            new Error(),
            new Error(),
        ];
    }

    /**
     * @return array
     */
    private function getThrowablesAndNonThrowables(): array
    {
        return [
            new Exception('Something went wrong.'),
            new JsonApiErrorResponse(),
            new Error(),
            new Exception('Something went wrong.'),
        ];
    }
}
