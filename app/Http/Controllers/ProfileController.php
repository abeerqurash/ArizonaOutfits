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
    /**
     * Show profile.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update basic profile information.
     *
     * Email and phone are deliberately NOT changed here.
     * They have their own verified security flows.
     */
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

    /**
     * Delete customer account.
     *
     * Passwordless accounts are NOT permitted to delete
     * themselves through the password confirmation form.
     */
    public function destroy(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        /*
        |--------------------------------------------------------------------------
        | Admin Protection
        |--------------------------------------------------------------------------
        */

        if (
            $user->is_admin ||
            $user->is_super_admin
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Password Required
        |--------------------------------------------------------------------------
        |
        | A phone/social-only customer must first establish a verified
        | email/password security method before account deletion.
        |
        | Later, if desired, we can add a dedicated phone OTP deletion flow.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Logout + Delete
        |--------------------------------------------------------------------------
        */

        Auth::logout();

        $user->delete();

        $request->session()
            ->invalidate();

        $request->session()
            ->regenerateToken();

        return Redirect::to('/');
    }
}