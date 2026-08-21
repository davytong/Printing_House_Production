<?php

namespace App\Http\Middleware;

use App\Services\RoleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $permission  Permission name to check
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!RoleService::can($permission)) {
            return redirect()->route('dashboard')
                ->with('error', 'អ្នកមិនមានសិទ្ធិចូលប្រើផ្នែកនេះទេ។ / You do not have permission to access this section.');
        }
        
        return $next($request);
    }
}
