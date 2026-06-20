<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEntry
{
    /**
     * Redirect to entry screen if user hasn't entered their name/position.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for entry routes and API routes
        if ($request->routeIs('entry', 'entry.login', 'entry.logout')) {
            return $next($request);
        }

        // Check if session has user info
        if (! session('user_name') || ! session('user_position')) {
            return redirect()->route('entry');
        }

        return $next($request);
    }
}
