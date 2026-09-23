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
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'សម័យកាលបានផុតកំណត់ (CSRF Token Mismatch)។ សូមព្យាយាមម្តងទៀត។ / CSRF token mismatch.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            return redirect()->back()
                ->with('error', 'ទំព័របានផុតកំណត់ (Session Expired)។ ទិន្នន័យត្រូវបានរក្សាទុក សូមចុច «រក្សាទុក» ម្តងទៀត។ / Session expired. Please try submitting again.')
                ->withInput();
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'ទំហំរូបភាពធំពេកលើសពីការកំណត់។ / Upload payload is too large.',
                ], 413);
            }

            return redirect()->back()
                ->with('error', 'ទំហំរូបភាពធំពេកលើសពីការកំណត់។ សូមជ្រើសរូបភាពតូចជាងនេះ។ / Upload size is too large.')
                ->withInput();
        });
    })->create();
