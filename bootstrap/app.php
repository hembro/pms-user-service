<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use jeremyaliparo\Foundation\Exceptions\ApiExceptionHandler;
use jeremyaliparo\Foundation\Http\Middleware\CaptureRequestContext;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api([
            CaptureRequestContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        ApiExceptionHandler::register($exceptions);
    })->create();
