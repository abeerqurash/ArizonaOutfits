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

    .security-password-recovery {
        display: grid;
        gap: 9px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #e5eaf1
    }

    .security-password-recovery .security-note {
        margin: 0 !important
    }

    .security-field-error {
        color: #be123c;
        font-size: 12px;
        line-height: 1.4
    }

    .security-password-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap
    }

    .security-password-wrap {
        position: relative
    }

    .security-password-wrap input {
        padding-right: 44px
    }

    .security-password-toggle {
        position: absolute;
        top: 50%;
        right: 8px;
        display: grid;
        width: 32px;
        height: 32px;
        padding: 0;
        place-items: center;
        transform: translateY(-50%);
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #64748b;
        cursor: pointer
    }

    .security-password-toggle:hover,
    .security-password-toggle:focus-visible {
        background: #f1f5f9;
        color: #0f766e;
        outline: none
    }

    .security-password-help {
        display: grid;
        gap: 7px;
        margin: 0;
        padding: 11px 12px;
        border: 1px solid #e5eaf1;
        border-radius: 9px;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        line-height: 1.5
    }

    .security-password-help strong {
        color: #334155
    }

    .security-password-generated {
        display: none;
        margin: 0;
        padding: 10px 12px;
        border: 1px solid #99f6e4;
        border-radius: 9px;
        background: #f0fdfa;
        color: #115e59;
        font-size: 12px;
        line-height: 1.5;
        overflow-wrap: anywhere
    }

    .security-password-generated.is-visible {
        display: block
    }


    .security-password-strength {
        display: grid;
        gap: 8px;
        margin-top: 2px;
    }

    .security-password-strength__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #64748b;
        font-size: 11px;
    }

    .security-password-strength__head strong {
        color: #475569;
    }

    .security-password-strength__track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #e5eaf1;
    }

    .security-password-strength__bar {
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: #94a3b8;
        transition: width .18s ease, background .18s ease;
    }

    .security-password-strength[data-score="1"] .security-password-strength__bar { width: 25%; background: #dc2626; }
    .security-password-strength[data-score="2"] .security-password-strength__bar { width: 50%; background: #d97706; }
    .security-password-strength[data-score="3"] .security-password-strength__bar { width: 75%; background: #0284c7; }
    .security-password-strength[data-score="4"] .security-password-strength__bar { width: 100%; background: #0f766e; }

    .security-password-requirements {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px 10px;
        margin: 0;
        padding: 0;
        list-style: none;
        color: #64748b;
        font-size: 11px;
    }

    .security-password-requirements li {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .security-password-requirements li::before {
        content: '○';
        color: #94a3b8;
        font-weight: 900;
    }

    .security-password-requirements li.is-met { color: #047857; }
    .security-password-requirements li.is-met::before { content: '✓'; color: #047857; }

    .security-method-locked {
        display: grid;
        gap: 6px;
        padding: 11px 12px;
        border: 1px solid #fde68a;
        border-radius: 9px;
        background: #fffbeb;
        color: #78570b;
        font-size: 12px;
        line-height: 1.5;
    }

    .security-method-locked strong { color: #92400e; }
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

        .security-password-requirements {
            grid-template-columns: 1fr;
        }
    }

    .security-email-sources {
        display: grid;
        gap: 10px;
        margin: 14px 0;
    }

    .security-email-source {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 14px;
        border: 1px solid #e5eaf1;
        border-radius: 12px;
        background: #f8fafc;
    }

    .security-email-source > div {
        min-width: 0;
        display: grid;
        gap: 3px;
    }

    .security-email-source strong {
        color: #172033;
        font-size: 13px;
    }

    .security-email-source span {
        overflow-wrap: anywhere;
        color: #475569;
        font-size: 13px;
    }

    .security-email-source small {
        flex: 0 0 auto;
        color: #0f766e;
        font-size: 12px;
        font-weight: 700;
        text-align: right;
    }

    @media (max-width: 520px) {
        .security-email-source {
            align-items: flex-start;
            flex-direction: column;
        }

        .security-email-source small {
            text-align: left;
        }
    }


    .security-email-source-popup[hidden] { display: none !important; }
    .security-email-source-popup {
        position: fixed; inset: 0; z-index: 9998;
        display: grid; place-items: center; padding: 20px;
    }
    .security-email-source-popup__backdrop {
        position: absolute; inset: 0; background: rgba(15, 23, 42, 0.58);
    }
    .security-email-source-popup__dialog {
        position: relative; z-index: 1; width: min(100%, 460px);
        padding: 28px; border-radius: 14px; background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24); text-align: center;
    }
    .security-email-source-popup__dialog h3 { margin: 0 0 8px; color: #172033; }
    .security-email-source-popup__dialog > p {
        margin: 0 0 20px; color: #64748b; font-size: 13px; line-height: 1.6;
    }
    .security-email-source-popup__close {
        position: absolute; top: 12px; right: 12px; width: 34px; height: 34px;
        display: grid; place-items: center; border: 1px solid #e5eaf1;
        border-radius: 9px; background: #fff; color: #475569; cursor: pointer;
    }
    .security-email-source-popup__options { display: grid; gap: 10px; }
    .security-email-source-popup__options form,
    .security-email-source-popup__options .security-button { width: 100%; }

</style>
@endpush


@section('content')

@php
$user = $user ?? auth()->user();
$canDisconnectEmail = $canDisconnectEmail ?? false;
$canRemovePhone = $canRemovePhone ?? false;
$canDisconnectGoogle = $canDisconnectGoogle ?? false;
$canDisconnectFacebook = $canDisconnectFacebook ?? false;

/*
|--------------------------------------------------------------------------
| Social provider display state
|--------------------------------------------------------------------------
|
| The security page must reflect the provider identifiers actually stored
| on the current database-backed User model. Do not infer connection state
| from registration_method, the current session, or an old OAuth login.
|
*/
$googleConnected = filled($user->google_id);
$facebookConnected = filled($user->facebook_id);
@endphp


<header class="customer-page-heading">

    <button
        type="button"
        data-security-back-button
        aria-label="Go back"
        title="Back"
        style="
            flex:0 0 34px;
            width:34px;
            height:34px;
            display:inline-grid;
            place-items:center;
            padding:0;
            margin:1px 2px 0 0;
            border:1px solid #e5eaf1;
            border-radius:8px;
            background:#fff;
            color:#475569;
            cursor:pointer;
            box-shadow:none;
        ">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    </button>

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

    <div class="security-grid">


        {{-- ====================================================== --}}
        {{-- EMAIL --}}
        {{-- ====================================================== --}}

        @php
            $emailState = $emailIdentityState ?? [
                'connected' => false,
                'sources' => [],
                'primary_email' => null,
                'custom_connected' => false,
                'custom_verified' => false,
                'custom_verification_pending' => false,
                'google_connected' => false,
                'facebook_connected' => false,
                'can_resend_custom_verification' => false,
                'disconnectable_sources' => [],
            ];

            $emailSources = $emailState['sources'] ?? [];
            $emailSourceLabels = [
                'custom' => 'Custom Email',
                'google' => 'Google',
                'facebook' => 'Facebook',
            ];
        @endphp

        <section class="security-card">

            <h3>
                <i class="fa-solid fa-envelope"></i>
                Email
            </h3>

            @if ($emailState['connected'])

                <div class="security-status">
                    <i class="fa-solid fa-circle-check"></i>
                    Connected
                </div>

                <div class="security-email-sources">
                    @foreach ($emailSources as $sourceKey => $source)
                        <div class="security-email-source">
                            <div>
                                <strong>{{ $emailSourceLabels[$sourceKey] ?? ucfirst($sourceKey) }}</strong>

                                @if (filled($source['email'] ?? null))
                                    <span>{{ $source['email'] }}</span>
                                @else
                                    <span>Email not supplied by provider</span>
                                @endif
                            </div>

                            <small>
                                @if ($sourceKey === 'custom')
                                    @if ($source['verified'] ?? false)
                                        Verified
                                    @elseif ($source['verification_pending'] ?? false)
                                        Verification pending
                                    @else
                                        Connected
                                    @endif
                                @else
                                    Provider authenticated
                                @endif
                            </small>
                        </div>
                    @endforeach
                </div>

                @if ($emailState['custom_verification_pending'])
                    <div class="security-warning">
                        <h3>Custom email verification pending</h3>

                        <p>
                            Verify your custom email before it can be used
                            as an Arizona Outfits email login.
                        </p>

                        @if ($emailState['can_resend_custom_verification'])
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf

                                <button type="submit" class="security-button">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    Resend verification email
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                @if ($emailState['custom_connected'] && $emailState['custom_verified'])

                    @if (filled($user->pending_email))
                        <div class="security-warning">
                            <h3>New email verification pending</h3>

                            <p>{{ $user->pending_email }}</p>

                            <p>
                                Your current custom email login remains active
                                until this new address is verified.
                            </p>

                            <form
                                method="POST"
                                action="{{ route('customer.security.email.change.cancel') }}"
                                data-security-confirm-form
                                data-confirm-title="Cancel email change?"
                                data-confirm-message="Cancel the pending email change? Your current custom email login will remain active."
                                data-confirm-button="Cancel email change">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="security-button">
                                    Cancel email change
                                </button>
                            </form>
                        </div>
                    @else
                        <form
                            method="POST"
                            action="{{ route('customer.security.email.change') }}"
                            class="security-form"
                            data-security-confirm-form
                            data-confirm-title="Verify email change"
                            data-confirm-message="Enter your current password to continue changing your custom email address."
                            data-confirm-button="Verify & continue"
                            data-require-password="true">

                            @csrf

                            <label class="security-field">
                                <span>New custom email address</span>

                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    maxlength="255"
                                    autocomplete="email"
                                    placeholder="New email address">
                            </label>

                            <button type="submit" class="security-button">
                                <i class="fa-solid fa-envelope-circle-check"></i>
                                Change custom email
                            </button>
                        </form>
                    @endif

                @elseif (! $emailState['custom_connected'])

                    <div class="security-note">
                        Google/Facebook email does not automatically create a
                        local email/password login. You can add a separate
                        custom email login below.
                    </div>

                    <form
                        method="POST"
                        action="{{ route('customer.security.email') }}"
                        class="security-form">

                        @csrf

                        <label class="security-field">
                            <span>Custom email address</span>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                maxlength="255"
                                autocomplete="email"
                                placeholder="Email address">
                        </label>

                        @unless ($user->hasPassword())
                            <label class="security-field">
                                <span>Create password</span>

                                <input
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Create password">
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
                        @endunless

                        <button type="submit" class="security-button">
                            <i class="fa-solid fa-envelope-circle-check"></i>
                            Connect custom email
                        </button>
                    </form>
                @endif

                @php
                    $hasDisconnectableEmailSource =
                        ($emailState['custom_connected'] && $canDisconnectEmail)
                        || ($emailState['google_connected'] && $canDisconnectGoogle)
                        || ($emailState['facebook_connected'] && $canDisconnectFacebook);
                @endphp

                @if ($hasDisconnectableEmailSource)
                    <button type="button" class="security-button" data-email-source-popup-open>
                        <i class="fa-solid fa-link-slash"></i>
                        Disconnect email
                    </button>
                @else
                    <div class="security-method-locked">
                        <strong><i class="fa-solid fa-lock"></i> Email source protected</strong>
                        <span>At least one usable login method must remain connected.</span>
                    </div>
                @endif

            @else

                <div class="security-status">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    Not connected
                </div>

                <p>
                    Add a custom email login or connect Google/Facebook below.
                </p>

                <form
                    method="POST"
                    action="{{ route('customer.security.email') }}"
                    class="security-form">

                    @csrf

                    <label class="security-field">
                        <span>Custom email address</span>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            maxlength="255"
                            autocomplete="email"
                            placeholder="Email address">
                    </label>

                    @unless ($user->hasPassword())
                        <label class="security-field">
                            <span>Create password</span>

                            <input
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Create password">
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
                    @endunless

                    <button type="submit" class="security-button">
                        <i class="fa-solid fa-envelope-circle-check"></i>
                        Connect custom email
                    </button>
                </form>

            @endif

        </section>


        {{-- ====================================================== --}}
        {{-- PASSWORD --}}
        {{-- ====================================================== --}}

        <section class="security-card">

            <h3>
                <i class="fa-solid fa-key"></i>
                Password
            </h3>

            @if ($user->hasPassword())

            <div class="security-status">
                <i class="fa-solid fa-circle-check"></i>
                Password enabled
            </div>

            <p>
                Change your password securely. Your current password is
                required before a new password can be saved.
            </p>

            @else

            <div class="security-status">
                <i class="fa-solid fa-circle-exclamation"></i>
                No password created
            </div>

            <p>
                Create a password as an additional secure way to access
                your account. Your existing connected login methods will
                remain available.
            </p>

            @endif

            <form
                method="POST"
                action="{{ route('customer.security.password.update') }}"
                class="security-form"
                data-password-security-form>

                @csrf

                @if ($user->hasPassword())
                <label class="security-field">
                    <span>Current password</span>

                    <div class="security-password-wrap">
                        <input
                            type="password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            placeholder="Current password"
                            data-password-input>

                        <button
                            type="button"
                            class="security-password-toggle"
                            aria-label="Show current password"
                            data-password-toggle>
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>

                    @error('current_password')
                    <small>{{ $message }}</small>
                    @enderror
                </label>
                @endif

                <label class="security-field">
                    <span>New password</span>

                    <div class="security-password-wrap">
                        <input
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="New password"
                            data-new-password
                            data-password-input>

                        <button
                            type="button"
                            class="security-password-toggle"
                            aria-label="Show new password"
                            data-password-toggle>
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>

                    @error('password')
                    <small>{{ $message }}</small>
                    @enderror

                    <div
                        class="security-password-strength"
                        data-password-strength
                        data-score="0"
                        aria-live="polite">
                        <div class="security-password-strength__head">
                            <span>Password strength</span>
                            <strong data-password-strength-label>Start typing</strong>
                        </div>
                        <div class="security-password-strength__track" aria-hidden="true">
                            <div class="security-password-strength__bar"></div>
                        </div>
                        <ul class="security-password-requirements">
                            <li data-password-rule="length">At least 8 characters</li>
                            <li data-password-rule="upper">Uppercase letter</li>
                            <li data-password-rule="lower">Lowercase letter</li>
                            <li data-password-rule="number">Number</li>
                            <li data-password-rule="symbol">Symbol</li>
                            <li data-password-rule="space">No leading/trailing spaces</li>
                        </ul>
                    </div>
                </label>

                <label class="security-field">
                    <span>Confirm new password</span>

                    <div class="security-password-wrap">
                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm new password"
                            data-password-confirmation
                            data-password-input>

                        <button
                            type="button"
                            class="security-password-toggle"
                            aria-label="Show password confirmation"
                            data-password-toggle>
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </label>

                <div class="security-password-help">
                    <strong>Strong password protection</strong>
                    <span>
                        Use a long, unique password. Your current password
                        and immediately previous password cannot be reused.
                    </span>
                </div>

                <p
                    class="security-password-generated"
                    data-generated-password
                    role="status"
                    aria-live="polite"></p>

                <div class="security-password-actions">
                    <button
                        type="button"
                        class="security-button"
                        data-generate-password>
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        Generate strong password
                    </button>

                    <button
                        type="submit"
                        class="security-button">
                        <i class="fa-solid fa-shield-halved"></i>
                        {{ $user->hasPassword()
                            ? 'Change password'
                            : 'Create password' }}
                    </button>
                </div>

            </form>

            @if ($user->hasPassword())
            <div class="security-password-recovery">
                <p class="security-note">
                    Forgot your current password?
                    @if (filled($user->email))
                        Send secure reset instructions to
                        {{ $user->email }}.
                    @else
                        Add an email address to your account before using
                        password recovery.
                    @endif
                </p>

                <form
                    method="POST"
                    action="{{ route(
                        'customer.security.password.recovery'
                    ) }}"
                    data-security-confirm-form
                    data-confirm-title="Send password reset email?"
                    data-confirm-message="We will send password reset instructions to your account email address."
                    data-confirm-button="Send reset email">

                    @csrf

                    <button
                        type="submit"
                        class="security-button"
                        @disabled(! filled($user->email))>
                        <i class="fa-solid fa-envelope-open-text"></i>
                        Forgot password?
                    </button>
                </form>

                @error('password_recovery')
                <small class="security-field-error">{{ $message }}</small>
                @enderror
            </div>
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


            @if ($canRemovePhone)

            <form
                method="POST"
                action="{{ route('customer.security.phone.remove') }}"
                data-security-confirm-form
                data-confirm-title="Remove phone?"
                data-confirm-message="Remove this verified phone number from your account?"
                data-confirm-button="Remove phone"
                data-require-password="{{ $user->hasPassword() ? 'true' : 'false' }}">

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

            <div class="security-method-locked">
                <strong><i class="fa-solid fa-lock"></i> Phone protected</strong>
                <span>
                    This phone number cannot be removed while it is required to keep your account accessible.
                    Add another usable login method first.
                </span>
            </div>

            @endif


            <p class="security-note">
                Changing this verified phone requires your current password
                first. The replacement number must then pass OTP verification
                before your current number is replaced.
            </p>

            @if ($user->hasPassword())

            <form
                method="POST"
                action="{{ route('customer.security.phone.send') }}"
                class="security-form"
                data-security-confirm-form
                data-confirm-title="Verify phone change"
                data-confirm-message="Enter your current password to continue changing your verified phone number."
                data-confirm-button="Verify & send code"
                data-require-password="true">

                @csrf

                <label class="security-field">
                    <span>New phone number</span>

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

                @error('current_password')
                <small class="security-field-error">{{ $message }}</small>
                @enderror

                <button type="submit" class="security-button">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    Change phone
                </button>
            </form>

            @else

            <div class="security-method-locked">
                <strong>
                    <i class="fa-solid fa-shield-halved"></i>
                    Re-verification required
                </strong>
                <span>
                    Create a password before changing this verified phone.
                    Passwordless provider/phone re-verification will be handled
                    separately so this security check cannot be bypassed.
                </span>
            </div>

            @endif

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

                @if ($googleConnected)

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


                    @if ($canDisconnectGoogle)

                    <form
                        method="POST"
                        action="{{ route(
                            'customer.security.social.disconnect',
                            ['provider' => 'google']
                        ) }}"
                        data-security-confirm-form
                        data-confirm-title="Disconnect Google?"
                        data-confirm-message="Disconnect Google from your account?"
                        data-confirm-button="Disconnect Google"
                        data-require-password="{{ $user->hasPassword() ? 'true' : 'false' }}">

                        @csrf
                        @method('DELETE')


                        <button
                            type="submit"
                            class="security-button">

                            <i class="fa-solid fa-link-slash"></i>

                            Disconnect Google

                        </button>

                    </form>

                    @else

                    <div class="security-method-locked">
                        <strong><i class="fa-solid fa-lock"></i> Google protected</strong>
                        <span>
                            Google cannot be disconnected because it is currently required to keep your account accessible.
                            Add another usable login method first.
                        </span>
                    </div>

                    @endif

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

                @if ($facebookConnected)

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


                    @if ($canDisconnectFacebook)

                    <form
                        method="POST"
                        action="{{ route(
                            'customer.security.social.disconnect',
                            ['provider' => 'facebook']
                        ) }}"
                        data-security-confirm-form
                        data-confirm-title="Disconnect Facebook?"
                        data-confirm-message="Disconnect Facebook from your account?"
                        data-confirm-button="Disconnect Facebook"
                        data-require-password="{{ $user->hasPassword() ? 'true' : 'false' }}">

                        @csrf
                        @method('DELETE')


                        <button
                            type="submit"
                            class="security-button">

                            <i class="fa-solid fa-link-slash"></i>

                            Disconnect Facebook

                        </button>

                    </form>

                    @else

                    <div class="security-method-locked">
                        <strong><i class="fa-solid fa-lock"></i> Facebook protected</strong>
                        <span>
                            Facebook cannot be disconnected because it is currently required to keep your account accessible.
                            Add another usable login method first.
                        </span>
                    </div>

                    @endif

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

    {{-- ====================================================== --}}
    {{-- REUSABLE SECURITY CONFIRMATION POPUP --}}
    {{-- ====================================================== --}}

    <div
        id="email-source-popup"
        class="security-email-source-popup"
        hidden
        role="dialog"
        aria-modal="true"
        aria-labelledby="email-source-popup-title">

        <div class="security-email-source-popup__backdrop" data-email-source-popup-close></div>

        <div class="security-email-source-popup__dialog">
            <button type="button" class="security-email-source-popup__close" data-email-source-popup-close aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <h3 id="email-source-popup-title">Disconnect email source</h3>
            <p>Choose the connected source you want to disconnect. Other login methods and email sources will remain unchanged.</p>

            <div class="security-email-source-popup__options">
                @if ($emailState['custom_connected'] && $canDisconnectEmail)
                    <form method="POST"
                          action="{{ route('customer.security.email.disconnect') }}"
                          data-security-confirm-form
                          data-email-source-disconnect-form
                          data-confirm-title="Disconnect Custom Email?"
                          data-confirm-message="Disconnect your custom email login? Other connected login methods will remain available."
                          data-confirm-button="Disconnect Custom Email"
                          data-require-password="{{ $user->hasPassword() ? 'true' : 'false' }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="security-button">
                            <i class="fa-solid fa-envelope"></i> Custom Email
                        </button>
                    </form>
                @endif

                @if ($emailState['google_connected'] && $canDisconnectGoogle)
                    <form method="POST"
                          action="{{ route('customer.security.social.disconnect', ['provider' => 'google']) }}"
                          data-security-confirm-form
                          data-email-source-disconnect-form
                          data-confirm-title="Disconnect Google?"
                          data-confirm-message="Disconnect Google from your account? Other connected login methods will remain available."
                          data-confirm-button="Disconnect Google"
                          data-require-password="{{ $user->hasPassword() ? 'true' : 'false' }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="security-button">
                            <i class="fa-brands fa-google"></i> Google
                        </button>
                    </form>
                @endif

                @if ($emailState['facebook_connected'] && $canDisconnectFacebook)
                    <form method="POST"
                          action="{{ route('customer.security.social.disconnect', ['provider' => 'facebook']) }}"
                          data-security-confirm-form
                          data-email-source-disconnect-form
                          data-confirm-title="Disconnect Facebook?"
                          data-confirm-message="Disconnect Facebook from your account? Other connected login methods will remain available."
                          data-confirm-button="Disconnect Facebook"
                          data-require-password="{{ $user->hasPassword() ? 'true' : 'false' }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="security-button">
                            <i class="fa-brands fa-facebook-f"></i> Facebook
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div
        id="security-confirm-popup"
        class="security-confirm-popup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="security-confirm-title"
        aria-describedby="security-confirm-message"
        hidden>

        <div class="security-confirm-popup__backdrop" data-security-confirm-close></div>

        <div class="security-confirm-popup__dialog" role="document">
            <button
                type="button"
                class="security-confirm-popup__close"
                data-security-confirm-close
                aria-label="Close confirmation">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>

            <div class="security-confirm-popup__icon" aria-hidden="true">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <h3 id="security-confirm-title">Confirm action</h3>
            <p id="security-confirm-message">Are you sure you want to continue?</p>

            <div
                id="security-confirm-password-wrap"
                class="security-confirm-password"
                hidden>

                <label for="security-confirm-password">
                    Current password
                </label>

                <input
                    type="password"
                    id="security-confirm-password"
                    autocomplete="current-password"
                    maxlength="255"
                    placeholder="Enter your current password">

                <small>
                    Required before changing a login method.
                </small>

            </div>

            <div class="security-confirm-popup__actions">
                <button type="button" class="security-confirm-cancel" data-security-confirm-close>
                    Cancel
                </button>

                <button type="button" class="security-confirm-submit" id="security-confirm-submit">
                    Confirm
                </button>
            </div>
        </div>
    </div>

