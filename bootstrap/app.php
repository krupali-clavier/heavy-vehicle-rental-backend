<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle API exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Validation failed',
                    'data' => implode(', ', array_values(array_column($e->errors(), '0'))),
                ], 422);
            }
        });

        // Handle JWT exceptions
        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Unauthenticated',
                ], 401);
            }
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Token is invalid',
                ], 401);
            }
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\JWTException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Token error',
                ], 401);
            }
        });

        // Handle Model Not Found exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found',
                ], 404);
            }
        });

        // Handle Authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });

        // Handle Authorization exceptions
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthorized. '.$e->getMessage(),
                ], 403);
            }
        });

        // Handle custom API exceptions
        $exceptions->render(function (\App\Exceptions\ApiException $e, $request) {
            if ($request->is('api/*')) {
                return $e->render();
            }
        });

        // Handle general exceptions for API routes
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e->getMessage() ?: 'An error occurred';

                // Don't expose internal errors in production
                if (! config('app.debug') && $statusCode === 500) {
                    $message = 'Internal server error';
                }

                $response = [
                    'message' => $message,
                ];

                // Include stack trace in debug mode
                if (config('app.debug')) {
                    $response['trace'] = $e->getTraceAsString();
                    $response['file'] = $e->getFile();
                    $response['line'] = $e->getLine();
                }

                return response()->json($response, $statusCode);
            }
        });
    })->create();
