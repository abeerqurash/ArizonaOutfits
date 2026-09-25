<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request): View|RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $email = Str::lower(trim($credentials['email']));
        $throttleKey = $this->throttleKey($email, $request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many admin login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $admin = Admin::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (
            !$admin ||
            !filled($admin->password) ||
            !Hash::check($credentials['password'], $admin->password)
        ) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'The provided admin credentials do not match our records.',
            ]);
        }

        if (($admin->status ?? 'active') !== 'active') {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'This administrator account is currently disabled.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('admin')->login(
            $admin,
            $request->boolean('remember')
        );

        /*
         * Regenerate the session ID after authentication to prevent
         * session fixation. Laravel preserves the existing session data,
         * including a simultaneously authenticated customer guard.
         */
        $request->session()->regenerate();

        return redirect()->intended(
            route('admin.dashboard', absolute: false)
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        /*
         * Log out ONLY the administrator guard.
         *
         * Do not invalidate the entire Laravel session here. The customer
         * "web" guard and the administrator "admin" guard can intentionally
         * coexist in the same browser session. Invalidating the whole
         * session would also destroy the customer's authentication state.
         */
        Auth::guard('admin')->logout();

        /*
         * Rotate the CSRF token after the admin logout without destroying
         * unrelated session data such as the customer guard.
         */
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function throttleKey(string $email, Request $request): string
    {
        return 'admin-login|' . Str::transliterate($email) . '|' . $request->ip();
    }
}