</div>

<style>
    .security-confirm-popup[hidden] {
        display: none !important;
    }

    .security-confirm-popup {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .security-confirm-popup__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(3px);
    }

    .security-confirm-popup__dialog {
        position: relative;
        z-index: 1;
        width: min(100%, 460px);
        padding: 34px 30px 30px;
        border: 1px solid #e5eaf1;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
        text-align: center;
    }

    .security-confirm-popup__close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 50%;
        background: #f1f5f9;
        color: #172033;
        cursor: pointer;
    }

    .security-confirm-popup__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin-bottom: 18px;
        border-radius: 50%;
        background: #ccfbf1;
        color: #0f766e;
        font-size: 23px;
    }

    .security-confirm-popup__dialog h3 {
        margin: 0 0 10px;
        color: #172033;
        font-size: 22px;
        line-height: 1.25;
    }

    .security-confirm-popup__dialog p {
        margin: 0 auto;
        max-width: 360px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.65;
    }

    .security-confirm-popup__actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 26px;
        flex-wrap: wrap;
    }

    .security-confirm-cancel,
    .security-confirm-submit {
        min-width: 140px;
        min-height: 44px;
        padding: 11px 22px;
        border-radius: 100px;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .security-confirm-cancel {
        border: 1px solid #dbe3ec;
        background: #ffffff;
        color: #172033;
    }

    .security-confirm-submit {
        border: 1px solid #0f766e;
        background: #0f766e;
        color: #ffffff;
    }

    .security-confirm-password {
        width: 100%;
        margin: 18px 0 4px;
        text-align: left;
    }

    .security-confirm-password[hidden] {
        display: none;
    }

    .security-confirm-password label {
        display: block;
        margin-bottom: 7px;
        color: #172033;
        font-size: 13px;
        font-weight: 700;
    }

    .security-confirm-password input {
        width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #d8e0e8;
        border-radius: 10px;
        background: #ffffff;
        color: #172033;
        font: inherit;
        outline: none;
        box-sizing: border-box;
    }

    .security-confirm-password input:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.10);
    }

    .security-confirm-password small {
        display: block;
        margin-top: 6px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.45;
    }

    .security-confirm-cancel:hover,
    .security-confirm-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.10);
    }

    @media (max-width: 520px) {
        .security-confirm-popup__dialog {
            padding: 32px 20px 24px;
        }

        .security-confirm-popup__actions {
            flex-direction: column-reverse;
        }

        .security-confirm-cancel,
        .security-confirm-submit {
            width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const backButton = document.querySelector(
        '[data-security-back-button]'
    );

    if (backButton) {
        backButton.addEventListener('click', function () {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }

            window.location.href = @json(
                route('customer.dashboard')
            );
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const passwordForm = document.querySelector('[data-password-security-form]');

    if (passwordForm) {
        passwordForm.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const wrap = button.closest('.security-password-wrap');
                const input = wrap ? wrap.querySelector('[data-password-input]') : null;

                if (!input) {
                    return;
                }

                const reveal = input.type === 'password';
                input.type = reveal ? 'text' : 'password';

                const icon = button.querySelector('i');
                button.setAttribute(
                    'aria-label',
                    reveal ? 'Hide password' : 'Show password'
                );

                if (icon) {
                    icon.classList.toggle('fa-eye', !reveal);
                    icon.classList.toggle('fa-eye-slash', reveal);
                }
            });
        });

        const generateButton = passwordForm.querySelector('[data-generate-password]');
        const passwordInput = passwordForm.querySelector('[data-new-password]');
        const confirmationInput = passwordForm.querySelector('[data-password-confirmation]');
        const generatedOutput = passwordForm.querySelector('[data-generated-password]');
        const strengthBox = passwordForm.querySelector('[data-password-strength]');
        const strengthLabel = passwordForm.querySelector('[data-password-strength-label]');

        function updatePasswordStrength() {
            if (!passwordInput || !strengthBox || !strengthLabel) {
                return;
            }

            const value = passwordInput.value;
            const checks = {
                length: value.length >= 8,
                upper: /[A-Z]/.test(value),
                lower: /[a-z]/.test(value),
                number: /[0-9]/.test(value),
                symbol: /[^A-Za-z0-9\s]/.test(value),
                space: value.length > 0 && value === value.trim(),
            };

            Object.keys(checks).forEach(function (rule) {
                const item = strengthBox.querySelector('[data-password-rule="' + rule + '"]');
                if (item) {
                    item.classList.toggle('is-met', checks[rule]);
                }
            });

            if (!value) {
                strengthBox.dataset.score = '0';
                strengthLabel.textContent = 'Start typing';
                return;
            }

            const met = Object.values(checks).filter(Boolean).length;
            let score = 1;
            let label = 'Weak';

            if (met >= 4 && value.length >= 8) { score = 2; label = 'Fair'; }
            if (met >= 5 && value.length >= 10) { score = 3; label = 'Good'; }
            if (met === 6 && value.length >= 12) { score = 4; label = 'Strong'; }

            strengthBox.dataset.score = String(score);
            strengthLabel.textContent = label;
        }

        if (passwordInput) {
            passwordInput.addEventListener('input', updatePasswordStrength);
            updatePasswordStrength();
        }

        if (
            generateButton
            && passwordInput
            && confirmationInput
            && generatedOutput
            && window.crypto
            && typeof window.crypto.getRandomValues === 'function'
        ) {
            generateButton.addEventListener('click', function () {
                const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
                const lower = 'abcdefghijkmnopqrstuvwxyz';
                const digits = '23456789';
                const symbols = '!@#$%^&*()-_=+';
                const all = upper + lower + digits + symbols;

                function secureIndex(length) {
                    const values = new Uint32Array(1);
                    const max = Math.floor(4294967296 / length) * length;
                    let value;

                    do {
                        window.crypto.getRandomValues(values);
                        value = values[0];
                    } while (value >= max);

                    return value % length;
                }

                const characters = [
                    upper[secureIndex(upper.length)],
                    lower[secureIndex(lower.length)],
                    digits[secureIndex(digits.length)],
                    symbols[secureIndex(symbols.length)],
                ];

                while (characters.length < 20) {
                    characters.push(all[secureIndex(all.length)]);
                }

                for (let index = characters.length - 1; index > 0; index -= 1) {
                    const swapIndex = secureIndex(index + 1);
                    const temporary = characters[index];
                    characters[index] = characters[swapIndex];
                    characters[swapIndex] = temporary;
                }

                const generatedPassword = characters.join('');

                passwordInput.value = generatedPassword;
                confirmationInput.value = generatedPassword;
                passwordInput.type = 'text';
                confirmationInput.type = 'text';

                generatedOutput.textContent =
                    'Strong password generated and filled into both password fields. Save it in your password manager before continuing.';
                generatedOutput.classList.add('is-visible');
                updatePasswordStrength();

                passwordInput.focus();
            });
        }
    }

    const popup = document.getElementById('security-confirm-popup');
    const title = document.getElementById('security-confirm-title');
    const message = document.getElementById('security-confirm-message');
    const submitButton = document.getElementById('security-confirm-submit');
    const passwordWrap = document.getElementById('security-confirm-password-wrap');
    const passwordInput = document.getElementById('security-confirm-password');
    const forms = document.querySelectorAll('[data-security-confirm-form]');

    if (!popup || !title || !message || !submitButton || !forms.length) {
        return;
    }

    let pendingForm = null;
    let triggerButton = null;

    const closeButtons = popup.querySelectorAll('[data-security-confirm-close]');
    const focusableSelector = 'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function openPopup(form) {
        pendingForm = form;
        triggerButton = document.activeElement;

        title.textContent = form.dataset.confirmTitle || 'Confirm action';
        message.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue?';
        submitButton.textContent = form.dataset.confirmButton || 'Confirm';
        delete submitButton.dataset.closeOnly;

        const requiresPassword =
            form.dataset.requirePassword === 'true';

        if (passwordWrap && passwordInput) {
            passwordWrap.hidden = !requiresPassword;
            passwordInput.value = '';
        }

        popup.hidden = false;
        document.body.style.overflow = 'hidden';

        if (requiresPassword && passwordInput) {
            passwordInput.focus();
        } else {
            submitButton.focus();
        }
    }

    function closePopup() {
        popup.hidden = true;
        document.body.style.overflow = '';
        pendingForm = null;

        if (passwordInput) {
            passwordInput.value = '';
        }

        if (passwordWrap) {
            passwordWrap.hidden = true;
        }

        if (triggerButton && typeof triggerButton.focus === 'function') {
            triggerButton.focus();
        }

        triggerButton = null;
    }

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            openPopup(form);
        });
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closePopup);
    });

    submitButton.addEventListener('click', function () {
        if (submitButton.dataset.closeOnly === 'true') {
            delete submitButton.dataset.closeOnly;
            closePopup();
            return;
        }

        if (!pendingForm) {
            return;
        }

        const form = pendingForm;
        const requiresPassword =
            form.dataset.requirePassword === 'true';

        /*
         * Passwordless accounts need provider/phone re-verification.
         * Do not allow the existing authenticated session alone to authorize
         * removal of a login method.
         */
        if (
            form.hasAttribute('data-require-password') &&
            form.dataset.requirePassword === 'false'
        ) {
            title.textContent = 'Re-verification required';
            message.textContent =
                'This account has no ArizonaOutfits password. Re-verification through a connected login method is required before this security setting can be changed.';

            if (passwordWrap) {
                passwordWrap.hidden = true;
            }

            submitButton.textContent = 'Close';
            submitButton.dataset.closeOnly = 'true';
            pendingForm = null;
            return;
        }

        if (requiresPassword) {
            const password =
                passwordInput
                    ? passwordInput.value
                    : '';

            if (!password) {
                if (passwordInput) {
                    passwordInput.focus();
                }

                return;
            }

            let hiddenPassword =
                form.querySelector(
                    'input[name="current_password"]'
                );

            if (!hiddenPassword) {
                hiddenPassword =
                    document.createElement('input');

                hiddenPassword.type = 'hidden';
                hiddenPassword.name = 'current_password';
                form.appendChild(hiddenPassword);
            }

            hiddenPassword.value = password;
        }

        form.dataset.confirmed = 'true';
        popup.hidden = true;
        document.body.style.overflow = '';
        form.requestSubmit();
    });

    document.addEventListener('keydown', function (event) {
        if (popup.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closePopup();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusable = Array.from(popup.querySelectorAll(focusableSelector))
            .filter(function (element) {
                return element.offsetParent !== null;
            });

        if (!focusable.length) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const openButton = document.querySelector('[data-email-source-popup-open]');
    const popup = document.getElementById('email-source-popup');

    if (!openButton || !popup) {
        return;
    }

    const closeButtons = popup.querySelectorAll('[data-email-source-popup-close]');
    const disconnectForms = popup.querySelectorAll('[data-email-source-disconnect-form]');

    function openPopup() {
        popup.hidden = false;
        document.body.style.overflow = 'hidden';
        const firstButton = popup.querySelector('.security-email-source-popup__options button');
        if (firstButton) firstButton.focus();
    }

    function closePopup() {
        popup.hidden = true;
        document.body.style.overflow = '';
        openButton.focus();
    }

    openButton.addEventListener('click', openPopup);
    closeButtons.forEach(function (button) {
        button.addEventListener('click', closePopup);
    });

    disconnectForms.forEach(function (form) {
        form.addEventListener('submit', function () {
            popup.hidden = true;
            document.body.style.overflow = '';
        });
    });

    document.addEventListener('keydown', function (event) {
        if (!popup.hidden && event.key === 'Escape') {
            event.preventDefault();
            closePopup();
        }
    });
});

</script>

@endsection