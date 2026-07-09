<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\NoCacheHeaders::class,
        ]);

        $middleware->alias([
            'profile.complete' => \App\Http\Middleware\EnsureProfileIsComplete::class,
            'role.seller' => \App\Http\Middleware\EnsureUserIsSeller::class,
            'role.driver' => \App\Http\Middleware\IsDriver::class,
            'role.buyer' => \App\Http\Middleware\RedirectIfNotBuyer::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
