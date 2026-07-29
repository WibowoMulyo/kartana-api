<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            [$code, $message, $errors] = match (true) {
                $e instanceof ValidationException      => [422, 'Validation failed', $e->errors()],
                $e instanceof AuthenticationException  => [401, 'Unauthenticated', null],
                $e instanceof AuthorizationException   => [403, 'You do not have permission to perform this action', null],
                $e instanceof ModelNotFoundException,
                $e instanceof NotFoundHttpException    => [404, 'Resource not found', null],
                default                                => [500, 'Server error',
                                                            config('app.debug') ? $e->getMessage() : null],
            };

            return response()->json([
                'status'  => 'error',
                'message' => $message,
                'errors'  => $errors,
            ], $code);
        });
    })->create();
