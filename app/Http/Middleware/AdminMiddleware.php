<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | User must be logged in
        |--------------------------------------------------------------------------
        */

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | User account must be active
        |--------------------------------------------------------------------------
        */

        if ($user->status !== 'active') {
            auth()->logout();

            return redirect()
                ->route('login')
                ->with('error', 'Your account has been disabled.');
        }

        /*
        |--------------------------------------------------------------------------
        | User must be administrator
        |--------------------------------------------------------------------------
        */

        if (!$user->is_admin) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}