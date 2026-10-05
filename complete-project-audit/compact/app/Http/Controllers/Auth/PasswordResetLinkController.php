<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\CustomerEmailIdentity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);
        $normalizedEmail = CustomerEmailIdentity::normalizeEmail(
            (string) $validated['email']
        );
        $genericMessage =
            'If an eligible Arizona Outfits account exists for that email address, password reset instructions have been sent.';
        if ($normalizedEmail === null) {
            return back()->with('status', $genericMessage);
        }
        $identity = CustomerEmailIdentity::query()
            ->with('user')
            ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
            ->where('normalized_email', $normalizedEmail)
            ->whereNull('disconnected_at')
            ->whereNotNull('email_verified_at')
            ->where('is_login_enabled', true)
            ->first();
        $user = $identity?->user;
        if (
            ! $user instanceof User
            || $user->status !== 'active'
            || ! $user->hasPassword()
            || ! $user->hasEmailLogin()
        ) {
            return back()->with('status', $genericMessage);
        }
        $status = Password::broker()->sendResetLink([
            'email' => (string) $user->email,
        ]);
        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput(['email' => Str::lower(trim($validated['email']))])
                ->withErrors(['email' => __($status)]);
        }
        return back()->with('status', $genericMessage);
    }
}