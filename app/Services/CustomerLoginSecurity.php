<?php

namespace App\Services;

use App\Models\User;

class CustomerLoginSecurity
{
    /**
     * Number of currently usable login methods.
     */
    public function usableLoginMethodCount(
        User $user
    ): int {
        $count = 0;

        if ($user->hasEmailLogin()) {
            $count++;
        }

        if ($user->hasVerifiedPhone()) {
            $count++;
        }

        if ($user->hasGoogleAccount()) {
            $count++;
        }

        if ($user->hasFacebookAccount()) {
            $count++;
        }

        return $count;
    }

    /**
     * Determine whether provider can safely be disconnected.
     */
    public function canDisconnectSocial(
        User $user,
        string $provider
    ): bool {
        if (
            ! in_array(
                $provider,
                [
                    'google',
                    'facebook',
                ],
                true
            )
        ) {
            return false;
        }

        $connected =
            $provider === 'google'
                ? $user->hasGoogleAccount()
                : $user->hasFacebookAccount();

        if (! $connected) {
            return false;
        }

        /*
         * Removing this provider must leave another
         * usable authentication method.
         */
        if (
            $this->usableLoginMethodCount($user)
            <= 1
        ) {
            return false;
        }

        /*
         * Phone-created accounts must retain a backup
         * method besides their verified phone.
         */
        if (
            $user->registration_method === 'phone' &&
            $user->hasVerifiedPhone()
        ) {
            $remainingBackups = 0;

            if ($user->hasVerifiedEmail()) {
                $remainingBackups++;
            }

            if (
                $provider !== 'google' &&
                $user->hasGoogleAccount()
            ) {
                $remainingBackups++;
            }

            if (
                $provider !== 'facebook' &&
                $user->hasFacebookAccount()
            ) {
                $remainingBackups++;
            }

            if ($remainingBackups < 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether verified phone can be removed.
     */
    public function canRemovePhone(
        User $user
    ): bool {
        if (! $user->hasVerifiedPhone()) {
            return false;
        }

        /*
         * Phone-created accounts retain their phone as
         * their primary method for now.
         */
        if (
            $user->registration_method === 'phone'
        ) {
            return false;
        }

        /*
         * Another usable login method must remain.
         */
        return $this->usableLoginMethodCount($user)
            > 1;
    }
}