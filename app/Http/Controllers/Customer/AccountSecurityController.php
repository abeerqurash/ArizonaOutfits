<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PhoneVerificationCode;
use App\Models\User;
use App\Models\CustomerEmailIdentity;
use App\Services\PasswordSecurityService;
use App\Services\CustomerLoginSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyPendingEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
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
        Request $request,
        CustomerLoginSecurity $loginSecurity
    ): View {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser, 401);

        /*
         * Always read the latest database state for the security page.
         * This prevents a just-disconnected Google/Facebook account from
         * being rendered from a stale authenticated model instance.
         */
        $user = User::query()
            ->whereKey($authenticatedUser->id)
            ->firstOrFail();

        return view(
            'customer.account.security',
            [
                'user' => $user,
                'canDisconnectEmail' =>
                    $loginSecurity->canDisconnectEmail($user),
                'canRemovePhone' =>
                    $loginSecurity->canRemovePhone($user),
                'canDisconnectGoogle' =>
                    $loginSecurity->canDisconnectSocial($user, 'google'),
                'canDisconnectFacebook' =>
                    $loginSecurity->canDisconnectSocial($user, 'facebook'),
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

        abort_unless($user, 401);

        if ($user->hasEmailLogin() || $user->hasPendingEmailLogin()) {
            throw ValidationException::withMessages([
                'email' =>
                    'An email login is already connected or awaiting verification.',
            ]);
        }

        $passwordRules = $user->hasPassword()
            ? ['nullable']
            : [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ];

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
            ],
            'password' => $passwordRules,
        ]);

        $email = strtolower(
            trim((string) $validated['email'])
        );

        $existingUser = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereKeyNot($user->id)
            ->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' =>
                    'This email address is already connected to another account.',
            ]);
        }

        $updates = [
            'email' => $email,
            'email_verified_at' => null,
            'email_login_enabled_at' => null,
            'email_login_pending_at' => now(),
            'security_reminder_shown_at' => null,
        ];

        if (! $user->hasPassword()) {
            $updates['password'] = Hash::make(
                (string) $validated['password']
            );
            $updates['password_set_at'] = now();
        }

        $user->forceFill($updates)->save();

        /*
         * Persist the manual/custom email as its own identity.
         * This never overwrites Google or Facebook, even when the address
         * string is identical.
         */
        CustomerEmailIdentity::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'source' => CustomerEmailIdentity::SOURCE_CUSTOM,
            ],
            [
                'email' => $email,
                'normalized_email' =>
                    CustomerEmailIdentity::normalizeEmail($email),
                'provider_user_id' => null,
                'verified_at' => null,
                'verification_pending_at' => now(),
                'connected_at' => now(),
                'disconnected_at' => null,
                'is_login_enabled' => false,
            ]
        );

        $user->sendEmailVerificationNotification();

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                'A verification email has been sent. Email login will only become connected after you verify that address.'
            );
    }

    /**
     * Send a password-reset link to the authenticated customer's
     * own email address without sending them through the guest-only
     * forgot-password request page.
     */
    public function sendPasswordResetLink(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (
            $user->is_admin
            || $user->is_super_admin
            || $user->status !== 'active'
        ) {
            abort(403);
        }

        if (! $user->hasPassword()) {
            throw ValidationException::withMessages([
                'password_recovery' =>
                    'You do not need password recovery yet. Create your first password from Login & Security.',
            ]);
        }

        if (! filled($user->email)) {
            throw ValidationException::withMessages([
                'password_recovery' =>
                    'Add an email address to your account before using password recovery.',
            ]);
        }

        $status = Password::broker()->sendResetLink([
            'email' => (string) $user->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            Log::warning(
                'Authenticated customer password recovery link failed.',
                [
                    'user_id' => $user->id,
                    'status' => $status,
                ]
            );

            throw ValidationException::withMessages([
                'password_recovery' => __($status),
            ]);
        }

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                'Password reset instructions have been sent to your account email address.'
            );
    }


    /**
     * Create or change the authenticated customer's password.
     *
     * Customers who already have a password must confirm the current
     * password. Social/phone customers who have never created a password
     * can create their first password without being asked for a password
     * that does not exist.
     */
    public function updatePassword(
        Request $request,
        PasswordSecurityService $passwordSecurity
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (
            $user->is_admin
            || $user->is_super_admin
            || $user->status !== 'active'
        ) {
            abort(403);
        }

        $rules = [
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ];

        if ($user->hasPassword()) {
            $rules['current_password'] = [
                'required',
                'string',
            ];
        }

        $validated = $request->validate($rules);

        if (
            $user->hasPassword()
            && ! Hash::check(
                (string) $validated['current_password'],
                (string) $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'current_password' =>
                    'The current password you entered is incorrect.',
            ]);
        }

        $hadPassword = $user->hasPassword();
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
            /*
             * Only preserve a genuine customer-created current password.
             *
             * Legacy social accounts may still contain an inaccessible
             * system-generated hash while password_set_at is NULL. That
             * legacy hash must not become customer password history.
             */
            if ($user->hasPassword()) {
                $passwordSecurity->rememberCurrentPassword($user);
            }

            $user->forceFill([
                'password' => Hash::make($newPassword),
                'password_set_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(60),
                'security_reminder_shown_at' => null,
            ])->save();
        });

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                $hadPassword
                    ? 'Your password has been changed successfully.'
                    : 'Your password has been created successfully.'
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

        abort_unless($user, 401);

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
        | Sensitive Existing Phone Change Reauthentication
        |--------------------------------------------------------------------------
        |
        | Adding the first phone number is not treated as replacing an
        | established login method. Replacing an existing verified phone is.
        |
        | Password-capable customers must prove the current password before
        | an OTP can be sent to a replacement number.
        |
        */

        if ($user->hasVerifiedPhone()) {
            if (! $user->hasPassword()) {
                throw ValidationException::withMessages([
                    'phone' =>
                        'Re-verification is required before changing this phone number. Create a password first or use a supported account re-verification method.',
                ]);
            }

            $request->validate([
                'current_password' => [
                    'required',
                    'string',
                ],
            ]);

            if (
                ! Hash::check(
                    (string) $request->input('current_password'),
                    (string) $user->password
                )
            ) {
                throw ValidationException::withMessages([
                    'current_password' =>
                        'Your current password is incorrect.',
                ]);
            }
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

        if (! $user->hasEmailLogin()) {
            return redirect()
                ->route('customer.security')
                ->withErrors([
                    'email' =>
                    'Connect and verify an email login before changing it.',
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
            'current_password' => [
                'required',
                'string',
            ],
        ]);

        if (
            ! $user->hasPassword()
            || ! Hash::check(
                (string) $validated['current_password'],
                (string) $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'current_password' =>
                    'Your current password is incorrect.',
            ]);
        }

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
