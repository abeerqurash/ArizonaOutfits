<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CustomerLoginSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginMethodController extends Controller
{
    public function __construct(
        private readonly CustomerLoginSecurity $security
    ) {}

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
}
