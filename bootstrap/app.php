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
        $middleware->trustProxies(at: '*');

        if (env('APP_ENV') === 'testing') {
            $middleware->validateCsrfTokens(except: ['*']);
        }

        $middleware->web(append: [
            \App\Http\Middleware\CheckEntry::class,
            \App\Http\Middleware\SetLanguage::class,
        ]);
        
        // Register middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdmin::class,
            'can' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
