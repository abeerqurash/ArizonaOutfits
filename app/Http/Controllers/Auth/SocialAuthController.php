<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    /**
     * Supported customer social providers.
     */
    private const PROVIDERS = [
        'google',
        'facebook',
    ];

    /*
    |--------------------------------------------------------------------------
    | NORMAL SOCIAL LOGIN
    |--------------------------------------------------------------------------
    */

    /**
     * Redirect guest customer to social provider for login.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return $this->providerRedirect($provider);
    }

    /**
     * Handle normal guest social login callback.
     */
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

            /*
            |--------------------------------------------------------------------------
            | Provider ID Required
            |--------------------------------------------------------------------------
            */

            if ($providerId === '') {
                return $this->backToLogin(
                    'We could not verify your social account. Please try again.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Normal Guest Social Login Requires Email
            |--------------------------------------------------------------------------
            |
            | We need an email when creating/finding a customer during normal
            | guest login.
            |
            | Authenticated account linking is handled separately below and
            | does not require the provider to return an email.
            |
            */

            if ($email === '') {
                return $this->backToLogin(
                    ucfirst($provider) .
                    ' did not provide an email address. Please use another login method.'
                );
            }

            $providerColumn =
                $this->providerColumn($provider);

            /*
            |--------------------------------------------------------------------------
            | First Find By Provider ID
            |--------------------------------------------------------------------------
            */

            $user = User::query()
                ->where(
                    $providerColumn,
                    $providerId
                )
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Otherwise Find Existing Customer By Email
            |--------------------------------------------------------------------------
            */

            if (! $user) {
                $user = User::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$email]
                    )
                    ->first();
            }

            /*
            |--------------------------------------------------------------------------
            | Protect Admin Accounts
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Block Disabled Customers
            |--------------------------------------------------------------------------
            */

            if (
                $user &&
                $user->status !== 'active'
            ) {
                return $this->backToLogin(
                    'Your account has been disabled. Please contact support.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create New Social Customer
            |--------------------------------------------------------------------------
            */

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

                    /*
                     * Keep this compatible with your current working social
                     * authentication.
                     */
                    'password' =>
                        Str::random(64),

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
            } else {
                /*
                |--------------------------------------------------------------------------
                | Existing Customer
                |--------------------------------------------------------------------------
                */

                $updates = [];

                /*
                 * If this provider is not yet connected to the matched
                 * customer, verify ownership before connecting it.
                 */
                if (
                    blank(
                        $user->{$providerColumn}
                    )
                ) {
                    $providerOwner =
                        User::query()
                            ->where(
                                $providerColumn,
                                $providerId
                            )
                            ->whereKeyNot(
                                $user->id
                            )
                            ->first();

                    if ($providerOwner) {
                        return $this->backToLogin(
                            'This ' .
                            ucfirst($provider) .
                            ' account is already connected to another customer.'
                        );
                    }

                    $updates[$providerColumn] =
                        $providerId;
                } elseif (
                    (string) $user->{$providerColumn}
                    !== $providerId
                ) {
                    return $this->backToLogin(
                        'This email is already connected to a different ' .
                        ucfirst($provider) .
                        ' account.'
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
            }

            /*
            |--------------------------------------------------------------------------
            | Login Customer
            |--------------------------------------------------------------------------
            */

            Auth::login(
                $user,
                true
            );

            /*
            |--------------------------------------------------------------------------
            | Regenerate Session
            |--------------------------------------------------------------------------
            */

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

    /*
    |--------------------------------------------------------------------------
    | CONNECT SOCIAL ACCOUNT
    |--------------------------------------------------------------------------
    */

    /**
     * Redirect an already-authenticated customer to Google/Facebook
     * for intentional account linking.
     */
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

        /*
        |--------------------------------------------------------------------------
        | Customer Accounts Only
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
        | Disabled Customer Protection
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Already Connected
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Store Linking Intent
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Account linking has a DIFFERENT callback URL from normal login.
        |
        */

        return $this->providerLinkRedirect(
            $provider
        );
    }

    /**
     * Handle social account linking callback.
     */
    public function connectCallback(
        Request $request,
        string $provider
    ): RedirectResponse {
        $this->validateProvider($provider);

        /*
        |--------------------------------------------------------------------------
        | Customer Must Still Be Logged In
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Admin Protection
        |--------------------------------------------------------------------------
        */

        if (
            $user->is_admin ||
            $user->is_super_admin
        ) {
            $request->session()
                ->forget('social_link');

            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Disabled Customer Protection
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Validate Linking Session
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Linking Request Expires After 15 Minutes
        |--------------------------------------------------------------------------
        */

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
            /*
            |--------------------------------------------------------------------------
            | IMPORTANT: Use Linking Callback URL
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Provider ID Required
            |--------------------------------------------------------------------------
            */

            if ($providerId === '') {
                throw new \RuntimeException(
                    'Provider did not return an account ID.'
                );
            }

            $providerColumn =
                $this->providerColumn(
                    $provider
                );

            /*
            |--------------------------------------------------------------------------
            | Check Current Customer Again
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Provider Ownership Protection
            |--------------------------------------------------------------------------
            |
            | A Google/Facebook account may belong to only one local user.
            |
            */

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

            /*
            |--------------------------------------------------------------------------
            | Connect Provider To Current User
            |--------------------------------------------------------------------------
            |
            | DO NOT search/merge by provider email here.
            |
            | The customer is already authenticated, so the provider ID is
            | intentionally connected to this exact account.
            |
            */

            $updates = [
                $providerColumn =>
                    $providerId,

                'security_reminder_shown_at' =>
                    null,
            ];

            /*
            |--------------------------------------------------------------------------
            | Avatar
            |--------------------------------------------------------------------------
            */

            if (
                $avatar &&
                blank($user->avatar)
            ) {
                $updates['avatar'] =
                    $avatar;
            }

            /*
            |--------------------------------------------------------------------------
            | Provider Email
            |--------------------------------------------------------------------------
            |
            | We deliberately do NOT copy provider email into users.email.
            |
            | Phone-created customers must add their email using the secure
            | email setup flow.
            |
            | Facebook may also return no email with public_profile.
            |
            */

            $user->forceFill(
                $updates
            )->save();

            /*
            |--------------------------------------------------------------------------
            | Finish Linking Session
            |--------------------------------------------------------------------------
            */

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

    /*
    |--------------------------------------------------------------------------
    | PROVIDER HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Normal Google/Facebook login redirect.
     *
     * Socialite uses services.{provider}.redirect.
     */
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

        /*
         * Keep Facebook compatible with the currently working
         * Meta configuration.
         */
        return Socialite::driver(
            'facebook'
        )
            ->setScopes([
                'public_profile',
            ])
            ->redirect();
    }

    /**
     * Google/Facebook account-linking redirect.
     *
     * Uses services.{provider}.link_redirect instead of the
     * normal login callback URL.
     */
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
                'public_profile',
            ])
            ->redirect();
    }

    /**
     * Validate provider.
     */
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

    /**
     * Get provider database column.
     */
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

    /**
     * Return guest customer to login page.
     */
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