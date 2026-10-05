<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user('web');
        abort_unless($user instanceof User, 401);
        abort_if($user->is_admin || $user->is_super_admin || $user->status !== 'active', 403);
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
        DB::transaction(function () use ($user, $validated) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_if($locked->is_admin || $locked->is_super_admin || $locked->status !== 'active', 403);
            if (!$locked->hasPassword() || !Hash::check($validated['current_password'], $locked->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'The current password you entered is incorrect.',
                ])->errorBag('updatePassword');
            }
            $security = app(PasswordSecurityService::class);
            if ($security->isRecentlyUsed($locked, $validated['password'])) {
                throw ValidationException::withMessages([
                    'password' => 'Choose a password different from your current and previous password.',
                ])->errorBag('updatePassword');
            }
            $security->rememberCurrentPassword($locked);
            $locked->forceFill([
                'password' => Hash::make($validated['password']),
                'password_set_at' => now(),
                'remember_token' => Str::random(60),
                'security_reminder_shown_at' => null,
            ])->save();
        });
        return back()->with('status', 'password-updated');
    }
}
