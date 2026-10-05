<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\CustomerEmailIdentity;
use App\Models\User;
use App\Services\PasswordSecurityService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class NewPasswordController extends Controller
{
    public function create(
        Request $request,
        string $token
    ): View|RedirectResponse {
        $normalizedEmail = CustomerEmailIdentity::normalizeEmail(
            (string) $request->query('email', '')
        );
        $identity = $normalizedEmail === null
            ? null
            : CustomerEmailIdentity::query()
                ->with('user')
                ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
                ->where('normalized_email', $normalizedEmail)
                ->whereNull('disconnected_at')
                ->whereNotNull('email_verified_at')
                ->where('is_login_enabled', true)
                ->first();
        $eligibleUser = $identity?->user;
        $tokenIsValid =
            $eligibleUser instanceof User
            && $eligibleUser->status === 'active'
            && $eligibleUser->hasPassword()
            && $eligibleUser->hasEmailLogin()
            && Password::broker()->tokenExists(
                $eligibleUser,
                $token
            );
        if (! $tokenIsValid) {
            return redirect()
                ->route('login')
                ->with(
                    'social_error',
                    'This password reset link is invalid or is no longer available.'
                );
        }
        return view('auth.reset-password', [
            'request' => $request,
        ]);
    }
    public function store(
        Request $request,
        PasswordSecurityService $passwordSecurity
    ): RedirectResponse {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $normalizedEmail = CustomerEmailIdentity::normalizeEmail(
            (string) $validated['email']
        );
        $identity = $normalizedEmail === null
            ? null
            : CustomerEmailIdentity::query()
                ->with('user')
                ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
                ->where('normalized_email', $normalizedEmail)
                ->whereNull('disconnected_at')
                ->whereNotNull('email_verified_at')
                ->where('is_login_enabled', true)
                ->first();
        $eligibleUser = $identity?->user;
        if (
            ! $eligibleUser instanceof User
            || $eligibleUser->status !== 'active'
            || ! $eligibleUser->hasPassword()
            || ! $eligibleUser->hasEmailLogin()
        ) {
            throw ValidationException::withMessages([
                'email' =>
                    'This password reset link is invalid or is no longer available.',
            ]);
        }
        $status = Password::broker()->reset(
            [
                'email' => (string) $eligibleUser->email,
                'password' => (string) $validated['password'],
                'password_confirmation' =>
                    (string) $request->input('password_confirmation'),
                'token' => (string) $validated['token'],
            ],
            function (User $user) use (
                $eligibleUser,
                $validated,
                $passwordSecurity
            ) {
                if ($user->getKey() !== $eligibleUser->getKey()) {
                    throw ValidationException::withMessages([
                        'email' =>
                            'This password reset link is invalid or is no longer available.',
                    ]);
                }
                $stillEligible = CustomerEmailIdentity::query()
                    ->where('user_id', $user->id)
                    ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
                    ->where(
                        'normalized_email',
                        CustomerEmailIdentity::normalizeEmail($user->email)
                    )
                    ->whereNull('disconnected_at')
                    ->whereNotNull('email_verified_at')
                    ->where('is_login_enabled', true)
                    ->exists();
                if (
                    ! $stillEligible
                    || $user->status !== 'active'
                    || ! $user->hasPassword()
                    || ! $user->hasEmailLogin()
                ) {
                    throw ValidationException::withMessages([
                        'email' =>
                            'This password reset link is invalid or is no longer available.',
                    ]);
                }
                $newPassword = (string) $validated['password'];
                if (
                    $passwordSecurity->isRecentlyUsed(
                        $user,
                        $newPassword
                    )
                ) {
                    throw ValidationException::withMessages([
                        'password' =>
                            'Choose a password different from your current and previous password.',
                    ]);
                }
                DB::transaction(function () use (
                    $user,
                    $newPassword,
                    $passwordSecurity
                ) {
                    $passwordSecurity->rememberCurrentPassword($user);
                    $user->forceFill([
                        'password' => Hash::make($newPassword),
                        'password_set_at' => now(),
                        'remember_token' => Str::random(60),
                        'security_reminder_shown_at' => null,
                    ])->save();
                });
                event(new PasswordReset($user));
            }
        );
        return $status === Password::PASSWORD_RESET
            ? redirect()
                ->route('login')
                ->with('status', __($status))
            : back()
                ->withInput(['email' => $validated['email']])
                ->withErrors(['email' => __($status)]);
    }
}