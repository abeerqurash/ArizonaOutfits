<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PhoneAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Social Login
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/auth/{provider}/redirect',
        [SocialAuthController::class, 'redirect']
    )
        ->whereIn(
            'provider',
            [
                'google',
                'facebook',
            ]
        )
        ->name('social.redirect');

    Route::get(
        '/auth/{provider}/callback',
        [SocialAuthController::class, 'callback']
    )
        ->whereIn(
            'provider',
            [
                'google',
                'facebook',
            ]
        )
        ->name('social.callback');


    /*
    |--------------------------------------------------------------------------
    | Email Registration
    |--------------------------------------------------------------------------
    */

    Route::get(
        'register',
        [RegisteredUserController::class, 'create']
    )->name('register');

    Route::post(
        'register',
        [RegisteredUserController::class, 'store']
    )
        ->middleware('throttle:authentication');


    /*
    |--------------------------------------------------------------------------
    | Email / Password Login
    |--------------------------------------------------------------------------
    */

    Route::get(
        'login',
        [AuthenticatedSessionController::class, 'create']
    )->name('login');

    Route::post(
        'login',
        [AuthenticatedSessionController::class, 'store']
    )
        ->middleware('throttle:authentication');


    /*
    |--------------------------------------------------------------------------
    | Phone Registration
    |--------------------------------------------------------------------------
    */

    Route::get(
        'register/phone',
        [PhoneAuthController::class, 'createRegistration']
    )->name('phone.register');

    Route::post(
        'register/phone',
        [PhoneAuthController::class, 'sendRegistrationCode']
    )
        ->middleware('throttle:5,1')
        ->name('phone.register.send');

    Route::get(
        'register/phone/verify',
        [PhoneAuthController::class, 'showRegistrationVerification']
    )->name('phone.register.verify');

    Route::post(
        'register/phone/verify',
        [PhoneAuthController::class, 'verifyRegistrationCode']
    )
        ->middleware('throttle:10,1')
        ->name('phone.register.verify.store');


    /*
    |--------------------------------------------------------------------------
    | Phone Login
    |--------------------------------------------------------------------------
    */

    Route::get(
        'login/phone',
        [PhoneAuthController::class, 'createLogin']
    )->name('phone.login');

    Route::post(
        'login/phone',
        [PhoneAuthController::class, 'sendLoginCode']
    )
        ->middleware('throttle:5,1')
        ->name('phone.login.send');

    Route::get(
        'login/phone/verify',
        [PhoneAuthController::class, 'showLoginVerification']
    )->name('phone.login.verify');

    Route::post(
        'login/phone/verify',
        [PhoneAuthController::class, 'verifyLoginCode']
    )
        ->middleware('throttle:10,1')
        ->name('phone.login.verify.store');


    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */

    Route::get(
        'forgot-password',
        [PasswordResetLinkController::class, 'create']
    )->name('password.request');

    Route::post(
        'forgot-password',
        [PasswordResetLinkController::class, 'store']
    )
        ->middleware('throttle:authentication')
        ->name('password.email');

    Route::get(
        'reset-password/{token}',
        [NewPasswordController::class, 'create']
    )->name('password.reset');

    Route::post(
        'reset-password',
        [NewPasswordController::class, 'store']
    )
        ->middleware('throttle:authentication')
        ->name('password.store');
});


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        'verify-email',
        EmailVerificationPromptController::class
    )->name('verification.notice');

    Route::get(
        'verify-email/{id}/{hash}',
        VerifyEmailController::class
    )
        ->middleware([
            'signed',
            'throttle:6,1',
        ])
        ->name('verification.verify');

    Route::post(
        'email/verification-notification',
        [EmailVerificationNotificationController::class, 'store']
    )
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get(
        'confirm-password',
        [ConfirmablePasswordController::class, 'show']
    )->name('password.confirm');

    Route::post(
        'confirm-password',
        [ConfirmablePasswordController::class, 'store']
    )
        ->middleware('throttle:authentication');

    Route::put(
        'password',
        [PasswordController::class, 'update']
    )
        ->middleware('throttle:authentication')
        ->name('password.update');

    Route::post(
        'logout',
        [AuthenticatedSessionController::class, 'destroy']
    )->name('logout');
});