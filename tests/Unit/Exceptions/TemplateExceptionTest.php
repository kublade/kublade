<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\TemplateException;
use Exception;
use Tests\TestCase;

/**
 * Class TemplateExceptionTest.
 *
 * Unit tests for the TemplateException class.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateExceptionTest extends TestCase
{
    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanBeConstructedWithMessageAndCode(): void
    {
        $message   = 'Test template error';
        $code      = 500;
        $exception = new TemplateException($message, $code);

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
        $message   = 'Test template error';
        $code      = 503;
        $previous  = new Exception('Previous template error');
        $exception = new TemplateException($message, $code, $previous);

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
        $exception = new TemplateException('Test template error', 500);
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
            400 => 'Bad Request - Invalid template configuration',
            401 => 'Unauthorized - Template credentials invalid',
            403 => 'Forbidden - Insufficient template permissions',
            404 => 'Not Found - Template resource not found',
            409 => 'Conflict - Template resource already exists',
            422 => 'Unprocessable Entity - Invalid template manifest',
            500 => 'Internal Server Error - Template operation failed',
            503 => 'Service Unavailable - Template service down',
        ];

        foreach ($codes as $code => $message) {
            $exception = new TemplateException($message, $code);
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
        $longMessage = str_repeat('Test template error message with detailed information about the failure ', 50);
        $code        = 500;
        $exception   = new TemplateException($longMessage, $code);

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
        $specialMessage = "Test template error with special chars: \n\r\t!@#$%^&*()_+{}|:\"<>?[]\\;',./~`";
        $code           = 500;
        $exception      = new TemplateException($specialMessage, $code);

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
        $innerException  = new Exception('Inner template error - Git operation failed');
        $middleException = new Exception('Middle template error - Repository sync failed', 0, $innerException);
        $outerException  = new TemplateException('Outer template error - Deployment failed', 500, $middleException);

        $this->assertEquals('Outer template error - Deployment failed', $outerException->getMessage());
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
        $message   = 'Test template error with zero code';
        $code      = 0;
        $exception = new TemplateException($message, $code);

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
        $message   = 'Test template error with negative code';
        $code      = -1;
        $exception = new TemplateException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * @test
     *
     * @group exceptions
     */
    public function itCanHandleTemplateSpecificErrorMessages(): void
    {
        $templateErrors = [
            'Git repository not found',
            'Invalid template manifest format',
            'Template reconciliation failed',
            'Helm release not found in template',
            'Template source not found',
            'Template kustomization failed',
            'Template image automation failed',
            'Template notification failed',
        ];

        foreach ($templateErrors as $error) {
            $exception = new TemplateException($error, 500);
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
        $multiLineMessage = "Template error occurred:\n" .
            "- Git repository sync failed\n" .
            "- Helm chart not found\n" .
            "- Kustomization failed\n" .
            '- Image automation error';
        $code      = 500;
        $exception = new TemplateException($multiLineMessage, $code);

        $this->assertEquals($multiLineMessage, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
