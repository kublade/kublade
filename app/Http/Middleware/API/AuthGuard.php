<?php

declare(strict_types=1);

namespace App\Http\Middleware\API;

use App\Helpers\API\Response;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (!Auth::guard('api')->check()) {
            return Response::generate(401, 'error', 'Unauthorized');
        }

        Auth::shouldUse('api');

        if ($next($request) instanceof JsonResponse) {
            return $next($request);
        }

        return Response::generate(401, 'error', 'Server Error');
    }
}
