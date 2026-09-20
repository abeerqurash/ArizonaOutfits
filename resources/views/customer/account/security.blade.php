@extends('customer.layouts.app')

@section('title', 'Login & Security')

@section('page-heading', 'Login & Security')


@push('page-styles')
<style>
    .security-shell {
        display: grid;
        gap: 18px
    }

    .security-overview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 20px;
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04)
    }

    .security-overview-main {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0
    }

    .security-overview-icon {
        display: grid;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 10px;
        background: #ccfbf1;
        color: #0f766e
    }

    .security-overview-copy span {
        display: block;
        margin-bottom: 3px;
        color: #0f766e;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .08em;
        text-transform: uppercase
    }

    .security-overview-copy strong {
        display: block;
        color: #172033;
        font-size: 15px
    }

    .security-overview-copy small {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.45
    }

    .security-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px
    }

    .security-card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 18px;
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .035)
    }

    .security-card h3 {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 0 14px;
        color: #172033;
        font-size: 15px
    }

    .security-card h3>i {
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 8px;
        background: #f1f5f9;
        color: #0f766e;
        font-size: 12px
    }

    .security-card p {
        margin: 0 0 13px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
        overflow-wrap: anywhere
    }

    .security-status {
        display: inline-flex;
        align-self: flex-start;
        align-items: center;
        gap: 6px;
        margin: 0 0 12px;
        padding: 5px 8px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-size: 11px;
        font-weight: 800
    }

    .security-status .fa-circle-exclamation {
        color: #b45309
    }

    .security-status:has(.fa-circle-exclamation) {
        background: #fffbeb;
        color: #92400e
    }

    .security-verified {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #047857 !important
    }

    .security-form {
        display: grid;
        gap: 12px;
        margin-top: auto
    }

    .security-field {
        display: grid;
        gap: 6px
    }

    .security-field>span {
        color: #334155;
        font-size: 12px;
        font-weight: 800
    }

    .security-form input {
        box-sizing: border-box;
        width: 100%;
        min-height: 42px;
        padding: 9px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #172033;
        font: inherit;
        outline: none
    }

    .security-form input:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .09)
    }

    .security-field small {
        color: #be123c;
        font-size: 12px;
        line-height: 1.4
    }

    .security-button {
        display: inline-flex;
        align-self: flex-start;
        justify-content: center;
        align-items: center;
        gap: 7px;
        min-height: 39px;
        padding: 0 13px;
        border: 1px solid #dbe2ea;
        border-radius: 8px;
        background: #fff;
        color: #172033;
        cursor: pointer;
        text-decoration: none;
        font-size: 12px;
        font-weight: 850;
        transition: .15s ease
    }

    .security-button:hover {
        border-color: #99f6e4;
        background: #f0fdfa;
        color: #0f766e
    }

    .security-form>.security-button {
        border-color: #0f766e;
        background: #0f766e;
        color: #fff
    }

    .security-form>.security-button:hover {
        background: #115e59;
        color: #fff
    }

    .security-warning {
        margin: 0 0 16px;
        padding: 13px 14px;
        border: 1px solid #fde68a;
        border-left: 3px solid #d97706;
        border-radius: 10px;
        background: #fffbeb
    }

    .security-warning h3 {
        margin: 0 0 5px;
        color: #92400e;
        font-size: 13px
    }

    .security-warning p {
        margin: 0;
        color: #78570b;
        font-size: 12px
    }

    .security-card .security-warning {
        margin-top: 5px
    }

    .security-provider,
    .security-connected {
        display: grid;
        gap: 12px
    }

    .security-provider {
        height: 100%
    }

    .security-connected .security-button,
    .security-provider>a.security-button {
        margin-top: auto
    }

    .security-connected p {
        margin: 0
    }

    .security-note {
        margin-top: 3px !important;
        color: #64748b !important;
        font-size: 12px !important
    }

    .security-message {
        margin-bottom: 16px;
        padding: 12px 14px;
        border: 1px solid #e5eaf1;
        border-radius: 10px;
        background: #fff;
        font-size: 13px
    }

    .security-message ul {
        margin: 7px 0 0;
        padding-left: 18px
    }

    @media(max-width:760px) {
        .security-grid {
            grid-template-columns: 1fr
        }

        .security-overview {
            align-items: flex-start;
            padding: 16px
        }

        .security-card {
            padding: 16px
        }
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


<div class="security-shell">

    <div class="security-overview">
        <div class="security-overview-main">
            <span class="security-overview-icon" aria-hidden="true">
                <i class="fa-solid fa-shield-halved"></i>
            </span>

            <div class="security-overview-copy">
                <span>Account protection</span>
                <strong>Keep your sign-in methods secure</strong>
                <small>Email, phone and connected accounts are managed from one place.</small>
            </div>
        </div>
    </div>

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

                <label class="security-field">
                    <span>New email address</span>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                        placeholder="New email address">

                    @error('email')
                    <small>{{ $message }}</small>
                    @enderror
                </label>

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


                <label class="security-field">
                    <span>Email address</span>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                        placeholder="Email address">

                    @error('email')
                    <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="security-field">
                    <span>Create password</span>

                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Create password">

                    @error('password')
                    <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="security-field">
                    <span>Confirm password</span>

                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm password">
                </label>


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

                <label class="security-field">
                    <span>Phone number</span>

                    <input
                        type="tel"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        maxlength="30"
                        autocomplete="tel"
                        inputmode="tel"
                        pattern="[0-9+() .-]{8,30}"
                        title="Enter a valid phone number using digits and common phone symbols."
                        placeholder="e.g. +92 312 3456789"
                        oninput="this.value=this.value.replace(/[^0-9+() .-]/g, '')">

                    @error('phone')
                    <small>{{ $message }}</small>
                    @enderror
                </label>

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

</div>

@endsection