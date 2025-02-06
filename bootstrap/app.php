<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias middleware untuk penggunaan di route
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'mac.restrict' => \App\Http\Middleware\MacAddressMiddleware::class,
        ]);

        // Tangani redirect untuk guest pada API routes
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, Request $request) {
            // Tangani UnauthenticatedException
            if ($e instanceof AuthenticationException) {
                if ($request->is('api/*')) {
                    return response()->json(['message' => 'Unauthenticated'], 401);
                }

                // Redirect untuk non-API
                return redirect()->guest(route('filament.app.auth.login'));
            }

            // Tangani ModelNotFoundException
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                if ($request->is('api/*')) {
                    return response()->json(['message' => 'Resource not found'], 404);
                }
            }

            // Tangani HttpException
            if ($e instanceof HttpException) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            // Default: Tangani exception lainnya
            if ($request->is('api/*')) {
                return response()->json(['message' => 'An unexpected error occurred'], 500);
            }
        });
    })
    ->create();
