<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\HelmException;
use Exception;
use Tests\TestCase;

/**
 * Class HelmExceptionTest.
 *
 * Unit tests for the HelmException class.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class HelmExceptionTest extends TestCase
{
    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanBeConstructedWithMessage(): void
    {
        $message   = 'Test helm error';
        $exception = new HelmException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanBeConstructedWithCustomCode(): void
    {
        $message   = 'Test helm error';
        $code      = 404;
        $exception = new HelmException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanBeConstructedWithPreviousException(): void
    {
        $message   = 'Test helm error';
        $code      = 500;
        $previous  = new Exception('Previous error');
        $exception = new HelmException($message, $code, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itExtendsBaseException(): void
    {
        $exception = new HelmException('Test helm error');
        $this->assertInstanceOf(Exception::class, $exception);
    }
}
