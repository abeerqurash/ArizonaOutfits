<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

            'profile_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_profile_image' => ['nullable', 'boolean'],
            'author_title' => ['nullable', 'string', 'max:255'],

            'facebook_url' => ['nullable', 'url:http,https', 'max:2048'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:2048'],
            'x_url' => ['nullable', 'url:http,https', 'max:2048'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:2048'],
            'youtube_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        $emailChanged = strcasecmp((string) $admin->email, (string) $validated['email']) !== 0;

        $oldProfileImage = $admin->profile_image;
        $newProfileImage = $oldProfileImage;
        $uploadedProfileImage = null;

        if ($request->boolean('remove_profile_image')) {
            $newProfileImage = null;
        }

        if ($request->hasFile('profile_image_upload')) {
            $uploadedProfileImage = $request->file('profile_image_upload')->store('admins/profile', 'public');
            $newProfileImage = $uploadedProfileImage;
        }

        try {
            $admin->fill([
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'phone' => filled($validated['phone'] ?? null) ? trim($validated['phone']) : null,

                'profile_image' => $newProfileImage,
                'author_title' => filled($validated['author_title'] ?? null)
                    ? trim($validated['author_title'])
                    : null,

                'facebook_url' => $this->nullableUrl($validated['facebook_url'] ?? null),
                'instagram_url' => $this->nullableUrl($validated['instagram_url'] ?? null),
                'x_url' => $this->nullableUrl($validated['x_url'] ?? null),
                'linkedin_url' => $this->nullableUrl($validated['linkedin_url'] ?? null),
                'youtube_url' => $this->nullableUrl($validated['youtube_url'] ?? null),
            ]);

            if ($emailChanged) {
                $admin->email_verified_at = null;
            }

            $admin->save();
        } catch (\Throwable $exception) {
            if ($uploadedProfileImage) {
                Storage::disk('public')->delete($uploadedProfileImage);
            }

            throw $exception;
        }

        if (
            $oldProfileImage &&
            $oldProfileImage !== $newProfileImage &&
            $this->isManagedProfileImage($oldProfileImage)
        ) {
            Storage::disk('public')->delete($oldProfileImage);
        }

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

    private function nullableUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function isManagedProfileImage(string $path): bool
    {
        return str_starts_with($path, 'admins/profile/');
    }
}
