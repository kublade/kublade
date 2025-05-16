<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\PermissionSet;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Request                      $request
     * @param Closure(Request): (Response) $next
     * @param string                       $permission
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user          = Auth::user();
        $permissionSet = PermissionSet::from($permission, $request);

        if (
            !$user ||
            $permissionSet->every(fn (string $permission) => !$user->can($permission))
        ) {
            return redirect()->route('home')->with('warning', __('Ooops, you don\'t have permission to do that.'));
        }

        return $next($request);
    }
}
