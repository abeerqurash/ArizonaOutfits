@extends('layouts.app')

@section('title', 'Create Customer Account')

@include('auth.partials.frontend-styles')

@section('content')

@php
    $registrationHasError = $errors->any() || session('social_error');

    $registrationMessage =
        session('social_error')
        ?: ($errors->any() ? $errors->first() : session('status'));
@endphp

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'Your wardrobe, your way.',
            'message' => 'Create an account for faster checkout, order tracking and a shopping experience built around you.'
        ]
    )

    <div class="auth-card auth-card-scroll">

        <span>Customer account</span>

        <h1>Create account</h1>

        <p>Register with email, phone, Google or Facebook.</p>


        @include('auth.partials.social-login')


        <a
            href="{{ route('phone.register') }}"
            class="auth-submit auth-submit-wide auth-method-button"
        >
            <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
            Continue with phone
        </a>


        <div class="auth-email-divider" aria-hidden="true">
            <span></span>
            <strong>Or use email</strong>
            <span></span>
        </div>


        <form method="POST" action="{{ route('register') }}">

            @csrf


            <div class="auth-field">

                <label for="name">Full name</label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    maxlength="255"
                    autocomplete="name"
                    placeholder="Your full name"
                >

            </div>


            <div class="auth-field">

                <label for="email">Email address</label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    maxlength="255"
                    autocomplete="username"
                    placeholder="you@example.com"
                >

            </div>


            <div class="auth-field">

                <label for="password">Password</label>

                <div class="auth-password-input">

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        aria-describedby="password-requirements password-strength-text"
                        placeholder="Create a secure password"
                    >

                    <button
                        type="button"
                        class="auth-password-toggle"
                        data-password-toggle="password"
                        aria-label="Show password"
                        aria-controls="password"
                    >
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>

                </div>


                <div
                    class="auth-password-strength"
                    aria-live="polite"
                >

                    <div class="auth-strength-track">
                        <span id="password-strength-bar"></span>
                    </div>

                    <strong id="password-strength-text">
                        Enter a password
                    </strong>

                </div>


                <ul
                    class="auth-password-rules"
                    id="password-requirements"
                >
                    <li data-rule="length">10 or more characters</li>
                    <li data-rule="lower">One lowercase letter</li>
                    <li data-rule="upper">One uppercase letter</li>
                    <li data-rule="number">One number</li>
                    <li data-rule="symbol">One symbol</li>
                </ul>

            </div>


            <div class="auth-field">

                <label for="password_confirmation">
                    Confirm password
                </label>

                <div class="auth-password-input">

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        aria-describedby="password-match"
                        placeholder="Enter the password again"
                    >

                    <button
                        type="button"
                        class="auth-password-toggle"
                        data-password-toggle="password_confirmation"
                        aria-label="Show confirmed password"
                        aria-controls="password_confirmation"
                    >
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>

                </div>

                <p
                    class="auth-password-match"
                    id="password-match"
                    aria-live="polite"
                ></p>

            </div>


            <button
                class="auth-submit auth-submit-wide"
                type="submit"
            >
                Create account
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>

        </form>


        <p class="auth-switch">
            Already registered?
            <a href="{{ route('login') }}">Log in</a>
        </p>

    </div>

</section>


@if ($registrationMessage)
<div
    class="customer-auth-popup-backdrop"
    data-customer-auth-popup
    role="presentation"
>
    <div
        class="customer-auth-popup"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="customer-auth-popup-title"
        aria-describedby="customer-auth-popup-message"
    >
        <button
            type="button"
            class="customer-auth-popup-close"
            data-customer-auth-popup-close
            aria-label="Close message"
        >
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>

        <div
            class="customer-auth-popup-icon {{ $registrationHasError ? 'is-error' : 'is-success' }}"
            aria-hidden="true"
        >
            <i class="fa-solid {{ $registrationHasError ? 'fa-circle-exclamation' : 'fa-circle-check' }}"></i>
        </div>

        <h2 id="customer-auth-popup-title">
            {{ $registrationHasError ? 'Unable to create account' : 'Success' }}
        </h2>

        <p id="customer-auth-popup-message">
            {{ $registrationMessage }}
        </p>

        <button
            type="button"
            class="customer-auth-popup-button"
            data-customer-auth-popup-close
        >
            OK
        </button>
    </div>
</div>
@endif


@push('page-styles')
<style>
    .auth-method-button {
        margin-bottom: 20px;
        text-decoration: none;
    }

    .auth-email-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0;
        color: #94a3b8;
    }

    .auth-email-divider span {
        height: 1px;
        flex: 1;
        background: #e5eaf1;
    }

    .auth-email-divider strong {
        flex: 0 0 auto;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .customer-auth-popup-backdrop {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(3px);
    }

    .customer-auth-popup {
        position: relative;
        width: min(100%, 430px);
        padding: 30px 28px 26px;
        border: 1px solid rgba(148, 163, 184, .24);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
        text-align: center;
    }

    .customer-auth-popup-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 50%;
        background: #f1f5f9;
        color: #475569;
        cursor: pointer;
    }

    .customer-auth-popup-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin-bottom: 16px;
        border-radius: 50%;
        font-size: 25px;
    }

    .customer-auth-popup-icon.is-error {
        background: #fef2f2;
        color: #dc2626;
    }

    .customer-auth-popup-icon.is-success {
        background: #f0fdf4;
        color: #16a34a;
    }

    .customer-auth-popup h2 {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 22px;
        line-height: 1.25;
    }

    .customer-auth-popup p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.65;
    }

    .customer-auth-popup-button {
        min-width: 110px;
        margin-top: 22px;
        padding: 11px 22px;
        border: 0;
        border-radius: 10px;
        background: #111827;
        color: #fff;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
    }

    @media (max-width: 575px) {
        .customer-auth-popup {
            padding: 28px 20px 22px;
            border-radius: 15px;
        }
    }
</style>
@endpush


@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const popup = document.querySelector('[data-customer-auth-popup]');

    if (!popup) {
        return;
    }

    const closePopup = function () {
        popup.remove();
    };

    popup.querySelectorAll('[data-customer-auth-popup-close]')
        .forEach(function (button) {
            button.addEventListener('click', closePopup);
        });

    popup.addEventListener('click', function (event) {
        if (event.target === popup) {
            closePopup();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && document.body.contains(popup)) {
            closePopup();
        }
    });
});
</script>
@endpush


@include('auth.partials.password-tools')

@endsection
