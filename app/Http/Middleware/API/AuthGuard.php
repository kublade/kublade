<?php

declare(strict_types=1);

namespace App\Http\Middleware\API;

use App\Helpers\API\Response;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthGuard.
 *
 * This class is the middleware for the authentication guard.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class AuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return JsonResponse
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

        return Response::generate(500, 'error', 'Server Error');
    }
}
