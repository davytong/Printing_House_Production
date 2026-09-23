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

        // APP_URL is deployment configuration. Do not derive it from the Host
        // header: an untrusted Host header can otherwise alter generated URLs.

        // Register Observers for automated syncing
        \App\Models\ProductionTask::observe(\App\Observers\ProductionTaskObserver::class);
    }
}
