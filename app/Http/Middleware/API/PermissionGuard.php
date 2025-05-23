<?php

declare(strict_types=1);

namespace App\Http\Middleware\API;

use App\Helpers\API\Response;
use App\Helpers\PermissionSet;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class PermissionGuard.
 *
 * This class is the middleware for checking the permission.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class PermissionGuard
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string  $permission
     *
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next, string $permission): JsonResponse
    {
        $user          = Auth::user();
        $permissionSet = PermissionSet::from($permission, $request);

        if (
            !$user ||
            $permissionSet->every(function (string $permission) use ($user) {
                return !$user->can($permission);
            })
        ) {
            return Response::generate(401, 'error', 'Unauthorized');
        }

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            return $response;
        }

        return Response::generate(500, 'error', 'Server Error');
    }
}
