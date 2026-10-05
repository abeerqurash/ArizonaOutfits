@extends('layouts.app')

@section('title', 'Customer Login')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">
    @include('auth.partials.visual-copy', [
        'heading' => 'Welcome back to your style.',
        'message' => 'Sign in to follow orders, download invoices and keep your account details up to date.'
    ])

    <div class="auth-card">
        <span>Customer account</span>
        <h1>Welcome back</h1>
        <p>Sign in with email, phone, Google or Facebook.</p>

        @include('auth.partials.social-login')

        <a href="{{ route('phone.login') }}" class="auth-submit auth-submit-wide auth-method-button">
            <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
            Continue with phone
        </a>

        <div class="auth-email-divider" aria-hidden="true">
            <span></span><strong>Or use email</strong><span></span>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="auth-field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    required autofocus maxlength="255" autocomplete="username"
                    placeholder="you@example.com">
            </div>

            <div class="auth-field">
                <label for="password">Password</label>
                <div class="auth-password-input">
                    <input id="password" type="password" name="password" required
                        autocomplete="current-password" placeholder="Enter your password">
                    <button type="button" class="auth-password-toggle"
                        data-password-toggle="password" aria-label="Show password"
                        aria-controls="password">
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="auth-login-options">
                <label class="auth-check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Remember me
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <button class="auth-submit auth-submit-wide" type="submit">
                Log in
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
        </form>

        @if (Route::has('register'))
            <p class="auth-switch">
                New to Arizona Outfits?
                <a href="{{ route('register') }}">Create an account</a>
            </p>
        @endif
    </div>
</section>


@if ($errors->any() || session('social_error') || session('status'))
    @php
        $customerAuthHasError =
            $errors->any() || session('social_error');

        $customerAuthMessage =
            session('social_error')
            ?: ($errors->any() ? $errors->first() : session('status'));
    @endphp

    <div
        class="customer-auth-popup-layer is-open"
        id="customerAuthMessagePopup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="customerAuthMessageTitle"
    >
        <div class="customer-auth-popup">

            <div class="customer-auth-popup-icon {{ $customerAuthHasError ? 'customer-auth-popup-icon-error' : 'customer-auth-popup-icon-success' }}">
                <i
                    class="fa-solid {{ $customerAuthHasError ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"
                    aria-hidden="true"
                ></i>
            </div>

            <span class="customer-auth-popup-eyebrow {{ $customerAuthHasError ? 'customer-auth-popup-eyebrow-error' : '' }}">
                Customer account
            </span>

            <h2 id="customerAuthMessageTitle">
                {{ $customerAuthHasError ? 'Unable to sign in' : 'Success' }}
            </h2>

            <p>
                {{ $customerAuthMessage }}
            </p>

            <button
                type="button"
                class="auth-submit auth-submit-wide"
                data-customer-auth-popup-close
            >
                {{ $customerAuthHasError ? 'Try again' : 'Continue' }}
            </button>

        </div>
    </div>
@endif


@push('page-styles')
<style>
.auth-method-button{margin-bottom:20px;text-decoration:none}
.auth-email-divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:#94a3b8}
.auth-email-divider span{height:1px;flex:1;background:#e5eaf1}
.auth-email-divider strong{flex:0 0 auto;font-size:10px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}

.customer-auth-popup-layer{
    position:fixed;
    inset:0;
    z-index:99999;
    display:none;
    align-items:center;
    justify-content:center;
    padding:20px;
    background:rgba(15,23,42,.62);
    backdrop-filter:blur(5px);
}

.customer-auth-popup-layer.is-open{
    display:flex;
}

.customer-auth-popup{
    width:min(100%,440px);
    padding:30px 26px;
    border-radius:16px;
    background:#fff;
    box-shadow:0 24px 70px rgba(15,23,42,.24);
    text-align:center;
}

.customer-auth-popup-icon{
    display:grid;
    width:54px;
    height:54px;
    margin:0 auto 16px;
    place-items:center;
    border-radius:50%;
    font-size:21px;
}

.customer-auth-popup-icon-success{
    background:#ecfdf5;
    color:#047857;
}

.customer-auth-popup-icon-error{
    background:#fef2f2;
    color:#dc2626;
}

.customer-auth-popup-eyebrow{
    display:block;
    margin-bottom:7px;
    color:#0f766e;
    font-size:10px;
    font-weight:800;
    letter-spacing:.14em;
    text-transform:uppercase;
}

.customer-auth-popup-eyebrow-error{
    color:#b91c1c;
}

.customer-auth-popup h2{
    margin:0 0 10px;
    color:#172033;
    font-size:25px;
    line-height:1.2;
}

.customer-auth-popup p{
    margin:0 0 22px;
    color:#64748b;
    font-size:14px;
    line-height:1.65;
}

.customer-auth-popup .auth-submit{
    margin-top:0;
}
</style>
@endpush

@push('page-scripts')
<script>
'use strict';

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.passwordToggle);

            if (!input) {
                return;
            }

            const showing = input.type === 'text';

            input.type = showing ? 'password' : 'text';

            button.setAttribute(
                'aria-label',
                showing ? 'Show password' : 'Hide password'
            );

            const icon = button.querySelector('i');

            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }
        });
    });

    document
        .querySelectorAll('[data-customer-auth-popup-close]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const popup = button.closest(
                    '.customer-auth-popup-layer'
                );

                if (popup) {
                    popup.classList.remove('is-open');
                }
            });
        });
});
</script>
@endpush

@endsection
