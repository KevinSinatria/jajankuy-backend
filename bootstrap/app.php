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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'login.auth' => \App\Http\Middleware\LoginMiddleware::class,
            'middleware.auth' => \App\Http\Middleware\AuthMiddleware::class,
            'middleware.admin' => \App\Http\Middleware\AdminMiddleware::class,
            'middleware.customer' => \App\Http\Middleware\CustomerMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
