<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\CustomerEmailIdentity;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);
        $email = CustomerEmailIdentity::normalizeEmail(
            (string) $validated['email']
        );
        if ($email === null) {
            throw ValidationException::withMessages([
                'email' => 'Please enter a valid email address.',
            ]);
        }
        $emailOwnerExists = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();
        if ($emailOwnerExists) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already in use.',
            ]);
        }
        $user = DB::transaction(function () use ($validated, $email): User {
            $now = now();
            $user = User::create([
                'name' => trim((string) $validated['name']),
                'email' => $email,
                'phone' => null,
                'password' => Hash::make((string) $validated['password']),
                'password_set_at' => $now,
                'email_verified_at' => null,
                'email_login_enabled_at' => null,
                'email_login_pending_at' => $now,
                'registration_method' => 'email',
                'status' => 'active',
                'is_admin' => false,
                'is_super_admin' => false,
            ]);
            CustomerEmailIdentity::query()->create([
                'user_id' => $user->id,
                'source' => CustomerEmailIdentity::SOURCE_CUSTOM,
                'email' => $email,
                'normalized_email' => $email,
                'provider_user_id' => null,
                'provider_verified_at' => null,
                'email_verified_at' => null,
                'email_verification_pending_at' => $now,
                // Temporary legacy compatibility while old columns remain.
                'verified_at' => null,
                'verification_pending_at' => $now,
                'connected_at' => $now,
                'disconnected_at' => null,
                'is_login_enabled' => false,
            ]);
            return $user;
        });
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }
}