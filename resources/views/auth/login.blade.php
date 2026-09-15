@extends('layouts.app')
@section('title', 'Customer Login')
@include('auth.partials.frontend-styles')
@section('content')
<section class="auth-page">
    @include('auth.partials.visual-copy', ['heading' => 'Welcome back to your style.', 'message' => 'Sign in to follow orders, download invoices and keep your account details up to date.'])
    <div class="auth-card"><span>Customer account</span>
        <h1>Welcome back</h1>
        <p>Enter your details to continue to your account.</p>
        @if(session('status'))<div class="auth-status">{{ session('status') }}</div>@endif
        @include('auth.partials.social-login')
        <form method="POST" action="{{ route('login') }}">@csrf
            <div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
            <div class="auth-field"><label for="password">Password</label>
                <div class="auth-password-input"><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"><button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>@error('password')<p class="auth-error">{{ $message }}</p>@enderror
            </div>
            <div class="auth-login-options"><label class="auth-check"><input type="checkbox" name="remember" value="1"> Remember me</label>@if(Route::has('password.request'))<a href="{{ route('password.request') }}">Forgot password?</a>@endif</div>
            <button class="auth-submit auth-submit-wide" type="submit">Log in <i class="fa-solid fa-arrow-right"></i></button>
        </form>
        @if(Route::has('register'))<p class="auth-switch">New to Arizona Outfits? <a href="{{ route('register') }}">Create an account</a></p>@endif
    </div>
</section>
@push('page-scripts')<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-password-toggle]').forEach(function(button) {
            button.addEventListener('click', function() {
                const input = document.getElementById(button.dataset.passwordToggle);
                if (!input) return;
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                button.querySelector('i')?.classList.toggle('fa-eye', showing);
                button.querySelector('i')?.classList.toggle('fa-eye-slash', !showing);
            });
        });
    });
</script>@endpush
@endsection