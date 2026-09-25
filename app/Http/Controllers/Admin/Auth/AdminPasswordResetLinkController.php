<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = Str::lower(trim($validated['email']));

        $admin = Admin::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('status', 'active')
            ->first();

        /*
         * Keep the public response generic so this endpoint does not reveal
         * whether an administrator account exists.
         */
        if (!$admin) {
            return back()->with(
                'status',
                'If an active administrator account exists for that email address, password reset instructions have been sent.'
            );
        }

        $status = Password::broker('admins')->sendResetLink([
            'email' => $admin->email,
        ]);

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        /*
         * Even if mail delivery/configuration fails, do not expose account
         * existence through a different public success message.
         */
        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return back()->with(
            'status',
            'If an active administrator account exists for that email address, password reset instructions have been sent.'
        );
    }
}
