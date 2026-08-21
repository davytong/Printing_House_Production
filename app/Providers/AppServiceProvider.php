<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

// Define CURLOPT_RESOLVE if cURL extension is not loaded (prevents crash on new environments)
if (!defined('CURLOPT_RESOLVE')) {
    define('CURLOPT_RESOLVE', 10203);
}

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination markup (this app uses Bootstrap, not Tailwind).
        // Without this, the default Tailwind paginator renders giant un-styled SVG arrows.
        Paginator::useBootstrapFive();

        // Dynamically override APP_URL based on how the server is accessed
        if (isset($_SERVER['HTTP_HOST'])) {
            $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                       (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            
            $proto = $isHttps ? 'https' : 'http';
            config(['app.url' => $proto . '://' . $_SERVER['HTTP_HOST']]);
            
            if ($isHttps) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        // Register Observers for automated syncing
        \App\Models\ProductionTask::observe(\App\Observers\ProductionTaskObserver::class);
    }
}
