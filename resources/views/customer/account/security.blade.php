@extends('customer.layouts.app')

@section('title', 'Login & Security')

@section('page-heading', 'Login & Security')


@push('page-styles')

<style>
    .security-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit,
                minmax(280px, 1fr));
        gap: 20px;
    }

    .security-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 14px;
        padding: 24px;
    }

    .security-card h3 {
        margin: 0 0 8px;
    }

    .security-card p {
        margin: 0 0 18px;
        line-height: 1.6;
    }

    .security-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 16px;
        font-weight: 600;
    }

    .security-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .security-form input {
        box-sizing: border-box;
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d7d7d7;
        border-radius: 8px;
    }

    .security-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        padding: 10px 18px;
        border: 0;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
    }

    .security-message {
        padding: 14px 18px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background: #fff;
    }

    .security-message ul {
        margin: 10px 0 0;
        padding-left: 20px;
    }

    .security-warning {
        padding: 18px;
        margin-bottom: 22px;
        border: 1px solid #ddd;
        border-radius: 12px;
        background: #fff;
    }

    .security-warning h3 {
        margin-top: 0;
    }

    .security-warning p {
        margin-bottom: 0;
        line-height: 1.6;
    }

    .security-provider {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .security-connected {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .security-connected p {
        margin: 0;
    }

    .security-note {
        margin-top: 14px;
        font-size: 14px;
        line-height: 1.6;
    }

    .security-verified {
        display: flex;
        align-items: center;
        gap: 7px;
    }
</style>

@endpush


@section('content')

@php
$user = $user ?? auth()->user();
@endphp


<header class="customer-page-heading">

    <div>

        <span>
            Account security
        </span>

        <h2>
            Login & Security
        </h2>

        <p>
            Manage the ways you can securely access your
            Arizona Outfits account.
        </p>

    </div>

</header>


{{-- SECURITY SETUP WARNING --}}

@if ($user->needsBackupLoginMethod())

<div
    class="security-warning"
    role="alert">

    <h3>
        <i class="fa-solid fa-shield-halved"></i>
        Secure your account
    </h3>

    <p>
        Your account currently relies on your verified phone number.
        Add at least one backup method: a verified email,
        Google account or Facebook account.
    </p>

</div>

@elseif ($user->needsVerifiedPhone())

<div
    class="security-warning"
    role="status">

    <h3>
        <i class="fa-solid fa-mobile-screen-button"></i>
        Add a phone number
    </h3>

    <p>
        Add and verify your phone number to improve account
        security and give you another way to access your account.
    </p>

</div>

@endif


<div class="security-grid">


    {{-- ====================================================== --}}
    {{-- EMAIL --}}
    {{-- ====================================================== --}}

    <section class="security-card">

        <h3>
            <i class="fa-solid fa-envelope"></i>
            Email
        </h3>


        @if (filled($user->email))

        <div class="security-status">

            <i class="fa-solid fa-circle-check"></i>

            Connected

        </div>


        <p>
            {{ $user->email }}
        </p>


        @if ($user->hasVerifiedEmail())

        <p class="security-verified">

            <i class="fa-solid fa-circle-check"></i>

            <strong>
                Email verified
            </strong>

        </p>

        @else

        <p class="security-verified">

            <i class="fa-solid fa-clock"></i>

            <strong>
                Verification pending
            </strong>

        </p>


        <form
            method="POST"
            action="{{ route('verification.send') }}">

            @csrf

            <button
                type="submit"
                class="security-button">

                <i class="fa-solid fa-paper-plane"></i>

                Resend verification email

            </button>

        </form>

        @endif


        @if (filled($user->pending_email))

        <div class="security-warning">

            <h3>
                New email verification pending
            </h3>

            <p>
                {{ $user->pending_email }}
            </p>

            <p>
                Your current email remains active until
                this new address is verified.
            </p>


            <form
                method="POST"
                action="{{ route(
                        'customer.security.email.change.cancel'
                    ) }}">

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="security-button">
                    Cancel email change
                </button>

            </form>

        </div>

        @else

        <form
            method="POST"
            action="{{ route(
                    'customer.security.email.change'
                ) }}"
            class="security-form">

            @csrf

            <input
                type="email"
                name="email"
                required
                maxlength="255"
                autocomplete="email"
                placeholder="New email address">

            <button
                type="submit"
                class="security-button">

                <i class="fa-solid fa-envelope-circle-check"></i>

                Change email

            </button>

        </form>

        @endif


        @else

        <div class="security-status">

            <i class="fa-solid fa-circle-exclamation"></i>

            Not connected

        </div>


        <p>
            Add an email and password as a backup login
            and account recovery method.
        </p>


        <form
            method="POST"
            action="{{ route(
                'customer.security.email'
            ) }}"
            class="security-form">

            @csrf


            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                maxlength="255"
                autocomplete="email"
                placeholder="Email address">


            <input
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Create password">


            <input
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirm password">


            <button
                type="submit"
                class="security-button">

                <i class="fa-solid fa-envelope-circle-check"></i>

                Add email

            </button>

        </form>

        @endif

    </section>


    {{-- ====================================================== --}}
    {{-- PHONE --}}
    {{-- ====================================================== --}}

    <section class="security-card">

        <h3>
            <i class="fa-solid fa-mobile-screen-button"></i>
            Phone
        </h3>


        @if ($user->hasVerifiedPhone())

        <div class="security-status">

            <i class="fa-solid fa-circle-check"></i>

            Verified

        </div>


        <p>
            {{ $user->phone }}
        </p>


        <p class="security-note">
            Your phone number has been verified and can be
            used as a secure login method.
        </p>


        @if ($user->registration_method !== 'phone')

        <form
            method="POST"
            action="{{ route('customer.security.phone.remove') }}"
            onsubmit="return confirm('Remove this verified phone number from your account?');">

            @csrf
            @method('DELETE')


            <button
                type="submit"
                class="security-button">

                <i class="fa-solid fa-trash-can"></i>

                Remove phone

            </button>

        </form>

        @else

        <p class="security-note">

            <i class="fa-solid fa-lock"></i>

            This is the primary phone number used to create
            your account and cannot currently be removed.

        </p>

        @endif


        <p class="security-note">
            Changing an existing verified phone number will
            require verification of the new number before
            your current number is replaced.
        </p>

        @else

        <div class="security-status">

            <i class="fa-solid fa-circle-exclamation"></i>

            Not verified

        </div>


        <p>
            Add and verify a phone number to your account.
        </p>


        <form
            method="POST"
            action="{{ route(
        'customer.security.phone.send'
    ) }}"
            class="security-form">

            @csrf

            <input
                type="tel"
                name="phone"
                required
                maxlength="20"
                autocomplete="tel"
                inputmode="tel"
                placeholder="New phone number">

            <button
                type="submit"
                class="security-button">

                <i class="fa-solid fa-mobile-screen-button"></i>

                Change phone

            </button>

        </form>

        @endif

    </section>


    {{-- ====================================================== --}}
    {{-- GOOGLE --}}
    {{-- ====================================================== --}}

    <section class="security-card">

        <h3>
            <i class="fa-brands fa-google"></i>
            Google
        </h3>


        <div class="security-provider">

            @if ($user->hasGoogleAccount())

            <div class="security-connected">

                <p>

                    <i class="fa-solid fa-circle-check"></i>

                    <strong>
                        Google connected
                    </strong>

                </p>


                <p class="security-note">
                    Your Google account can be used as a secure
                    login and backup method.
                </p>


                <form
                    method="POST"
                    action="{{ route(
                            'customer.security.social.disconnect',
                            ['provider' => 'google']
                        ) }}"
                    onsubmit="return confirm('Disconnect Google from your account?');">

                    @csrf
                    @method('DELETE')


                    <button
                        type="submit"
                        class="security-button">

                        <i class="fa-solid fa-link-slash"></i>

                        Disconnect Google

                    </button>

                </form>

            </div>

            @else

            <p>
                Connect Google as an additional secure
                login and account recovery method.
            </p>


            <a
                href="{{ route(
                        'customer.security.social.connect',
                        ['provider' => 'google']
                    ) }}"
                class="security-button">

                <i class="fa-brands fa-google"></i>

                Connect Google

            </a>

            @endif

        </div>

    </section>


    {{-- ====================================================== --}}
    {{-- FACEBOOK --}}
    {{-- ====================================================== --}}

    <section class="security-card">

        <h3>
            <i class="fa-brands fa-facebook"></i>
            Facebook
        </h3>


        <div class="security-provider">

            @if ($user->hasFacebookAccount())

            <div class="security-connected">

                <p>

                    <i class="fa-solid fa-circle-check"></i>

                    <strong>
                        Facebook connected
                    </strong>

                </p>


                <p class="security-note">
                    Your Facebook account can be used as a secure
                    login and backup method.
                </p>


                <form
                    method="POST"
                    action="{{ route(
                            'customer.security.social.disconnect',
                            ['provider' => 'facebook']
                        ) }}"
                    onsubmit="return confirm('Disconnect Facebook from your account?');">

                    @csrf
                    @method('DELETE')


                    <button
                        type="submit"
                        class="security-button">

                        <i class="fa-solid fa-link-slash"></i>

                        Disconnect Facebook

                    </button>

                </form>

            </div>

            @else

            <p>
                Connect Facebook as an additional secure
                login and account recovery method.
            </p>


            <a
                href="{{ route(
                        'customer.security.social.connect',
                        ['provider' => 'facebook']
                    ) }}"
                class="security-button">

                <i class="fa-brands fa-facebook"></i>

                Connect Facebook

            </a>

            @endif

        </div>

    </section>

</div>

@endsection