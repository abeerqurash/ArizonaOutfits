@extends('layouts.app')

@section('title', 'Forgot Password')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'We will help you back in.',
            'message' => 'Account recovery is secure and simple. Your reset link will be sent only to your registered email.'
        ]
    )

    <div class="auth-card">

        <span>
            Account recovery
        </span>

        <div class="auth-heading-icon" aria-hidden="true">
            <i class="fa-solid fa-key"></i>
        </div>

        <h1>
            Forgot password?
        </h1>

        <p>
            Enter your account email and we will send you a secure password-reset link.
        </p>


        <form
            method="POST"
            action="{{ route('password.email') }}"
        >

            @csrf


            <div class="auth-field">

                <label for="email">
                    Email address
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    maxlength="255"
                    autocomplete="email"
                    placeholder="you@example.com"
                    aria-describedby="email-help"
                >

                <small
                    id="email-help"
                    class="auth-recovery-help"
                >
                    Use the email address connected to your Arizona Outfits account.
                </small>

            </div>


            <button
                class="auth-submit auth-submit-wide"
                type="submit"
            >
                Send reset link

                <i
                    class="fa-regular fa-paper-plane"
                    aria-hidden="true"
                ></i>
            </button>

        </form>


        <p class="auth-switch">

            <a href="{{ route('login') }}">
                <i
                    class="fa-solid fa-arrow-left"
                    aria-hidden="true"
                ></i>

                Back to login
            </a>

        </p>

    </div>

</section>


@if (session('status'))
    <div
        class="customer-auth-popup-layer is-open"
        id="customerAuthSuccessPopup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="customerAuthSuccessTitle"
    >
        <div class="customer-auth-popup">
            <div class="customer-auth-popup-icon customer-auth-popup-icon-success">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </div>

            <span class="customer-auth-popup-eyebrow">
                Account recovery
            </span>

            <h2 id="customerAuthSuccessTitle">
                Request received
            </h2>

            <p>
                {{ session('status') }}
            </p>

            <button
                type="button"
                class="auth-submit auth-submit-wide"
                data-customer-auth-popup-close
            >
                Continue
            </button>
        </div>
    </div>
@endif


@if ($errors->any())
    <div
        class="customer-auth-popup-layer is-open"
        id="customerAuthErrorPopup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="customerAuthErrorTitle"
    >
        <div class="customer-auth-popup">
            <div class="customer-auth-popup-icon customer-auth-popup-icon-error">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            </div>

            <span class="customer-auth-popup-eyebrow customer-auth-popup-eyebrow-error">
                Account recovery
            </span>

            <h2 id="customerAuthErrorTitle">
                Unable to send reset link
            </h2>

            <p>
                {{ $errors->first() }}
            </p>

            <button
                type="button"
                class="auth-submit auth-submit-wide"
                data-customer-auth-popup-close
            >
                Try again
            </button>
        </div>
    </div>
@endif


@push('page-styles')
<style>
    .auth-recovery-help {
        display: block;
        margin-top: 7px;
        color: #64748b;
        font-size: 11px;
        line-height: 1.5;
    }

    .auth-switch a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .customer-auth-popup-layer {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(5px);
    }

    .customer-auth-popup-layer.is-open {
        display: flex;
    }

    .customer-auth-popup {
        width: min(100%, 440px);
        padding: 30px 26px;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
        text-align: center;
    }

    .customer-auth-popup-icon {
        display: grid;
        width: 54px;
        height: 54px;
        margin: 0 auto 16px;
        place-items: center;
        border-radius: 50%;
        font-size: 21px;
    }

    .customer-auth-popup-icon-success {
        background: #ecfdf5;
        color: #047857;
    }

    .customer-auth-popup-icon-error {
        background: #fef2f2;
        color: #dc2626;
    }

    .customer-auth-popup-eyebrow {
        display: block;
        margin-bottom: 7px;
        color: #0f766e;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .customer-auth-popup-eyebrow-error {
        color: #b91c1c;
    }

    .customer-auth-popup h2 {
        margin: 0 0 10px;
        color: #172033;
        font-size: 25px;
        line-height: 1.2;
    }

    .customer-auth-popup p {
        margin: 0 0 22px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.65;
    }

    .customer-auth-popup .auth-submit {
        margin-top: 0;
    }
</style>
@endpush


@push('page-scripts')
<script>
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
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
