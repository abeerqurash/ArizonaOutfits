<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomerEmailIdentity;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
class SocialAuthController extends Controller
{
    private const PROVIDERS = [
        'google',
        'facebook',
    ];
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);
        return $this->providerRedirect($provider);
    }
    public function callback(
        Request $request,
        string $provider
    ): RedirectResponse {
        $this->validateProvider($provider);
        try {
            $socialUser = Socialite::driver($provider)->user();
            $providerId = trim(
                (string) $socialUser->getId()
            );
            $email = strtolower(
                trim(
                    (string) $socialUser->getEmail()
                )
            );
            $name = trim(
                (string) $socialUser->getName()
            );
            $avatar = $socialUser->getAvatar();
            if ($providerId === '') {
                return $this->backToLogin(
                    'We could not verify your social account. Please try again.'
                );
            }
            if ($email === '') {
                return $this->backToLogin(
                    ucfirst($provider) .
                    ' did not provide an email address. Please use another login method.'
                );
            }
            $providerColumn =
                $this->providerColumn($provider);
            $user = User::query()
                ->where(
                    $providerColumn,
                    $providerId
                )
                ->first();
            if (! $user) {
                $emailOwner = User::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$email]
                    )
                    ->first();
                if ($emailOwner) {
                    if (
                        $emailOwner->is_admin ||
                        $emailOwner->is_super_admin
                    ) {
                        return $this->backToLogin(
                            'Admin accounts cannot use customer social login. Please use the normal admin login method.'
                        );
                    }
                    if ($emailOwner->status !== 'active') {
                        return $this->backToLogin(
                            'Your account has been disabled. Please contact support.'
                        );
                    }
                    return $this->backToLogin(
                        ucfirst($provider) .
                        ' is not connected to this Arizona Outfits account. Sign in with one of your existing login methods, then connect ' .
                        ucfirst($provider) .
                        ' from Login & Security.'
                    );
                }
            }
            if (
                $user &&
                (
                    $user->is_admin ||
                    $user->is_super_admin
                )
            ) {
                return $this->backToLogin(
                    'Admin accounts cannot use customer social login. Please use the normal admin login method.'
                );
            }
            if (
                $user &&
                $user->status !== 'active'
            ) {
                return $this->backToLogin(
                    'Your account has been disabled. Please contact support.'
                );
            }
            if (! $user) {
                $user = User::create([
                    'name' =>
                        $name !== ''
                            ? $name
                            : Str::before(
                                $email,
                                '@'
                            ),
                    'email' => $email,
                    'password' =>
                        null,
                    'password_set_at' =>
                        null,
                    'registration_method' =>
                        $provider,
                    'status' =>
                        'active',
                    'is_admin' =>
                        false,
                    'is_super_admin' =>
                        false,
                    $providerColumn =>
                        $providerId,
                    'avatar' =>
                        $avatar,
                ]);
                event(
                    new Registered($user)
                );
                $this->syncProviderEmailIdentity(
                    $user,
                    $provider,
                    $providerId,
                    $email
                );
            } else {
                $updates = [];
                if (
                    blank($user->{$providerColumn}) ||
                    (string) $user->{$providerColumn} !== $providerId
                ) {
                    return $this->backToLogin(
                        ucfirst($provider) .
                        ' is not connected to this Arizona Outfits account. Please use another login method and reconnect it from Login & Security.'
                    );
                }
                if (
                    $avatar &&
                    $user->avatar !== $avatar
                ) {
                    $updates['avatar'] =
                        $avatar;
                }
                if ($updates !== []) {
                    $user->forceFill(
                        $updates
                    )->save();
                }
                $this->syncProviderEmailIdentity(
                    $user,
                    $provider,
                    $providerId,
                    $email
                );
            }
            Auth::login(
                $user,
                true
            );
            $request->session()
                ->regenerate();
            return redirect()->intended(
                route('customer.dashboard', absolute: false)
            );
        } catch (Throwable $exception) {
            Log::warning(
                'Social authentication failed.',
                [
                    'provider' =>
                        $provider,
                    'exception' =>
                        $exception::class,
                    'message' =>
                        $exception->getMessage(),
                ]
            );
            return $this->backToLogin(
                'Social sign in could not be completed. Please try again or use another login method.'
            );
        }
    }
    public function connectRedirect(
        Request $request,
        string $provider
    ): RedirectResponse {
        $this->validateProvider($provider);
        $user = $request->user();
        if (! $user) {
            return redirect()
                ->route('login');
        }
        if (
            $user->is_admin ||
            $user->is_super_admin
        ) {
            abort(403);
        }
        if ($user->status !== 'active') {
            Auth::logout();
            $request->session()
                ->invalidate();
            $request->session()
                ->regenerateToken();
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'Your account has been disabled.',
                ]);
        }
        $providerColumn =
            $this->providerColumn($provider);
        if (
            filled(
                $user->{$providerColumn}
            )
        ) {
            return redirect()
                ->route(
                    'customer.security'
                )
                ->with(
                    'success',
                    ucfirst($provider) .
                    ' is already connected to your account.'
                );
        }
        $request->session()->put(
            'social_link',
            [
                'user_id' =>
                    $user->id,
                'provider' =>
                    $provider,
                'started_at' =>
                    now()->timestamp,
            ]
        );
        return $this->providerLinkRedirect(
            $provider
        );
    }
    public function connectCallback(
        Request $request,
        string $provider
    ): RedirectResponse {
        $this->validateProvider($provider);
        $user = $request->user();
        if (! $user) {
            $request->session()
                ->forget('social_link');
            return redirect()
                ->route('login')
                ->with(
                    'social_error',
                    'Your session expired before the account could be connected. Please sign in and try again.'
                );
        }
        if (
            $user->is_admin ||
            $user->is_super_admin
        ) {
            $request->session()
                ->forget('social_link');
            abort(403);
        }
        if ($user->status !== 'active') {
            $request->session()
                ->forget('social_link');
            Auth::logout();
            $request->session()
                ->invalidate();
            $request->session()
                ->regenerateToken();
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'Your account has been disabled.',
                ]);
        }
        $link =
            $request->session()->get(
                'social_link'
            );
        if (
            ! is_array($link) ||
            ! isset(
                $link['user_id'],
                $link['provider'],
                $link['started_at']
            ) ||
            (int) $link['user_id']
                !== (int) $user->id ||
            (string) $link['provider']
                !== $provider
        ) {
            $request->session()
                ->forget('social_link');
            return redirect()
                ->route(
                    'customer.security'
                )
                ->withErrors([
                    'social' =>
                        'The social connection request is invalid or expired. Please try again.',
                ]);
        }
        if (
            now()->timestamp -
            (int) $link['started_at']
            > 900
        ) {
            $request->session()
                ->forget('social_link');
            return redirect()
                ->route(
                    'customer.security'
                )
                ->withErrors([
                    'social' =>
                        'The social connection request expired. Please try again.',
                ]);
        }
        try {
            $redirectUrl =
                config(
                    'services.' .
                    $provider .
                    '.link_redirect'
                );
            if (
                ! is_string($redirectUrl) ||
                trim($redirectUrl) === ''
            ) {
                throw new \RuntimeException(
                    'Social account linking redirect URL is not configured for ' .
                    $provider .
                    '.'
                );
            }
            $socialUser =
                Socialite::driver($provider)
                    ->redirectUrl(
                        $redirectUrl
                    )
                    ->user();
            $providerId = trim(
                (string)
                    $socialUser->getId()
            );
            $providerEmail =
                strtolower(
                    trim(
                        (string)
                            $socialUser->getEmail()
                    )
                );
            $avatar =
                $socialUser->getAvatar();
            if ($providerId === '') {
                throw new \RuntimeException(
                    'Provider did not return an account ID.'
                );
            }
            $providerColumn =
                $this->providerColumn(
                    $provider
                );
            if (
                filled(
                    $user->{$providerColumn}
                ) &&
                (string) $user->{$providerColumn}
                    !== $providerId
            ) {
                $request->session()
                    ->forget('social_link');
                return redirect()
                    ->route(
                        'customer.security'
                    )
                    ->withErrors([
                        'social' =>
                            'A different ' .
                            ucfirst($provider) .
                            ' account is already connected to your account.',
                    ]);
            }
            $providerOwner =
                User::query()
                    ->where(
                        $providerColumn,
                        $providerId
                    )
                    ->first();
            if (
                $providerOwner &&
                (int) $providerOwner->id
                    !== (int) $user->id
            ) {
                $request->session()
                    ->forget('social_link');
                return redirect()
                    ->route(
                        'customer.security'
                    )
                    ->withErrors([
                        'social' =>
                            'This ' .
                            ucfirst($provider) .
                            ' account is already connected to another Arizona Outfits account.',
                    ]);
            }
            $updates = [
                $providerColumn =>
                    $providerId,
                'security_reminder_shown_at' =>
                    null,
            ];
            if (
                $avatar &&
                blank($user->avatar)
            ) {
                $updates['avatar'] =
                    $avatar;
            }
            $user->forceFill(
                $updates
            )->save();
            $this->syncProviderEmailIdentity(
                $user,
                $provider,
                $providerId,
                $providerEmail !== ''
                    ? $providerEmail
                    : null
            );
            $request->session()
                ->forget('social_link');
            Log::info(
                'Customer connected social account.',
                [
                    'user_id' =>
                        $user->id,
                    'provider' =>
                        $provider,
                    'provider_email_available' =>
                        $providerEmail !== '',
                ]
            );
            return redirect()
                ->route(
                    'customer.security'
                )
                ->with(
                    'success',
                    ucfirst($provider) .
                    ' has been connected successfully.'
                );
        } catch (Throwable $exception) {
            $request->session()
                ->forget('social_link');
            Log::warning(
                'Social account linking failed.',
                [
                    'user_id' =>
                        $user->id,
                    'provider' =>
                        $provider,
                    'exception' =>
                        $exception::class,
                    'message' =>
                        $exception->getMessage(),
                ]
            );
            return redirect()
                ->route(
                    'customer.security'
                )
                ->withErrors([
                    'social' =>
                        ucfirst($provider) .
                        ' could not be connected. Please try again.',
                ]);
        }
    }
    private function syncProviderEmailIdentity(
        User $user,
        string $provider,
        string $providerId,
        ?string $email
    ): void {
        $normalizedEmail =
            CustomerEmailIdentity::normalizeEmail($email);
        $identity =
            CustomerEmailIdentity::query()
                ->firstOrNew([
                    'user_id' => $user->id,
                    'source' => $provider,
                ]);
        $identity->forceFill([
            'email' => $normalizedEmail,
            'normalized_email' => $normalizedEmail,
            'provider_user_id' => $providerId,
            'provider_verified_at' => now(),
            'email_verified_at' => null,
            'email_verification_pending_at' => null,
            // Temporary legacy compatibility.
            'verified_at' => now(),
            'verification_pending_at' => null,
            'connected_at' => $identity->connected_at ?? now(),
            'disconnected_at' => null,
            'is_login_enabled' => false,
        ])->save();
    }
    private function providerRedirect(
        string $provider
    ): RedirectResponse {
        if ($provider === 'google') {
            return Socialite::driver(
                'google'
            )
                ->setScopes([
                    'openid',
                    'profile',
                    'email',
                ])
                ->redirect();
        }
        return Socialite::driver(
            'facebook'
        )
            ->setScopes([
                'public_profile',
            ])
            ->redirect();
    }
    private function providerLinkRedirect(
        string $provider
    ): RedirectResponse {
        $redirectUrl =
            config(
                'services.' .
                $provider .
                '.link_redirect'
            );
        if (
            ! is_string($redirectUrl) ||
            trim($redirectUrl) === ''
        ) {
            throw new \RuntimeException(
                'Social account linking redirect URL is not configured for ' .
                $provider .
                '.'
            );
        }
        if ($provider === 'google') {
            return Socialite::driver(
                'google'
            )
                ->redirectUrl(
                    $redirectUrl
                )
                ->setScopes([
                    'openid',
                    'profile',
                    'email',
                ])
                ->redirect();
        }
        return Socialite::driver(
            'facebook'
        )
            ->redirectUrl(
                $redirectUrl
            )
            ->setScopes([
                'public_profile',])
            ->redirect();
    }
    private function validateProvider(
        string $provider
    ): void {
        abort_unless(
            in_array(
                $provider,
                self::PROVIDERS,
                true
            ),
            404
        );
    }
    private function providerColumn(
        string $provider
    ): string {
        return match ($provider) {
            'google' =>
                'google_id',
            'facebook' =>
                'facebook_id',
            default =>
                abort(404),
        };
    }
    private function backToLogin(
        string $message
    ): RedirectResponse {
        return redirect()
            ->route('login')
            ->with(
                'social_error',
                $message
            );
    }
}