<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Log who is trying to access the route
        if ($user) {
            Log::info('AdminMiddleware: User ID ' . $user->id . ' - is_admin: ' . ($user->is_admin ? 'yes' : 'no'));
        } else {
            Log::warning('AdminMiddleware: Guest user tried to access admin route.');
        }

        // Block access if not authenticated or not admin
        if (!$user || !$user->is_admin) {
            Log::warning('AdminMiddleware: Access denied.');
            abort(403, 'Unauthorized');
        }

        // Allow request to proceed
        Log::info('AdminMiddleware: Access granted.');
        return $next($request);
    }

}
