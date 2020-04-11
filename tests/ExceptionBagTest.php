<?php

namespace WtfPhp\JsonApiErrors\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use WtfPhp\JsonApiErrors\ExceptionBag;
use WtfPhp\JsonApiErrors\Tests\Fakes\TestResponse;

class ExceptionBagTest extends TestCase
{
    protected ExceptionBag $bag;

    public function setUp(): void
    {
        parent::setUp();
        $this->bag = new ExceptionBag();
    }

    /** @test */
    public function itShouldAddOneThrowable()
    {
        $this->assertTrue($this->bag->isEmpty());

        $exception = new Exception('Something went wrong.');
        $this->bag->add($exception);

        $this->assertIsArray($this->bag->getAll());
        $this->assertFalse($this->bag->isEmpty());
        $this->assertCount(1, $this->bag->getAll());
        $this->assertInstanceOf(Exception::class, $this->bag->getAll()[0]);
    }

    /** @test */
    public function itShouldAddMultipleThrowables()
    {
        $this->assertTrue($this->bag->isEmpty());

        $this->bag->addMultiple($this->throwables());

        $this->assertIsArray($this->bag->getAll());
        $this->assertFalse($this->bag->isEmpty());
        $this->assertCount(3, $this->bag->getAll());

        foreach($this->bag->getAll() as $exception) {
            $this->assertInstanceOf(Exception::class, $exception);
        }
    }

    /** @test */
    public function itShouldClearBag()
    {
        $this->assertTrue($this->bag->isEmpty());

        $this->bag->addMultiple($this->throwables());
        $this->assertFalse($this->bag->isEmpty());

        $this->bag->clear();

        $this->assertTrue($this->bag->isEmpty());
    }

    /** @test */
    public function itShouldNotAddNonThrowablesOnMultipleAdditions()
    {
        $this->assertTrue($this->bag->isEmpty());

        $this->bag->addMultiple($this->throwablesAndNonthrowables());

        $this->assertFalse($this->bag->isEmpty());

        $this->assertCount(2, $this->bag->getAll());

        foreach($this->bag->getAll() as $exception) {
            $this->assertInstanceOf(Exception::class, $exception);
        }
    }

    /**
     * @return array|Exception[]
     */
    private function throwables(): array
    {
        return [
            new Exception('Something went wrong.'),
            new Exception('Something went wrong.'),
            new Exception('Something went wrong.')
        ];
    }

    private function throwablesAndNonthrowables(): array
    {
        return [
            new Exception('Something went wrong.'),
            new TestResponse(),
            new Exception('Something went wrong.')
        ];
    }
}
