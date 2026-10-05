<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class PasswordSecurityService
{
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