<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['admin' => $request->user('admin')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user('admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email,' . $admin->id],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $emailChanged = strcasecmp((string) $admin->email, (string) $validated['email']) !== 0;

        $admin->fill([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => filled($validated['phone'] ?? null) ? trim($validated['phone']) : null,
        ]);

        if ($emailChanged) {
            $admin->email_verified_at = null;
        }

        $admin->save();

        return back()->with('status', 'Administrator profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = $request->user('admin');

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return back()
                ->withErrors(['current_password' => 'The current administrator password is incorrect.'], 'updatePassword')
                ->withInput();
        }

        $admin->forceFill(['password' => Hash::make($validated['password'])])->save();

        return back()->with('password_status', 'Administrator password updated successfully.');
    }
}
