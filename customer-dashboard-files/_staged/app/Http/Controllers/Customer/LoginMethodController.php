<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerEmailIdentity;
use App\Services\CustomerLoginSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginMethodController extends Controller
{
    public function __construct(
        private readonly CustomerLoginSecurity $security
    ) {}

    /**
     * Disconnect local email login while preserving the stored email as
     * customer/contact data.
     */
    public function disconnectEmail(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user, 401);

        if ($user->is_admin || $user->is_super_admin || $user->status !== 'active') {
            abort(403);
        }

        if (! $this->security->canDisconnectEmail($user)) {
            return redirect()
                ->route('customer.security')
                ->with(
                    'error',
                    'Email cannot be disconnected because it is currently required to keep your account accessible.'
                );
        }

        $this->requireCurrentPassword($request);

        DB::transaction(function () use ($user): void {
            $lockedUser = $user->newQuery()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->security->canDisconnectEmail($lockedUser)) {
                abort(
                    409,
                    'Account security changed. Please try again.'
                );
            }

            /*
             * Disconnect the explicit custom-email identity as well as the
             * legacy users-table login flags. The Email card is resolved from
             * customer_email_identities, so changing only the legacy flags
             * would make the page appear unchanged after refresh.
             */
            CustomerEmailIdentity::query()
                ->where('user_id', $lockedUser->id)
                ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
                ->whereNull('disconnected_at')
                ->update([
                    'disconnected_at' => now(),
                    'verification_pending_at' => null,
                    'is_login_enabled' => false,
                    'updated_at' => now(),
                ]);

            $lockedUser->forceFill([
                'email_login_enabled_at' => null,
                'email_login_pending_at' => null,
                'security_reminder_shown_at' => null,
            ])->save();
        });

        return redirect()
            ->route('customer.security')
            ->with(
                'success',
                'Email login has been disconnected. Your stored contact email was kept, but it can no longer be used to sign in.'
            );
    }

    /**
     * Disconnect Google/Facebook.
     */
    public function disconnectSocial(
        Request $request,
        string $provider
    ): RedirectResponse {
        abort_unless(
            in_array(
                $provider,
                [
                    'google',
                    'facebook',
                ],
                true
            ),
            404
        );

        $user = $request->user();

        abort_unless($user, 401);

        if (
            $user->is_admin ||
            $user->is_super_admin
        ) {
            abort(403);
        }

        if ($user->status !== 'active') {
            abort(403);
        }

        $providerColumn =
            match ($provider) {
                'google' =>
                'google_id',

                'facebook' =>
                'facebook_id',
            };

        if (
            blank(
                $user->{$providerColumn}
            )
        ) {
            return redirect()
                ->route(
                    'customer.security'
                )
                ->with(
                    'error',
                    ucfirst($provider) .
                        ' is not connected to your account.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Sensitive Action Re-authentication
        |--------------------------------------------------------------------------
        |
        | Password-capable accounts must prove knowledge of their current
        | ArizonaOutfits password immediately before a connected login method
        | can be removed.
        |
        | Passwordless accounts are deliberately NOT treated as verified merely
        | because a session exists. Their provider/phone re-authentication flow
        | is handled separately.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Lockout Protection
        |--------------------------------------------------------------------------
        */

        if (
            ! $this->security
                ->canDisconnectSocial(
                    $user,
                    $provider
                )
        ) {
            return redirect()
                ->route(
                    'customer.security'
                )
                ->with(
                    'error',
                    'You cannot disconnect ' .
                        ucfirst($provider) .
                        ' because it is currently required to keep your account secure and accessible.'
                );
        }

        /*
         * Only request re-authentication after confirming this login method
         * is actually removable. The final usable login method is never
         * disconnectable.
         */
        $this->requireCurrentPassword($request);

        DB::transaction(
            function () use (
                $user,
                $provider,
                $providerColumn
            ): void {
                $lockedUser =
                    $user->newQuery()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    ! $this->security
                        ->canDisconnectSocial(
                            $lockedUser,
                            $provider
                        )
                ) {
                    abort(
                        409,
                        'Account security changed. Please try again.'
                    );
                }

                $lockedUser->forceFill([
                    $providerColumn =>
                    null,

                    'security_reminder_shown_at' =>
                    null,
                ])->save();

                /*
                 * Keep the new multi-email identity source in sync with the
                 * legacy provider column. Disconnecting Google/Facebook must
                 * invalidate that provider identity without touching custom
                 * email or another provider, even when their email strings
                 * are identical.
                 */
                CustomerEmailIdentity::query()
                    ->where('user_id', $lockedUser->id)
                    ->where('source', $provider)
                    ->whereNull('disconnected_at')
                    ->update([
                        'disconnected_at' => now(),
                        'is_login_enabled' => false,
                        'updated_at' => now(),
                    ]);
            }
        );

        return redirect()
            ->route(
                'customer.security'
            )
            ->with(
                'success',
                ucfirst($provider) .
                    ' has been disconnected successfully.'
            );
    }

    /**
     * Remove verified phone from non-phone-created account.
     */
    public function removePhone(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user, 401);

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
        | Sensitive Action Re-authentication
        |--------------------------------------------------------------------------
        */

        if (
            ! $this->security
                ->canRemovePhone($user)
        ) {
            return redirect()
                ->route(
                    'customer.security'
                )
                ->with(
                    'error',
                    'This phone number cannot be removed because it is currently required for your account security or login.'
                );
        }

        /*
         * Do not ask for a password for an action that lockout protection
         * has already determined cannot be performed.
         */
        $this->requireCurrentPassword($request);

        DB::transaction(
            function () use ($user): void {
                $lockedUser =
                    $user->newQuery()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Re-check while database row is locked.
                 */
                if (
                    ! $this->security
                        ->canRemovePhone(
                            $lockedUser
                        )
                ) {
                    abort(
                        409,
                        'Account security changed. Please try again.'
                    );
                }

                $lockedUser->forceFill([
                    'phone' =>
                    null,

                    'phone_verified_at' =>
                    null,

                    'security_reminder_shown_at' =>
                    null,
                ])->save();
            }
        );

        return redirect()
            ->route(
                'customer.security'
            )
            ->with(
                'success',
                'Your phone number has been removed.'
            );
    }

    /**
     * Require fresh proof of the customer's current local password before
     * a destructive login-method change.
     */
    private function requireCurrentPassword(
        Request $request
    ): void {
        $user = $request->user();

        abort_unless($user, 401);

        /*
         * A passwordless social/phone account must be re-authenticated using
         * an actually connected provider/verified phone. Do not silently
         * accept the existing session as proof.
         */
        if (! $user->hasPassword()) {
            throw ValidationException::withMessages([
                'reauthentication' =>
                    'This account does not have an ArizonaOutfits password. Re-verify with a connected login method before changing this security setting.',
            ]);
        }

        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        if (
            ! Hash::check(
                (string) $validated['current_password'],
                (string) $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'current_password' =>
                    'The password you entered is incorrect.',
            ]);
        }
    }
}
