<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyerAuth
{
    /**
     * Handle an incoming request - ensure user is authenticated via Supabase
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login',
                ], 401);
            }

            return redirect()->route('buyer.login');
        }

        return $next($request);
    }
}
