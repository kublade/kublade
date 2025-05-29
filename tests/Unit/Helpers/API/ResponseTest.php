<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers\API;

use App\Helpers\API\Response;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Tests\CreatesApplication;

/**
 * Class ResponseTest.
 *
 * Unit tests for the Response helper class.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ResponseTest extends TestCase
{
    use CreatesApplication;

    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock response factory
        $this->responseFactory = $this->mock(ResponseFactory::class);

        // Bind the mock to the container
        $this->app->instance(ResponseFactory::class, $this->responseFactory);
    }

    /**
     * Test successful response generation.
     */
    public function testSuccessfulResponse(): void
    {
        $expectedResponse = new JsonResponse([
            'status'  => 'success',
            'message' => 'Operation successful',
            'data'    => ['key' => 'value'],
        ], 200);

        $this->responseFactory
            ->shouldReceive('json')
            ->once()
            ->with([
                'status'  => 'success',
                'message' => 'Operation successful',
                'data'    => ['key' => 'value'],
            ], 200)
            ->andReturn($expectedResponse);

        $response = Response::generate(200, 'success', 'Operation successful', ['key' => 'value']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'status'  => 'success',
            'message' => 'Operation successful',
            'data'    => ['key' => 'value'],
        ], $response->getData(true));
    }

    /**
     * Test error response generation.
     */
    public function testErrorResponse(): void
    {
        $expectedResponse = new JsonResponse([
            'status'  => 'error',
            'message' => 'Invalid input',
        ], 400);

        $this->responseFactory
            ->shouldReceive('json')
            ->once()
            ->with([
                'status'  => 'error',
                'message' => 'Invalid input',
            ], 400)
            ->andReturn($expectedResponse);

        $response = Response::generate(400, 'error', 'Invalid input', null);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'status'  => 'error',
            'message' => 'Invalid input',
        ], $response->getData(true));
    }

    /**
     * Test response with exception data.
     */
    public function testResponseWithException(): void
    {
        $exception    = new RuntimeException('Test exception', 123);
        $expectedData = [
            'status'  => 'error',
            'message' => 'Server error',
            'error'   => [
                'message' => 'Test exception',
                'code'    => 123,
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ],
        ];

        $expectedResponse = new JsonResponse($expectedData, 500);

        $this->responseFactory
            ->shouldReceive('json')
            ->once()
            ->with($expectedData, 500)
            ->andReturn($expectedResponse);

        $response = Response::generate(500, 'error', 'Server error', $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Server error', $data['message']);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Test exception', $data['error']['message']);
        $this->assertEquals(123, $data['error']['code']);
        $this->assertArrayHasKey('file', $data['error']);
        $this->assertArrayHasKey('line', $data['error']);
        $this->assertArrayHasKey('trace', $data['error']);
    }

    /**
     * Test response with different status codes.
     *
     * @dataProvider statusCodeProvider
     *
     * @param int    $code
     * @param string $status
     * @param string $message
     */
    public function testDifferentStatusCodes(int $code, string $status, string $message): void
    {
        $expectedData = [
            'status'  => $status,
            'message' => $message,
        ];

        $expectedResponse = new JsonResponse($expectedData, $code);

        $this->responseFactory
            ->shouldReceive('json')
            ->once()
            ->with($expectedData, $code)
            ->andReturn($expectedResponse);

        $response = Response::generate($code, $status, $message);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals($expectedData, $response->getData(true));
    }

    /**
     * Test response with null data.
     */
    public function testResponseWithNullData(): void
    {
        $expectedData = [
            'status'  => 'success',
            'message' => 'No data',
        ];

        $expectedResponse = new JsonResponse($expectedData, 200);

        $this->responseFactory
            ->shouldReceive('json')
            ->once()
            ->with($expectedData, 200)
            ->andReturn($expectedResponse);

        $response = Response::generate(200, 'success', 'No data', null);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedData, $response->getData(true));
    }

    /**
     * Test response with array data.
     */
    public function testResponseWithArrayData(): void
    {
        $data = [
            'user' => [
                'id'    => 1,
                'name'  => 'Test User',
                'email' => 'test@example.com',
            ],
            'meta' => [
                'created_at' => '2024-01-01',
                'updated_at' => '2024-01-02',
            ],
        ];

        $expectedData = [
            'status'  => 'success',
            'message' => 'User data retrieved',
            'data'    => $data,
        ];

        $expectedResponse = new JsonResponse($expectedData, 200);

        $this->responseFactory
            ->shouldReceive('json')
            ->once()
            ->with($expectedData, 200)
            ->andReturn($expectedResponse);

        $response = Response::generate(200, 'success', 'User data retrieved', $data);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedData, $response->getData(true));
    }

    /**
     * Test response with nested exception data.
     */
    public function testResponseWithNestedException(): void
    {
        $innerException = new RuntimeException('Inner exception', 456);
        $outerException = new RuntimeException('Outer exception', 789, $innerException);

        $expectedData = [
            'status'  => 'error',
            'message' => 'Nested exception',
            'error'   => [
                'message' => 'Outer exception',
                'code'    => 789,
                'file'    => $outerException->getFile(),
                'line'    => $outerException->getLine(),
                'trace'   => $outerException->getTraceAsString(),
            ],
        ];

        $expectedResponse = new JsonResponse($expectedData, 500);

        $this->responseFactory
            ->shouldReceive('json')
            ->once()
            ->with($expectedData, 500)
            ->andReturn($expectedResponse);

        $response = Response::generate(500, 'error', 'Nested exception', $outerException);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Nested exception', $data['message']);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Outer exception', $data['error']['message']);
        $this->assertEquals(789, $data['error']['code']);
    }

    /**
     * Data provider for status code tests.
     */
    public static function statusCodeProvider(): array
    {
        return [
            // Success codes / Erfolgscodes
            [200, 'success', 'OK'],
            [201, 'success', 'Created'],
            [202, 'success', 'Accepted'],
            [204, 'success', 'No Content'],

            // Client error codes / Client-Fehlercodes
            [400, 'error', 'Bad Request'],
            [401, 'error', 'Unauthorized'],
            [403, 'error', 'Forbidden'],
            [404, 'error', 'Not Found'],
            [422, 'error', 'Unprocessable Entity'],

            // Server error codes / Server-Fehlercodes
            [500, 'error', 'Internal Server Error'],
            [501, 'error', 'Not Implemented'],
            [502, 'error', 'Bad Gateway'],
            [503, 'error', 'Service Unavailable'],
            [504, 'error', 'Gateway Timeout'],
        ];
    }
}
