@extends('layouts.app')

@section('title', 'Customer Login')

@include('auth.partials.frontend-styles')

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span>Customer account</span>
        <h1>Welcome back</h1>
        <p>Log in to review your orders, download invoices and manage your profile.</p>

        @if (session('status'))<div class="auth-status">{{ session('status') }}</div>@endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="auth-field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')<p class="auth-error">{{ $message }}</p>@enderror
            </div>
            <div class="auth-field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
                @error('password')<p class="auth-error">{{ $message }}</p>@enderror
            </div>
            <label class="auth-check"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <div class="auth-row">
                @if(Route::has('password.request'))<a href="{{ route('password.request') }}">Forgot your password?</a>@endif
                <button class="auth-submit" type="submit">Log in</button>
            </div>
        </form>
        @if(Route::has('register'))<p class="auth-switch">New customer? <a href="{{ route('register') }}">Create an account</a></p>@endif
    </div>
</section>
@endsection
