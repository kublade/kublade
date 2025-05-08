<?php

declare(strict_types=1);

namespace App\Helpers\API;

use Illuminate\Http\JsonResponse;
use Throwable;

class Response
{
    /**
     * Generate a response.
     *
     * @param int                  $code
     * @param string               $status
     * @param string               $message
     * @param array|Throwable|null $data
     *
     * @return JsonResponse
     */
    public static function generate(int $code, string $status, string $message, array | Throwable | null $data = null): JsonResponse
    {
        $response = [
            'status'  => $status,
            'message' => $message,
        ];

        if ($data instanceof Throwable) {
            $response['error'] = self::serializeData($data);
        } elseif ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Serialize the data or exception.
     *
     * @param array|Throwable $data
     *
     * @return array
     */
    private static function serializeData(array | Throwable $data): array
    {
        if ($data instanceof Throwable) {
            return [
                'message' => $data->getMessage(),
                'code'    => $data->getCode(),
                'file'    => $data->getFile(),
                'line'    => $data->getLine(),
                'trace'   => $data->getTraceAsString(),
            ];
        }

        return $data;
    }
}
