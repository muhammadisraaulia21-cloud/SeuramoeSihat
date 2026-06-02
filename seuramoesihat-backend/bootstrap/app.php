<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Tidak pakai EnsureFrontendRequestsAreStateful karena auth pakai Bearer token
        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'admin'    => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Kembalikan JSON untuk semua error di API
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof NotFoundHttpException) {
                    return response()->json(['message' => 'Resource tidak ditemukan'], 404);
                }

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json(['message' => 'Tidak terautentikasi'], 401);
                }

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'message' => 'Data tidak valid',
                        'errors'  => $e->errors(),
                    ], 422);
                }

                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json(['message' => 'Data tidak ditemukan'], 404);
                }

                if (config('app.debug')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'trace'   => $e->getTraceAsString(),
                    ], 500);
                }

                return response()->json(['message' => 'Terjadi kesalahan server'], 500);
            }
        });
    })->create();
