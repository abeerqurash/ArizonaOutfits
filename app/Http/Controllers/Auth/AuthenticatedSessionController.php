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

        if ($this->isSafeCustomerDestination($redirect)) {
            $request->session()->put('customer.url.intended', $redirect);
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::guard('web')->user();

        if (
            !$user ||
            $user->is_admin ||
            $user->is_super_admin
        ) {
            Auth::guard('web')->logout();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Administrator accounts cannot use customer login.',
            ]);
        }

        if ((string) $user->status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account has been disabled. Please contact support.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->to(
            $this->pullCustomerIntendedDestination($request)
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function pullCustomerIntendedDestination(Request $request): string
    {
        $intended = $request->session()->pull('customer.url.intended');

        if ($this->isSafeCustomerDestination($intended)) {
            return $intended;
        }

        $legacyIntended = $request->session()->get('url.intended');

        if ($this->isSafeCustomerDestination($legacyIntended)) {
            $request->session()->forget('url.intended');

            return $legacyIntended;
        }

        return route('customer.dashboard', absolute: false);
    }

    private function isSafeCustomerDestination(mixed $destination): bool
    {
        if (!is_string($destination) || trim($destination) === '') {
            return false;
        }

        $destination = trim($destination);

        if (str_starts_with($destination, '//')) {
            return false;
        }

        $path = parse_url($destination, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return false;
        }

        if (!str_starts_with($destination, '/')) {
            $scheme = parse_url($destination, PHP_URL_SCHEME);
            $host = parse_url($destination, PHP_URL_HOST);

            if (
                !is_string($scheme) ||
                !in_array(strtolower($scheme), ['http', 'https'], true) ||
                !is_string($host) ||
                strcasecmp($host, request()->getHost()) !== 0
            ) {
                return false;
            }
        }

        $adminLoginPath = parse_url(route('admin.login'), PHP_URL_PATH);

        if (!is_string($adminLoginPath) || $adminLoginPath === '') {
            return false;
        }

        $adminBasePath = preg_replace(
            '#/login/?$#',
            '',
            $adminLoginPath
        );

        if (!is_string($adminBasePath) || $adminBasePath === '') {
            return false;
        }

        $adminBasePath = rtrim($adminBasePath, '/');

        return $path !== $adminBasePath
            && !str_starts_with($path, $adminBasePath . '/');
    }
}
