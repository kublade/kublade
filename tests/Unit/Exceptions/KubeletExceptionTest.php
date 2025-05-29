<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\KubeletException;
use Exception;
use Tests\TestCase;

/**
 * Class KubeletExceptionTest.
 *
 * Unit tests for the KubeletException class.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class KubeletExceptionTest extends TestCase
{
    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanBeConstructedWithMessageAndCode(): void
    {
        $message   = 'Test kubelet error';
        $code      = 500;
        $exception = new KubeletException($message, $code);

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
        $message   = 'Test kubelet error';
        $code      = 503;
        $previous  = new Exception('Previous kubelet error');
        $exception = new KubeletException($message, $code, $previous);

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
        $exception = new KubeletException('Test kubelet error', 500);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleDifferentErrorCodes(): void
    {
        $codes = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];

        foreach ($codes as $code => $message) {
            $exception = new KubeletException($message, $code);
            $this->assertEquals($code, $exception->getCode(), "Failed for code: {$code}");
            $this->assertEquals($message, $exception->getMessage(), "Failed for code: {$code}");
        }
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleLongErrorMessages(): void
    {
        $longMessage = str_repeat('Test kubelet error message ', 100);
        $code        = 500;
        $exception   = new KubeletException($longMessage, $code);

        $this->assertEquals($longMessage, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleSpecialCharactersInMessage(): void
    {
        $specialMessage = "Test kubelet error with special chars: \n\r\t!@#$%^&*()_+{}|:\"<>?[]\\;',./~`";
        $code           = 500;
        $exception      = new KubeletException($specialMessage, $code);

        $this->assertEquals($specialMessage, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleNestedExceptions(): void
    {
        $innerException  = new Exception('Inner kubelet error');
        $middleException = new Exception('Middle kubelet error', 0, $innerException);
        $outerException  = new KubeletException('Outer kubelet error', 500, $middleException);

        $this->assertEquals('Outer kubelet error', $outerException->getMessage());
        $this->assertEquals(500, $outerException->getCode());
        $this->assertSame($middleException, $outerException->getPrevious());
        $this->assertSame($innerException, $middleException->getPrevious());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleZeroErrorCode(): void
    {
        $message   = 'Test kubelet error with zero code';
        $code      = 0;
        $exception = new KubeletException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleNegativeErrorCode(): void
    {
        $message   = 'Test kubelet error with negative code';
        $code      = -1;
        $exception = new KubeletException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
