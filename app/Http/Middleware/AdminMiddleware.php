<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ?string $permission = null
    ): Response {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        if (!$admin->isActive()) {
            /*
             * Remove ONLY the disabled administrator authentication.
             *
             * Customer authentication may intentionally coexist in the same
             * Laravel browser session through the separate "web" guard, so
             * the complete session must not be invalidated here.
             */
            Auth::guard('admin')->logout();

            /*
             * Rotate the CSRF token after removing the disabled admin while
             * preserving unrelated session data, including customer login.
             */
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors([
                    'email' => 'This administrator account is currently disabled.',
                ]);
        }

        if (
            filled($permission)
            && !$admin->hasAdminPermission($permission)
        ) {
            abort(
                403,
                'You do not have permission to perform this administrator action.'
            );
        }

        return $next($request);
    }
}
