<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Allows either a logged-in user with is_admin = true or a session flag 'is_admin' (compat)
     */
    public function handle(Request $request, Closure $next)
    {
        // Prefer Laravel Auth
        if (Auth::check() && isset(Auth::user()->is_admin) && Auth::user()->is_admin) {
            return $next($request);
        }

        // Fallback to session flag for legacy setups
        if ($request->session()->get('is_admin')) {
            return $next($request);
        }

        // Not authorized
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        abort(403);
    }
}
