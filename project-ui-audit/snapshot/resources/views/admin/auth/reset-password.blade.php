@extends('layouts.app')

@section('title', 'Reset Administrator Password')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">
    @include('auth.partials.visual-copy', [
        'heading' => 'Secure administrator recovery.',
        'message' => 'Choose a new password for your Arizona Outfits administrator account.'
    ])

    <div class="auth-card">
        <span>Administrator security</span>
        <h1>Reset password</h1>
        <p>Create a new secure administrator password.</p>

        <form method="POST" action="{{ route('admin.password.store') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="auth-field">
                <label for="email">Administrator email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    required
                    autofocus
                    maxlength="255"
                    autocomplete="username"
                >
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
                        placeholder="Enter new password"
                    >
                    <button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Show password" aria-controls="password">
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="auth-field">
                <label for="password_confirmation">Confirm new password</label>
                <div class="auth-password-input">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm new password"
                    >
                    <button type="button" class="auth-password-toggle" data-password-toggle="password_confirmation" aria-label="Show password" aria-controls="password_confirmation">
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button class="auth-submit auth-submit-wide" type="submit">
                Reset administrator password
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
        </form>

        <p class="auth-switch">
            Return to
            <a href="{{ route('admin.login') }}">admin login</a>
        </p>
    </div>
</section>

@if ($errors->any())
    <div class="admin-auth-popup-layer is-open" role="dialog" aria-modal="true" aria-labelledby="adminAuthErrorTitle">
        <div class="admin-auth-popup">
            <div class="admin-auth-popup-icon admin-auth-popup-icon-error">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            </div>
            <h2 id="adminAuthErrorTitle">Unable to reset password</h2>
            <p>{{ $errors->first() }}</p>
            <button type="button" class="auth-submit auth-submit-wide" data-admin-auth-popup-close>Try again</button>
        </div>
    </div>
@endif

@push('page-styles')
<style>
.admin-auth-popup-layer{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.62);backdrop-filter:blur(5px)}
.admin-auth-popup-layer.is-open{display:flex}
.admin-auth-popup{width:min(100%,440px);padding:30px 26px;border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(15,23,42,.24);text-align:center}
.admin-auth-popup-icon{display:grid;width:54px;height:54px;margin:0 auto 16px;place-items:center;border-radius:50%;font-size:21px}
.admin-auth-popup-icon-error{background:#fef2f2;color:#dc2626}
.admin-auth-popup h2{margin:0 0 10px;color:#172033;font-size:21px;line-height:1.3}
.admin-auth-popup p{margin:0 0 20px;color:#64748b;font-size:13px;line-height:1.65}
</style>
@endpush

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }
        });
    });

    document.querySelectorAll('[data-admin-auth-popup-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            const popup = button.closest('.admin-auth-popup-layer');
            if (popup) popup.classList.remove('is-open');
        });
    });
});
</script>
@endpush

@endsection
