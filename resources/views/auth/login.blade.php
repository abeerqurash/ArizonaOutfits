@extends('layouts.app')

@section('title', 'Customer Login')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">
    @include('auth.partials.visual-copy', [
        'heading' => 'Welcome back to your style.',
        'message' => 'Sign in to follow orders, download invoices and keep your account details up to date.'
    ])

    <div class="auth-card">
        <span>Customer account</span>
        <h1>Welcome back</h1>
        <p>Sign in with email, phone, Google or Facebook.</p>

        @if (session('status'))
            <div class="auth-status" role="status">{{ session('status') }}</div>
        @endif

        @if (session('social_error'))
            <div class="auth-social-error" role="alert">{{ session('social_error') }}</div>
        @endif

        @include('auth.partials.social-login')

        <a href="{{ route('phone.login') }}" class="auth-submit auth-submit-wide auth-method-button">
            <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
            Continue with phone
        </a>

        <div class="auth-email-divider" aria-hidden="true">
            <span></span><strong>Or use email</strong><span></span>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="auth-field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    required autofocus maxlength="255" autocomplete="username"
                    placeholder="you@example.com">
                @error('email')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password">Password</label>
                <div class="auth-password-input">
                    <input id="password" type="password" name="password" required
                        autocomplete="current-password" placeholder="Enter your password">
                    <button type="button" class="auth-password-toggle"
                        data-password-toggle="password" aria-label="Show password"
                        aria-controls="password">
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password')
                    <p class="auth-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-login-options">
                <label class="auth-check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Remember me
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <button class="auth-submit auth-submit-wide" type="submit">
                Log in
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
        </form>

        @if (Route::has('register'))
            <p class="auth-switch">
                New to Arizona Outfits?
                <a href="{{ route('register') }}">Create an account</a>
            </p>
        @endif
    </div>
</section>

@push('page-styles')
<style>
.auth-method-button{margin-bottom:20px;text-decoration:none}
.auth-email-divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:#94a3b8}
.auth-email-divider span{height:1px;flex:1;background:#e5eaf1}
.auth-email-divider strong{flex:0 0 auto;font-size:10px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}
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
});
</script>
@endpush

@endsection
