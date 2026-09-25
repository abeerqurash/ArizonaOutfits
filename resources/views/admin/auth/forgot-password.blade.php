@extends('layouts.app')

@section('title', 'Administrator Password Recovery')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">
    @include('auth.partials.visual-copy', [
        'heading' => 'Recover administrator access.',
        'message' => 'Request a secure password reset link for your Arizona Outfits administrator account.'
    ])

    <div class="auth-card">
        <span>Administrator security</span>
        <h1>Forgot password?</h1>
        <p>Enter your administrator email address and we will send password reset instructions if the account is active.</p>

        <form method="POST" action="{{ route('admin.password.email') }}">
            @csrf

            <div class="auth-field">
                <label for="email">Administrator email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    maxlength="255"
                    autocomplete="email"
                    placeholder="admin@example.com"
                >
            </div>

            <button class="auth-submit auth-submit-wide" type="submit">
                Send reset instructions
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
        </form>

        <p class="auth-switch">
            Remembered your password?
            <a href="{{ route('admin.login') }}">Back to admin login</a>
        </p>
    </div>
</section>

@if ($errors->any() || session('status'))
    <div
        class="admin-auth-popup-layer is-open"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminAuthMessageTitle"
    >
        <div class="admin-auth-popup">
            <div class="admin-auth-popup-icon {{ $errors->any() ? 'admin-auth-popup-icon-error' : 'admin-auth-popup-icon-success' }}">
                <i class="fa-solid {{ $errors->any() ? 'fa-triangle-exclamation' : 'fa-circle-check' }}" aria-hidden="true"></i>
            </div>

            <h2 id="adminAuthMessageTitle">
                {{ $errors->any() ? 'Unable to send reset instructions' : 'Check your email' }}
            </h2>

            <p>{{ $errors->any() ? $errors->first() : session('status') }}</p>

            <button type="button" class="auth-submit auth-submit-wide" data-admin-auth-popup-close>
                Close
            </button>
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
.admin-auth-popup-icon-success{background:#ecfdf5;color:#047857}
.admin-auth-popup h2{margin:0 0 10px;color:#172033;font-size:21px;line-height:1.3}
.admin-auth-popup p{margin:0 0 20px;color:#64748b;font-size:13px;line-height:1.65}
</style>
@endpush

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
