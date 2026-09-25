<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordSecurityService
{
    /**
     * Determine whether the proposed password is disallowed because it
     * matches either the customer's current password or the immediately
     * previous password.
     *
     * Older passwords are intentionally reusable.
     */
    public function isRecentlyUsed(User $user, string $plainPassword): bool
    {
        if (
            filled($user->password)
            && Hash::check($plainPassword, $user->password)
        ) {
            return true;
        }

        $previousPassword = $user->passwordHistories()
            ->latest('id')
            ->value('password');

        return filled($previousPassword)
            && Hash::check($plainPassword, $previousPassword);
    }

    /**
     * Store the current password as the immediately previous password
     * before replacing it with a new password.
     *
     * Only one historical hash is required because the business rule
     * blocks the current password and one immediately previous password.
     */
    public function rememberCurrentPassword(User $user): void
    {
        if (! filled($user->password)) {
            return;
        }

        $user->passwordHistories()->delete();

        $user->passwordHistories()->create([
            'password' => $user->password,
        ]);
    }
}
