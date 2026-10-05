<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(
        EmailVerificationRequest $request
    ): RedirectResponse {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Already Verified
        |--------------------------------------------------------------------------
        */

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route(
                    'customer.security'
                )
                ->with(
                    'success',
                    'Your email address is already verified.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Email
        |--------------------------------------------------------------------------
        */

        if ($user->markEmailAsVerified()) {
            event(
                new Verified($user)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Reset Security Reminder State
        |--------------------------------------------------------------------------
        */

        $user->forceFill([
            'security_reminder_shown_at' =>
                null,
        ])->save();

        return redirect()
            ->route(
                'customer.security'
            )
            ->with(
                'success',
                'Your email address has been verified successfully.'
            );
    }
}