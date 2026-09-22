<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PhoneVerificationCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyPendingEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AccountSecurityController extends Controller
{
    private const PHONE_LINK_PURPOSE = 'link';

    private const OTP_EXPIRY_MINUTES = 10;

    private const MAX_OTP_ATTEMPTS = 5;

    /**
     * Display Login & Security page.
     */
    public function index(
        Request $request
    ): View {
        return view(
            'customer.account.security',
            [
                'user' => $request->user(),
            ]
        );
    }

    /**
     * Add email/password to an account that does not
     * currently have an email login method.
     */
    public function addEmail(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if (filled($user->email)) {
            throw ValidationException::withMessages([
                'email' =>
                'An email address is already connected to this account.',
            ]);
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $email = strtolower(
            trim(
                (string) $validated['email']
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Duplicate Account Protection
        |--------------------------------------------------------------------------
        |
        | We cannot simply attach an email already owned by another user.
        |
        | Account merging will only happen through a deliberately verified
        | flow, not by typing somebody else's email address.
        |
        */

        $existingUser = User::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [$email]
            )
            ->whereKeyNot($user->id)
            ->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' =>
                'This email address is already connected to another account.',
            ]);
        }

        $user->forceFill([
            'email' => $email,

            'password' => Hash::make(
                $validated['password']
            ),

            /*
             * This password was deliberately created by the customer.
             * Record that fact so future security-sensitive actions can
             * safely distinguish it from legacy/system-generated passwords.
             */
            'password_set_at' => now(),

            'email_verified_at' => null,

            'security_reminder_shown_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                'Email and password have been added to your account.'
            );
    }

    /**
     * Send OTP before attaching/changing a phone number.
     */
    public function sendPhoneCode(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'max:30',
            ],
        ]);

        $user = $request->user();

        $phone = $this->normalizePhone(
            (string) $validated['phone']
        );

        if (! $phone) {
            throw ValidationException::withMessages([
                'phone' =>
                'Enter a valid phone number.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Already Current Verified Phone
        |--------------------------------------------------------------------------
        */

        if (
            $user->phone === $phone
            && $user->hasVerifiedPhone()
        ) {
            throw ValidationException::withMessages([
                'phone' =>
                'This phone number is already verified on your account.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Phone Protection
        |--------------------------------------------------------------------------
        */

        $existingUser = User::query()
            ->where('phone', $phone)
            ->whereKeyNot($user->id)
            ->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'phone' =>
                'This phone number is already connected to another account.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Invalidate Old Link Codes
        |--------------------------------------------------------------------------
        */

        PhoneVerificationCode::query()
            ->where('phone', $phone)
            ->where(
                'purpose',
                self::PHONE_LINK_PURPOSE
            )
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Generate OTP
        |--------------------------------------------------------------------------
        */

        $code = (string) random_int(
            100000,
            999999
        );

        PhoneVerificationCode::create([
            'phone' => $phone,

            'purpose' =>
            self::PHONE_LINK_PURPOSE,

            'code_hash' =>
            Hash::make($code),

            'attempts' => 0,

            'expires_at' =>
            now()->addMinutes(
                self::OTP_EXPIRY_MINUTES
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Store Pending Phone In Session
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'security_phone_verification',
            [
                'user_id' => $user->id,
                'phone' => $phone,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Development OTP Delivery
        |--------------------------------------------------------------------------
        */

        $this->deliverDevelopmentOtp(
            $phone,
            $code
        );

        return redirect()
            ->route(
                'customer.security.phone.verify'
            )
            ->with(
                'status',
                'Verification code sent. During local development, check storage/logs/laravel.log.'
            );
    }

    /**
     * Show phone verification page.
     */
    public function showPhoneVerification(
        Request $request
    ): View|RedirectResponse {
        $verification =
            $request->session()->get(
                'security_phone_verification'
            );

        if (
            ! is_array($verification)
            || ! isset(
                $verification['user_id'],
                $verification['phone']
            )
            || (int) $verification['user_id']
            !== (int) $request->user()->id
        ) {
            return redirect()
                ->route('customer.security')
                ->withErrors([
                    'phone' =>
                    'Your phone verification session expired. Please request another code.',
                ]);
        }

        return view(
            'customer.account.verify-phone',
            [
                'phone' =>
                $verification['phone'],
            ]
        );
    }

    /**
     * Verify OTP and attach/change phone number.
     */
    public function verifyPhone(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'code' => [
                'required',
                'digits:6',
            ],
        ]);

        $sessionVerification =
            $request->session()->get(
                'security_phone_verification'
            );

        if (
            ! is_array($sessionVerification)
            || ! isset(
                $sessionVerification['user_id'],
                $sessionVerification['phone']
            )
            || (int) $sessionVerification['user_id']
            !== (int) $request->user()->id
        ) {
            return redirect()
                ->route('customer.security')
                ->withErrors([
                    'phone' =>
                    'Your phone verification session expired. Please start again.',
                ]);
        }

        $phone =
            (string) $sessionVerification['phone'];

        $verification =
            PhoneVerificationCode::query()
            ->where('phone', $phone)
            ->where(
                'purpose',
                self::PHONE_LINK_PURPOSE
            )
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'code' =>
                'No active verification code was found. Please request another code.',
            ]);
        }

        if ($verification->isExpired()) {
            $verification->forceFill([
                'used_at' => now(),
            ])->save();

            throw ValidationException::withMessages([
                'code' =>
                'Your verification code has expired. Please request another code.',
            ]);
        }

        if (
            $verification->attempts
            >= self::MAX_OTP_ATTEMPTS
        ) {
            $verification->forceFill([
                'used_at' => now(),
            ])->save();

            throw ValidationException::withMessages([
                'code' =>
                'Too many incorrect attempts. Please request another verification code.',
            ]);
        }

        if (
            ! Hash::check(
                (string) $validated['code'],
                $verification->code_hash
            )
        ) {
            $verification->increment(
                'attempts'
            );

            $verification->refresh();

            if (
                $verification->attempts
                >= self::MAX_OTP_ATTEMPTS
            ) {
                $verification->forceFill([
                    'used_at' => now(),
                ])->save();
            }

            throw ValidationException::withMessages([
                'code' =>
                'The verification code is incorrect.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Final Duplicate Check
        |--------------------------------------------------------------------------
        |
        | Another account could theoretically claim this phone between
        | sending and verifying the OTP.
        |
        */

        $existingUser = User::query()
            ->where('phone', $phone)
            ->whereKeyNot(
                $request->user()->id
            )
            ->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'code' =>
                'This phone number is already connected to another account.',
            ]);
        }

        DB::transaction(
            function () use (
                $request,
                $verification,
                $phone
            ): void {
                $verification->forceFill([
                    'used_at' => now(),
                ])->save();

                $request->user()->forceFill([
                    'phone' => $phone,

                    'phone_verified_at' => now(),

                    'security_reminder_shown_at' =>
                    null,
                ])->save();
            }
        );

        $request->session()->forget(
            'security_phone_verification'
        );

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                'Your phone number has been verified and connected successfully.'
            );
    }

    /**
     * Normalise local/international phone numbers.
     */
    private function normalizePhone(
        string $phone
    ): ?string {
        $phone = trim($phone);

        if ($phone === '') {
            return null;
        }

        $phone = preg_replace(
            '/[\s\-\(\)\.]/',
            '',
            $phone
        );

        if (! is_string($phone)) {
            return null;
        }

        if (
            str_starts_with(
                $phone,
                '00'
            )
        ) {
            $phone =
                '+' . substr(
                    $phone,
                    2
                );
        }

        /*
         * Pakistan local mobile:
         *
         * 03123456789
         *
         * becomes:
         *
         * +923123456789
         */
        if (
            preg_match(
                '/^03\d{9}$/',
                $phone
            )
        ) {
            $phone =
                '+92' . substr(
                    $phone,
                    1
                );
        }

        if (
            preg_match(
                '/^92\d{10}$/',
                $phone
            )
        ) {
            $phone =
                '+' . $phone;
        }

        /*
         * E.164-style validation.
         */
        if (
            ! preg_match(
                '/^\+[1-9]\d{7,14}$/',
                $phone
            )
        ) {
            return null;
        }

        return $phone;
    }

    /**
     * XAMPP/local OTP delivery.
     */
    private function deliverDevelopmentOtp(
        string $phone,
        string $code
    ): void {
        if (! app()->environment('local')) {
            Log::warning(
                'SMS provider is not configured for account security phone verification.',
                [
                    'phone' => $phone,
                ]
            );

            throw ValidationException::withMessages([
                'phone' =>
                'Phone verification is temporarily unavailable.',
            ]);
        }

        Log::info(
            'ArizonaOutfits development security phone OTP',
            [
                'phone' => $phone,

                'purpose' =>
                self::PHONE_LINK_PURPOSE,

                'otp' => $code,

                'expires_in_minutes' =>
                self::OTP_EXPIRY_MINUTES,
            ]
        );
    }
    /**
     * Request an existing email address change.
     *
     * The current email remains active until the
     * new email has been successfully verified.
     */
    public function requestEmailChange(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (
            $user->is_admin ||
            $user->is_super_admin
        ) {
            abort(403);
        }

        if ($user->status !== 'active') {
            abort(403);
        }

        if (blank($user->email)) {
            return redirect()
                ->route('customer.security')
                ->withErrors([
                    'email' =>
                    'Add an email address to your account first.',
                ]);
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
            ],
        ]);

        $email = strtolower(
            trim(
                (string) $validated['email']
            )
        );

        /*
    |--------------------------------------------------------------------------
    | Same Email
    |--------------------------------------------------------------------------
    */

        if (
            strtolower(
                (string) $user->email
            ) === $email
        ) {
            throw ValidationException::withMessages([
                'email' =>
                'This is already your current email address.',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Duplicate Protection
    |--------------------------------------------------------------------------
    */

        $existingUser =
            User::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [$email]
            )
            ->whereKeyNot($user->id)
            ->exists();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' =>
                'This email address is already connected to another account.',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Pending Email Collision Protection
    |--------------------------------------------------------------------------
    */

        $pendingOwner =
            User::query()
            ->whereRaw(
                'LOWER(pending_email) = ?',
                [$email]
            )
            ->whereKeyNot($user->id)
            ->exists();

        if ($pendingOwner) {
            throw ValidationException::withMessages([
                'email' =>
                'This email address is currently being verified by another account.',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Store Pending Email
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We do NOT replace $user->email here.
    |
    | Their current login remains completely functional.
    |
    */

        $user->forceFill([
            'pending_email' =>
            $email,

            'pending_email_requested_at' =>
            now(),
        ])->save();

        /*
    |--------------------------------------------------------------------------
    | Send Verification To NEW Email
    |--------------------------------------------------------------------------
    */

        Notification::route(
            'mail',
            $email
        )->notify(
            new VerifyPendingEmail(
                $user->id,
                $email,
                $user->name
            )
        );

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                'A verification link has been sent to your new email address. Your current email will remain active until the new address is verified.'
            );
    }


    /**
     * Verify and activate pending email.
     */
    public function verifyEmailChange(
        Request $request,
        User $user
    ): RedirectResponse {
        /*
    |--------------------------------------------------------------------------
    | Signed URL
    |--------------------------------------------------------------------------
    */

        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $authenticatedUser =
            $request->user();

        if (! $authenticatedUser) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Please log in before completing your email change.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | Account Ownership
    |--------------------------------------------------------------------------
    */

        if (
            (int) $authenticatedUser->id
            !== (int) $user->id
        ) {
            abort(403);
        }

        if (
            $user->is_admin ||
            $user->is_super_admin
        ) {
            abort(403);
        }

        if ($user->status !== 'active') {
            abort(403);
        }

        /*
    |--------------------------------------------------------------------------
    | Validate Pending Email
    |--------------------------------------------------------------------------
    */

        $signedEmail = strtolower(
            trim(
                (string) $request->query(
                    'email'
                )
            )
        );

        if (
            blank($user->pending_email) ||
            strtolower(
                (string) $user->pending_email
            ) !== $signedEmail
        ) {
            return redirect()
                ->route('customer.security')
                ->with(
                    'error',
                    'This email-change request is no longer valid.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | Final Duplicate Check
    |--------------------------------------------------------------------------
    |
    | Another account could have claimed the email after
    | the verification email was originally sent.
    |
    */

        $existingUser =
            User::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [$signedEmail]
            )
            ->whereKeyNot($user->id)
            ->exists();

        if ($existingUser) {
            return redirect()
                ->route('customer.security')
                ->with(
                    'error',
                    'This email address is now connected to another account and cannot be used.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | Replace Email Atomically
    |--------------------------------------------------------------------------
    */

        DB::transaction(
            function () use (
                $user,
                $signedEmail
            ): void {
                $lockedUser =
                    User::query()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    blank(
                        $lockedUser->pending_email
                    ) ||
                    strtolower(
                        (string) $lockedUser->pending_email
                    ) !== $signedEmail
                ) {
                    throw ValidationException::withMessages([
                        'email' =>
                        'This email-change request is no longer valid.',
                    ]);
                }

                /*
             * Final duplicate check while our user row is locked.
             *
             * The database unique email index remains the final
             * protection against simultaneous claims.
             */

                $duplicate =
                    User::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$signedEmail]
                    )
                    ->whereKeyNot(
                        $lockedUser->id
                    )
                    ->exists();

                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'email' =>
                        'This email address is already connected to another account.',
                    ]);
                }

                $lockedUser->forceFill([
                    'email' =>
                    $signedEmail,

                    /*
                 * Ownership was just proven by clicking
                 * the signed link sent to this address.
                 */
                    'email_verified_at' =>
                    now(),

                    'pending_email' =>
                    null,

                    'pending_email_requested_at' =>
                    null,

                    'security_reminder_shown_at' =>
                    null,
                ])->save();
            }
        );

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                'Your new email address has been verified and is now active.'
            );
    }


    /**
     * Cancel pending email change.
     */
    public function cancelEmailChange(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $user->forceFill([
            'pending_email' =>
            null,

            'pending_email_requested_at' =>
            null,
        ])->save();

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                'Your pending email change has been cancelled.'
            );
    }
}
