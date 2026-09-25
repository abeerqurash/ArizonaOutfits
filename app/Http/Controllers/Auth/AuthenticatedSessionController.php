<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        $redirect = $request->query('redirect');

        if (
            is_string($redirect)
            && str_starts_with($redirect, '/')
            && !str_starts_with($redirect, '//')
        ) {
            $request->session()->put('url.intended', $redirect);
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if ((string) $request->user()->status !== 'active') {
            Auth::guard('web')->logout();

            // Preserve any independently authenticated admin guard.
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account has been disabled. Please contact support.',
            ]);
        }

        // Prevent session fixation while preserving the other guard's data.
        $request->session()->regenerate();

        // Administrators authenticate only through the dedicated admin guard.
        return redirect()->intended(
            route('dashboard', absolute: false)
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        /*
         * Log out ONLY the customer guard. Do not invalidate the complete
         * Laravel session because an administrator can be authenticated
         * simultaneously through the separate "admin" guard.
         */
        Auth::guard('web')->logout();

        // Rotate CSRF protection without destroying the admin authentication.
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
