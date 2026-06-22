<?php

use App\Domain\Exceptions\IdempotencyViolation;
use App\Domain\Exceptions\InsufficientStock;
use App\Domain\Exceptions\InvalidStatusTransition;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        App\Providers\AppServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (IdempotencyViolation $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        });
        $exceptions->renderable(function (InvalidStatusTransition $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        });
        $exceptions->renderable(function (InsufficientStock $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        });
    })->create();
