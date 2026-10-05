<?php
namespace App\Http\Controllers;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }
    public function update(
        ProfileUpdateRequest $request
    ): RedirectResponse {
        $user = $request->user();
        $user->forceFill([
            'name' =>
                $request->validated('name'),
        ])->save();
        return Redirect::route(
            'profile.edit'
        )->with(
            'status',
            'profile-updated'
        );
    }
    public function destroy(
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
        if (blank($user->password)) {
            throw ValidationException::withMessages([
                'password' =>
                    'For security, this account cannot be deleted using password confirmation because no password is configured. Add and verify an email/password first.',
            ]);
        }
        $request->validateWithBag(
            'userDeletion',
            [
                'password' => [
                    'required',
                    'string',
                ],
            ]
        );
        if (
            ! Hash::check(
                (string) $request->password,
                (string) $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'password' =>
                    'The password you entered is incorrect.',
            ]);
        }
        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request) {
            $locked = \App\Models\User::query()->lockForUpdate()->findOrFail($user->id);
            abort_if($locked->is_admin || $locked->is_super_admin || $locked->status !== 'active', 403);
            if (!Hash::check((string) $request->password, (string) $locked->password)) {
                throw ValidationException::withMessages([
                    'password' => 'The password you entered is incorrect.',
                ])->errorBag('userDeletion');
            }
            if ($locked->orders()->withTrashed()->exists()) {
                throw ValidationException::withMessages([
                    'password' => 'This account has current or archived orders and cannot be deleted. Contact the store for assistance.',
                ])->errorBag('userDeletion');
            }
            $locked->delete();
        });
        Auth::guard('web')->logout();
        $request->session()->forget([
            'customer.url.intended', 'url.intended', 'social_link',
            'security_phone_verification', 'phone_login', 'phone_registration',
        ]);
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();
        return Redirect::to('/');
    }
}