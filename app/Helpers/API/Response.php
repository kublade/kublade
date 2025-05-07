<?php

declare(strict_types=1);

namespace App\Helpers\API;

use Illuminate\Http\JsonResponse;

class Response
{
    /**
     * Generate a response.
     *
     * @param int          $code
     * @param string       $status
     * @param string       $message
     * @param array|object $data
     *
     * @return JsonResponse
     */
    public static function generate(int $code, string $status, string $message, array | object | null $data = null): JsonResponse
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            ...($data ? ['data' => $data] : []),
        ], $code);
    }
}
