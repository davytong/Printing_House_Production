<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->session()->get('user_role');
        
        // Check if user has admin role
        if ($role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'អ្នកមិនមានសិទ្ធិចូលប្រើផ្នែកនេះទេ។ / You do not have permission to access this section.');
        }
        
        return $next($request);
    }
}
