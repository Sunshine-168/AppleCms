<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Replace default CSRF middleware to allow project-level exceptions
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );
        $middleware->appendToGroup('web', \App\Http\Middleware\InitializeMaccmsRequest::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\TransformMaccmsHtmlResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
