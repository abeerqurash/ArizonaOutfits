@extends('layouts.app')

@section('title', 'Reset Password')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'A fresh start, securely.',
            'message' => 'Choose a new password and get back to your orders, invoices and saved account details.'
        ]
    )

    <div class="auth-card auth-card-scroll">

        <span>Account recovery</span>

        <div class="auth-heading-icon" aria-hidden="true">
            <i class="fa-solid fa-lock"></i>
        </div>

        <h1>Choose a new password</h1>

        <p>
            Use a strong password that you do not use on another website.
        </p>


        <form method="POST" action="{{ route('password.store') }}">

            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $request->route('token') }}"
            >


            <div class="auth-field">

                <label for="email">Email address</label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
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

                <label for="password">New password</label>

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
                    Confirm new password
                </label>

                <div class="auth-password-input">

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        aria-describedby="password-match"
                        placeholder="Enter the new password again"
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
                Reset password

                <i
                    class="fa-solid fa-arrow-right"
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


@push('page-styles')
<style>
    .auth-switch a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endpush


@include('auth.partials.password-tools')

@endsection
