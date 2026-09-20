@extends('layouts.app')

@section('title', 'Create Account With Phone')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'Create your account.',
            'message' => 'Use your phone number to create a secure Arizona Outfits customer account.'
        ]
    )

    <div class="auth-card">

        <span>
            Phone registration
        </span>

        <h1>
            Continue with phone
        </h1>

        <p>
            We will send a 6-digit verification code to your phone number.
        </p>


        @if (session('status'))

            <div class="auth-status" role="status">
                {{ session('status') }}
            </div>

        @endif


        <form
            method="POST"
            action="{{ route('phone.register.send') }}"
        >

            @csrf


            <div class="auth-field">

                <label for="phone">
                    Phone number
                </label>

                <div class="auth-input-icon-wrap">

                    <i
                        class="fa-solid fa-mobile-screen-button auth-input-leading-icon"
                        aria-hidden="true"
                    ></i>

                    <input
                        id="phone"
                        type="tel"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        autofocus
                        inputmode="tel"
                        autocomplete="tel"
                        maxlength="30"
                        pattern="[0-9+() .-]{8,30}"
                        title="Enter a valid phone number using digits and common phone symbols."
                        placeholder="+92 312 3456789"
                        oninput="this.value=this.value.replace(/[^0-9+() .-]/g, '')"
                        aria-describedby="phone-help"
                    >

                </div>

                <small id="phone-help" class="auth-phone-help">
                    Pakistan: 03123456789 or +923123456789. International numbers should include the country code.
                </small>

                @error('phone')

                    <p class="auth-error" role="alert">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <button
                type="submit"
                class="auth-submit auth-submit-wide"
            >

                <i
                    class="fa-solid fa-paper-plane"
                    aria-hidden="true"
                ></i>

                Send verification code

            </button>

        </form>


        <div class="auth-phone-divider">
            <span>Already registered?</span>
        </div>


        <p class="auth-switch">

            Already have an account?

            <a href="{{ route('phone.login') }}">
                Log in with phone
            </a>

        </p>


        <p class="auth-switch auth-switch-secondary">

            <a href="{{ route('register') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                Use another signup method
            </a>

        </p>

    </div>

</section>

<style>
    .auth-input-icon-wrap {
        position: relative;
    }

    .auth-input-icon-wrap input {
        padding-left: 42px !important;
    }

    .auth-input-leading-icon {
        position: absolute;
        z-index: 2;
        top: 50%;
        left: 14px;
        color: #0f766e;
        font-size: 13px;
        pointer-events: none;
        transform: translateY(-50%);
    }

    .auth-phone-help {
        display: block;
        margin-top: 7px;
        color: #64748b;
        font-size: 11px;
        line-height: 1.5;
    }

    .auth-phone-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0 4px;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .auth-phone-divider::before,
    .auth-phone-divider::after {
        height: 1px;
        flex: 1;
        background: #e5eaf1;
        content: "";
    }

    .auth-switch-secondary {
        margin-top: 10px;
    }

    .auth-switch-secondary a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>

@endsection
