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
        $request->session()->regenerate();
        $intended = $request->session()->get('url.intended');
        if ($this->isSafeAdminDestination($intended)) {
            $request->session()->forget('url.intended');
            return redirect()->to($intended);
        }
        return redirect()->route('admin.dashboard');
    }
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
    private function isSafeAdminDestination(mixed $destination): bool
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
        return $path === $adminBasePath
            || str_starts_with($path, $adminBasePath . '/');
    }
    private function throttleKey(string $email, Request $request): string
    {
        return 'admin-login|' . Str::transliterate($email) . '|' . $request->ip();
    }
}