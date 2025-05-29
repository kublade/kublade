<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\FluxException;
use Exception;
use Tests\TestCase;

/**
 * Class FluxExceptionTest.
 *
 * Unit tests for the FluxException class.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class FluxExceptionTest extends TestCase
{
    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanBeConstructedWithMessageAndCode(): void
    {
        $message   = 'Test flux error';
        $code      = 500;
        $exception = new FluxException($message, $code);

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
        $message   = 'Test flux error';
        $code      = 503;
        $previous  = new Exception('Previous flux error');
        $exception = new FluxException($message, $code, $previous);

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
        $exception = new FluxException('Test flux error', 500);
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
            400 => 'Bad Request - Invalid flux configuration',
            401 => 'Unauthorized - Flux credentials invalid',
            403 => 'Forbidden - Insufficient flux permissions',
            404 => 'Not Found - Flux resource not found',
            409 => 'Conflict - Flux resource already exists',
            422 => 'Unprocessable Entity - Invalid flux manifest',
            500 => 'Internal Server Error - Flux operation failed',
            503 => 'Service Unavailable - Flux service down',
        ];

        foreach ($codes as $code => $message) {
            $exception = new FluxException($message, $code);
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
        $longMessage = str_repeat('Test flux error message with detailed information about the failure ', 50);
        $code        = 500;
        $exception   = new FluxException($longMessage, $code);

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
        $specialMessage = "Test flux error with special chars: \n\r\t!@#$%^&*()_+{}|:\"<>?[]\\;',./~`";
        $code           = 500;
        $exception      = new FluxException($specialMessage, $code);

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
        $innerException  = new Exception('Inner flux error - Git operation failed');
        $middleException = new Exception('Middle flux error - Repository sync failed', 0, $innerException);
        $outerException  = new FluxException('Outer flux error - Deployment failed', 500, $middleException);

        $this->assertEquals('Outer flux error - Deployment failed', $outerException->getMessage());
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
        $message   = 'Test flux error with zero code';
        $code      = 0;
        $exception = new FluxException($message, $code);

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
        $message   = 'Test flux error with negative code';
        $code      = -1;
        $exception = new FluxException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleFluxSpecificErrorMessages(): void
    {
        $fluxErrors = [
            'Git repository not found',
            'Invalid flux manifest format',
            'Flux reconciliation failed',
            'Helm release not found in flux',
            'Flux source not found',
            'Flux kustomization failed',
            'Flux image automation failed',
            'Flux notification failed',
        ];

        foreach ($fluxErrors as $error) {
            $exception = new FluxException($error, 500);
            $this->assertEquals($error, $exception->getMessage());
            $this->assertEquals(500, $exception->getCode());
        }
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleMultiLineErrorMessages(): void
    {
        $multiLineMessage = "Flux error occurred:\n" .
            "- Git repository sync failed\n" .
            "- Helm chart not found\n" .
            "- Kustomization failed\n" .
            '- Image automation error';
        $code      = 500;
        $exception = new FluxException($multiLineMessage, $code);

        $this->assertEquals($multiLineMessage, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
