<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Social providers allowed for customer login.
     */
    private const PROVIDERS = [
        'google',
        'facebook',
    ];

    /**
     * Redirect the customer to Google or Facebook.
     */
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(
            in_array($provider, self::PROVIDERS, true),
            404
        );

        /*
    |--------------------------------------------------------------------------
    | GOOGLE
    |--------------------------------------------------------------------------
    |
    | Google is already working.
    | Keep Google scopes explicit.
    |
    */

        if ($provider === 'google') {
            return Socialite::driver('google')
                ->setScopes([
                    'openid',
                    'profile',
                    'email',
                ])
                ->redirect();
        }


        /*
    |--------------------------------------------------------------------------
    | FACEBOOK
    |--------------------------------------------------------------------------
    |
    | Your current Meta app is rejecting the "email" scope.
    |
    | For now, request only public_profile.
    | This lets us confirm the Facebook OAuth flow itself is working.
    |
    */

        return Socialite::driver('facebook')
            ->setScopes([
                'public_profile',
            ])
            ->redirect();
    }

    /**
     * Handle the response returned by Google or Facebook.
     */
    public function callback(string $provider): RedirectResponse
    {
        abort_unless(
            in_array($provider, self::PROVIDERS, true),
            404
        );

        try {
            /*
            |--------------------------------------------------------------------------
            | Get Customer Information From Provider
            |--------------------------------------------------------------------------
            */

            $socialUser = Socialite::driver($provider)->user();

            $providerId = trim((string) $socialUser->getId());

            $email = strtolower(
                trim((string) $socialUser->getEmail())
            );

            $name = trim(
                (string) $socialUser->getName()
            );

            $avatar = $socialUser->getAvatar();


            /*
            |--------------------------------------------------------------------------
            | Provider ID Validation
            |--------------------------------------------------------------------------
            */

            if ($providerId === '') {
                return $this->backToLogin(
                    'We could not verify your social account. Please try again or use email and password.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Email Validation
            |--------------------------------------------------------------------------
            |
            | We require an email because ArizonaOutfits uses email as the
            | primary customer account identifier.
            |
            */

            if ($email === '') {
                return $this->backToLogin(
                    ucfirst($provider) .
                        ' did not provide an email address. Please use another account or sign in with email and password.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Determine Provider Database Column
            |--------------------------------------------------------------------------
            |
            | Google   -> google_id
            | Facebook -> facebook_id
            |
            */

            $providerColumn = $provider . '_id';


            /*
            |--------------------------------------------------------------------------
            | First Search By Social Provider ID
            |--------------------------------------------------------------------------
            */

            $user = User::query()
                ->where($providerColumn, $providerId)
                ->first();


            /*
            |--------------------------------------------------------------------------
            | Otherwise Search By Email
            |--------------------------------------------------------------------------
            |
            | This prevents duplicate customer accounts.
            |
            | Example:
            |
            | Customer originally registered:
            | john@gmail.com + password
            |
            | Later customer clicks Google using:
            | john@gmail.com
            |
            | We connect Google to the EXISTING customer instead of
            | creating another account.
            |
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
            |
            | Customer social login must never automatically connect
            | an administrator account.
            |
            */

            if (
                $user &&
                ($user->is_admin || $user->is_super_admin)
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
            | Create New Customer
            |--------------------------------------------------------------------------
            */

            if (! $user) {
                $user = User::create([
                    'name' => $name !== ''
                        ? $name
                        : Str::before($email, '@'),

                    'email' => $email,

                    /*
                     * Your current users table requires a password.
                     *
                     * Social customers don't enter one, so we generate
                     * a secure random password internally.
                     *
                     * User model's "hashed" cast hashes this automatically.
                     */
                    'password' => Str::random(64),

                    'status' => 'active',

                    'is_admin' => false,

                    'is_super_admin' => false,

                    $providerColumn => $providerId,

                    'avatar' => $avatar,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Fire Laravel Registered Event
                |--------------------------------------------------------------------------
                */

                event(
                    new Registered($user)
                );
            } else {

                /*
                |--------------------------------------------------------------------------
                | Existing Customer
                |--------------------------------------------------------------------------
                |
                | Connect Google/Facebook to the existing customer account.
                |
                */

                $updates = [];


                /*
                 * Provider is not connected yet.
                 */
                if (blank($user->{$providerColumn})) {
                    $updates[$providerColumn] = $providerId;
                }

                /*
                 * The account is already connected to another provider
                 * account with a different ID.
                 */ elseif (
                    (string) $user->{$providerColumn} !== $providerId
                ) {
                    return $this->backToLogin(
                        'This email is already connected to a different ' .
                            ucfirst($provider) .
                            ' account.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Update Social Avatar
                |--------------------------------------------------------------------------
                */

                if (
                    $avatar &&
                    $user->avatar !== $avatar
                ) {
                    $updates['avatar'] = $avatar;
                }


                /*
                |--------------------------------------------------------------------------
                | Save Updates
                |--------------------------------------------------------------------------
                */

                if ($updates !== []) {
                    $user->forceFill($updates)->save();
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
            |
            | Important protection against session fixation.
            |
            */

            request()
                ->session()
                ->regenerate();


            /*
            |--------------------------------------------------------------------------
            | Remove Old Intended URL
            |--------------------------------------------------------------------------
            */

            request()
                ->session()
                ->forget('url.intended');


            /*
            |--------------------------------------------------------------------------
            | Send Customer To Account Dashboard
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('customer.dashboard');
        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Log Technical Error
            |--------------------------------------------------------------------------
            |
            | Technical information goes into Laravel logs.
            | We don't expose sensitive technical information to customers.
            |
            */

            Log::warning(
                'Social authentication failed.',
                [
                    'provider' => $provider,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Friendly Customer Error
            |--------------------------------------------------------------------------
            */

            return $this->backToLogin(
                'Social sign in could not be completed. Please try again or use email and password.'
            );
        }
    }


    /**
     * Return customer to normal login page with an error.
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
