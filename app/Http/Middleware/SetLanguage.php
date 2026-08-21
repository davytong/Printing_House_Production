<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLanguage
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get language from session, default to 'km' (Khmer)
        $locale = session('app_locale', 'km');
        
        // Validate locale
        if (!in_array($locale, ['km', 'en'])) {
            $locale = 'km';
        }
        
        // Set app locale
        App::setLocale($locale);
        
        return $next($request);
    }
}
