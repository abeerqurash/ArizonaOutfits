<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\PhoneVerificationCode;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class PhoneAuthController extends Controller
{
    private const REGISTER_PURPOSE = 'register';
    private const LOGIN_PURPOSE = 'login';
    private const OTP_EXPIRY_MINUTES = 10;
    private const MAX_OTP_ATTEMPTS = 5;
    public function createRegistration(): View
    {
        return view('auth.phone-register');
    }
    public function sendRegistrationCode(
        Request $request
    ): RedirectResponse {
        $request->validate([
            'phone' => [
                'required',
                'string',
                'max:30',
            ],
        ]);
        $phone = $this->normalizePhone(
            (string) $request->input('phone')
        );
        if (! $phone) {
            throw ValidationException::withMessages([
                'phone' =>
                'Enter a valid phone number. Pakistani numbers can be entered like 03123456789. International numbers must include the country code, for example +923123456789.',
            ]);
        }
        if (
            User::query()
            ->where('phone', $phone)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'phone' =>
                'An account already exists with this phone number. Please log in instead.',
            ]);
        }
        $this->invalidateOldCodes(
            $phone,
            self::REGISTER_PURPOSE
        );
        $code = $this->generateOtp();
        PhoneVerificationCode::create([
            'phone' => $phone,
            'purpose' => self::REGISTER_PURPOSE,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(
                self::OTP_EXPIRY_MINUTES
            ),
        ]);
        $this->deliverDevelopmentOtp(
            $phone,
            $code,
            self::REGISTER_PURPOSE
        );
        $request->session()->put(
            'phone_registration.phone',
            $phone
        );
        return redirect()
            ->route('phone.register.verify')
            ->with(
                'status',
                'Verification code sent. During local development, check storage/logs/laravel.log for the OTP.'
            );
    }
    public function showRegistrationVerification(
        Request $request
    ): View|RedirectResponse {
        if (
            ! $request->session()->has(
                'phone_registration.phone'
            )
        ) {
            return redirect()
                ->route('phone.register');
        }
        return view(
            'auth.verify-phone',
            [
                'mode' => 'register',
                'phone' => $request
                    ->session()
                    ->get(
                        'phone_registration.phone'
                    ),
            ]
        );
    }
    public function verifyRegistrationCode(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'code' => [
                'required',
                'digits:6',
            ],
        ]);
        $phone = $request
            ->session()
            ->get(
                'phone_registration.phone'
            );
        if (! $phone) {
            return redirect()
                ->route('phone.register')
                ->withErrors([
                    'phone' =>
                    'Your phone verification session expired. Please start again.',
                ]);
        }
        if (
            User::query()
            ->where('phone', $phone)
            ->exists()
        ) {
            $request->session()->forget(
                'phone_registration'
            );
            return redirect()
                ->route('phone.login')
                ->withErrors([
                    'phone' =>
                    'An account already exists with this phone number. Please log in.',
                ]);
        }
        $verification = $this->getActiveCode(
            $phone,
            self::REGISTER_PURPOSE
        );
        $this->verifyOtp(
            $verification,
            (string) $validated['code']
        );
        $verification->forceFill([
            'used_at' => now(),
        ])->save();
        $user = User::create([
            'name' => $this->generateGuestName(),
            'email' => null,
            'phone' => $phone,
            'phone_verified_at' => now(),
            'password' => null,
            'registration_method' => 'phone',
            'status' => 'active',
            'is_admin' => false,
            'is_super_admin' => false,
        ]);
        event(
            new Registered($user)
        );
        Auth::login(
            $user,
            true
        );
        $request->session()->forget(
            'phone_registration'
        );
        $request->session()->regenerate();
        return redirect()
            ->route('customer.dashboard')
            ->with(
                'success',
                'Your account has been created successfully. Please add an email, Google or Facebook account as a backup login method.'
            );
    }
    public function createLogin(): View
    {
        return view('auth.phone-login');
    }
    public function sendLoginCode(
        Request $request
    ): RedirectResponse {
        $request->validate([
            'phone' => [
                'required',
                'string',
                'max:30',
            ],
        ]);
        $phone = $this->normalizePhone(
            (string) $request->input('phone')
        );
        if (! $phone) {
            throw ValidationException::withMessages([
                'phone' =>
                'Enter a valid phone number.',
            ]);
        }
        $user = User::query()
            ->where('phone', $phone)
            ->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'phone' =>
                'No account was found with this phone number.',
            ]);
        }
        if (
            $user->is_admin
            || $user->is_super_admin
        ) {
            throw ValidationException::withMessages([
                'phone' =>
                'Admin accounts cannot use customer phone login.',
            ]);
        }
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'phone' =>
                'Your account has been disabled. Please contact support.',
            ]);
        }
        if (! $user->hasVerifiedPhone()) {
            throw ValidationException::withMessages([
                'phone' =>
                'This phone number has not been verified yet.',
            ]);
        }
        $this->invalidateOldCodes(
            $phone,
            self::LOGIN_PURPOSE
        );
        $code = $this->generateOtp();
        PhoneVerificationCode::create([
            'phone' => $phone,
            'purpose' => self::LOGIN_PURPOSE,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(
                self::OTP_EXPIRY_MINUTES
            ),
        ]);
        $this->deliverDevelopmentOtp(
            $phone,
            $code,
            self::LOGIN_PURPOSE
        );
        $request->session()->put(
            'phone_login.phone',
            $phone
        );
        return redirect()
            ->route('phone.login.verify')
            ->with(
                'status',
                'Verification code sent. During local development, check storage/logs/laravel.log for the OTP.'
            );
    }
    public function showLoginVerification(
        Request $request
    ): View|RedirectResponse {
        if (
            ! $request->session()->has(
                'phone_login.phone'
            )
        ) {
            return redirect()
                ->route('phone.login');
        }
        return view(
            'auth.verify-phone',
            [
                'mode' => 'login',
                'phone' => $request
                    ->session()
                    ->get(
                        'phone_login.phone'
                    ),
            ]
        );
    }
    public function verifyLoginCode(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'code' => [
                'required',
                'digits:6',
            ],
        ]);
        $phone = $request
            ->session()
            ->get(
                'phone_login.phone'
            );
        if (! $phone) {
            return redirect()
                ->route('phone.login')
                ->withErrors([
                    'phone' =>
                    'Your login verification session expired. Please start again.',
                ]);
        }
        $user = User::query()
            ->where('phone', $phone)
            ->first();
        if (! $user) {
            $request->session()->forget(
                'phone_login'
            );
            return redirect()
                ->route('phone.login')
                ->withErrors([
                    'phone' =>
                    'No account was found with this phone number.',
                ]);
        }
        if (
            $user->is_admin
            || $user->is_super_admin
        ) {
            throw ValidationException::withMessages([
                'code' =>
                'Admin accounts cannot use customer phone login.',
            ]);
        }
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'code' =>
                'Your account has been disabled. Please contact support.',
            ]);
        }
        if (! $user->hasVerifiedPhone()) {
            throw ValidationException::withMessages([
                'code' =>
                'This phone number is not verified.',
            ]);
        }
        $verification = $this->getActiveCode(
            $phone,
            self::LOGIN_PURPOSE
        );
        $this->verifyOtp(
            $verification,
            (string) $validated['code']
        );
        $verification->forceFill([
            'used_at' => now(),
        ])->save();
        Auth::login(
            $user,
            true
        );
        $request->session()->forget(
            'phone_login'
        );
        $request->session()->regenerate();
        $intended = $request->session()->pull(
            'customer.url.intended'
        );
        if ($this->isSafeCustomerDestination($intended)) {
            return redirect()->to($intended);
        }
        $legacyIntended = $request->session()->get(
            'url.intended'
        );
        if ($this->isSafeCustomerDestination($legacyIntended)) {
            $request->session()->forget('url.intended');
            return redirect()->to($legacyIntended);
        }
        return redirect()->route('customer.dashboard');
    }
    private function isSafeCustomerDestination(mixed $destination): bool
    {
        if (!is_string($destination) || trim($destination) === '') {
            return false;
        }
        $destination = trim($destination);
        if (str_starts_with($destination, '//')) {
            return false;
        }
        $path = parse_url($destination, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return false;
        }
        if (!str_starts_with($destination, '/')) {
            $scheme = parse_url($destination, PHP_URL_SCHEME);
            $host = parse_url($destination, PHP_URL_HOST);
            if (
                !is_string($scheme) ||
                !in_array(strtolower($scheme), ['http', 'https'], true) ||
                !is_string($host) ||
                strcasecmp($host, request()->getHost()) !== 0
            ) {
                return false;
            }
        }
        $adminLoginPath = parse_url(route('admin.login'), PHP_URL_PATH);
        if (!is_string($adminLoginPath) || $adminLoginPath === '') {
            return false;
        }
        $adminBasePath = preg_replace('#/login/?$#', '', $adminLoginPath);
        if (!is_string($adminBasePath) || $adminBasePath === '') {
            return false;
        }
        $adminBasePath = rtrim($adminBasePath, '/');
        return $path !== $adminBasePath
            && !str_starts_with($path, $adminBasePath . '/');
    }
    private function normalizePhone(
        string $phone
    ): ?string {
        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }
        $phone = preg_replace(
            '/[\s\-\(\)\.]/',
            '',
            $phone
        );
        if (! is_string($phone)) {
            return null;
        }
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr(
                $phone,
                2
            );
        }
        if (
            preg_match(
                '/^03\d{9}$/',
                $phone
            )
        ) {
            $phone = '+92' . substr(
                $phone,
                1
            );
        }
        if (
            preg_match(
                '/^92\d{10}$/',
                $phone
            )
        ) {
            $phone = '+' . $phone;
        }
        if (
            ! preg_match(
                '/^\+[1-9]\d{7,14}$/',
                $phone
            )
        ) {
            return null;
        }
        return $phone;
    }
    private function generateGuestName(): string
    {
        do {
            $name =
                'guest0'
                . random_int(
                    10000000000,
                    99999999999
                );
        } while (
            User::query()
            ->where('name', $name)
            ->exists()
        );
        return $name;
    }
    private function generateOtp(): string
    {
        return (string) random_int(
            100000,
            999999
        );
    }
    private function invalidateOldCodes(
        string $phone,
        string $purpose
    ): void {
        PhoneVerificationCode::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
            ]);
    }
    private function getActiveCode(
        string $phone,
        string $purpose
    ): PhoneVerificationCode {
        $verification =
            PhoneVerificationCode::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->latest('id')
            ->first();
        if (! $verification) {
            throw ValidationException::withMessages([
                'code' =>
                'No active verification code was found. Please request another code.',
            ]);
        }
        if ($verification->isExpired()) {
            $verification->forceFill([
                'used_at' => now(),
            ])->save();
            throw ValidationException::withMessages([
                'code' =>
                'Your verification code has expired. Please request another code.',
            ]);
        }
        if (
            $verification->attempts
            >= self::MAX_OTP_ATTEMPTS
        ) {
            $verification->forceFill([
                'used_at' => now(),
            ])->save();
            throw ValidationException::withMessages([
                'code' =>
                'Too many incorrect attempts. Please request a new verification code.',
            ]);
        }
        return $verification;
    }
    private function verifyOtp(
        PhoneVerificationCode $verification,
        string $code
    ): void {
        if (
            Hash::check(
                $code,
                $verification->code_hash
            )
        ) {
            return;
        }
        $verification->increment(
            'attempts'
        );
        if (
            $verification->fresh()->attempts
            >= self::MAX_OTP_ATTEMPTS
        ) {
            $verification->forceFill([
                'used_at' => now(),
            ])->save();
        }
        throw ValidationException::withMessages([
            'code' =>
            'The verification code is incorrect.',
        ]);
    }
    private function deliverDevelopmentOtp(
        string $phone,
        string $code,
        string $purpose
    ): void {
        if (! app()->environment('local')) {
            Log::warning(
                'SMS provider is not configured.',
                [
                    'phone' => $phone,
                    'purpose' => $purpose,
                ]
            );
            throw ValidationException::withMessages([
                'phone' =>
                'Phone verification is temporarily unavailable. Please try again later.',
            ]);
        }
        Log::info(
            'ArizonaOutfits development phone OTP',
            [
                'phone' => $phone,
                'purpose' => $purpose,
                'otp' => $code,
                'expires_in_minutes' =>
                self::OTP_EXPIRY_MINUTES,
            ]
        );
    }
}