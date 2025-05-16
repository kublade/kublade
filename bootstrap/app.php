<?php

declare(strict_types=1);

use App\Http\Middleware\API\AuthGuard as ApiAuthGuard;
use App\Http\Middleware\API\PermissionGuard as ApiPermissionGuard;
use App\Http\Middleware\PermissionGuard as UiPermissionGuard;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'api.guard'            => ApiAuthGuard::class,
            'api.permission.guard' => ApiPermissionGuard::class,
            'ui.permission.guard'  => UiPermissionGuard::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
