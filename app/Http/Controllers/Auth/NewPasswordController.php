<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(
        Request $request,
        PasswordSecurityService $passwordSecurity
    ): RedirectResponse {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reset Password
        |--------------------------------------------------------------------------
        |
        | Laravel validates the reset token and resolves the correct user.
        | Once that has succeeded, we enforce Arizona Outfits' password reuse
        | rule before changing anything:
        |
        | - current password: blocked
        | - immediately previous password: blocked
        | - older passwords: allowed
        |
        */
        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function (User $user) use (
                $request,
                $passwordSecurity
            ) {
                $newPassword = (string) $request->password;

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
                    /*
                     * Preserve the current hash as the one immediately
                     * previous password before replacing it.
                     */
                    $passwordSecurity->rememberCurrentPassword($user);

                    $user->forceFill([
                        'password' => Hash::make($newPassword),
                        'password_set_at' => now(),
                        'remember_token' => Str::random(60),
                    ])->save();
                });

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
