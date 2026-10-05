<?php

namespace App\Services;

use App\Models\User;

class CustomerLoginSecurity
{
    /**
     * Count login methods that can genuinely be used to get back into the
     * account. For security lockout protection, email only counts when the address is
     * verified AND the customer has deliberately created a usable password.
     * A pending/unverified email must never justify removing the final
     * verified login method.
     */
    public function usableLoginMethodCount(User $user): int
    {
        $count = 0;

        $emailState = $user->resolvedEmailIdentityState();

        if (
            ($emailState['custom_connected'] ?? false)
            && ($emailState['custom_verified'] ?? false)
            && $user->hasPassword()
        ) {
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


    public function canDisconnectEmail(User $user): bool
    {
        $emailState = $user->resolvedEmailIdentityState();

        if (
            ! ($emailState['custom_connected'] ?? false)
            || ! ($emailState['custom_verified'] ?? false)
            || ! $user->hasPassword()
        ) {
            return false;
        }

        return $this->usableLoginMethodCount($user) > 1;
    }

    public function canDisconnectSocial(User $user, string $provider): bool
    {
        if (! in_array($provider, ['google', 'facebook'], true)) {
            return false;
        }

        $connected = $provider === 'google'
            ? $user->hasGoogleAccount()
            : $user->hasFacebookAccount();

        if (! $connected) {
            return false;
        }

        if ($this->usableLoginMethodCount($user) <= 1) {
            return false;
        }

        /*
         * Every verified login method is equal for lockout protection.
         * If at least two usable methods exist, one of them may be removed.
         * The customer's original registration method does not permanently
         * lock that method to the account.
         */
        return true;
    }

    public function canRemovePhone(User $user): bool
    {
        if (! $user->hasVerifiedPhone()) {
            return false;
        }

        /*
         * Phone follows the same rule as every other usable login method:
         * removable when another usable method remains.
         */
        return $this->usableLoginMethodCount($user) > 1;
    }
}
