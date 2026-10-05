<?php

namespace App\Http\Requests\Auth;

use App\Models\CustomerEmailIdentity;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Authenticate ONLY through an active, verified custom-email identity.
     *
     * A value remaining in users.email is not proof that email/password login
     * is currently connected. Google/Facebook email addresses are independent
     * identities and must never enable local password login by themselves.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $normalizedEmail = CustomerEmailIdentity::normalizeEmail(
            (string) $this->input('email')
        );

        $user = null;

        if ($normalizedEmail !== null) {
            $identity = CustomerEmailIdentity::query()
                ->with('user')
                ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
                ->where('normalized_email', $normalizedEmail)
                ->whereNull('disconnected_at')
                ->whereNotNull('email_verified_at')
                ->where('is_login_enabled', true)
                ->first();

            $candidate = $identity?->user;

            if (
                $candidate instanceof User
                && $candidate->hasPassword()
                && Hash::check(
                    (string) $this->input('password'),
                    (string) $candidate->password
                )
            ) {
                $user = $candidate;
            }
        }

        if ($user === null) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('email')).'|'.$this->ip()
        );
    }
}
