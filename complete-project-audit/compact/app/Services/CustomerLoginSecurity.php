<?php
namespace App\Services;
use App\Models\User;
class CustomerLoginSecurity
{
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
        return true;
    }
    public function canRemovePhone(User $user): bool
    {
        if (! $user->hasVerifiedPhone()) {
            return false;
        }
        return $this->usableLoginMethodCount($user) > 1;
    }
}