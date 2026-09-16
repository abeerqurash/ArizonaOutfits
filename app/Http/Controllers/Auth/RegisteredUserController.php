<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /** @throws ValidationException */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $user = User::create([
            'name' => trim($validated['name']),

            'email' => strtolower(
                trim($validated['email'])
            ),

            /*
             * Phone is added and verified separately.
             */
            'phone' => null,

            'password' => Hash::make(
                $validated['password']
            ),

            'registration_method' => 'email',

            'status' => 'active',

            'is_admin' => false,

            'is_super_admin' => false,
        ]);

        event(
            new Registered($user)
        );

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard');
    }
}