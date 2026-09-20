@extends('layouts.app')

@section('title', 'Create Customer Account')

@include('auth.partials.frontend-styles')

@section('content')

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


        @if (session('social_error'))
            <div class="auth-social-error" role="alert">
                {{ session('social_error') }}
            </div>
        @endif


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

                @error('name')
                    <p class="auth-error" role="alert">
                        {{ $message }}
                    </p>
                @enderror

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

                @error('email')
                    <p class="auth-error" role="alert">
                        {{ $message }}
                    </p>
                @enderror

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

                @error('password')
                    <p class="auth-error" role="alert">
                        {{ $message }}
                    </p>
                @enderror


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

                @error('password_confirmation')
                    <p class="auth-error" role="alert">
                        {{ $message }}
                    </p>
                @enderror

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
</style>
@endpush


@include('auth.partials.password-tools')

@endsection
