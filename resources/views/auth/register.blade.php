@extends('layouts.app')

@section('title', 'Create Customer Account')

@include('auth.partials.frontend-styles')

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span>Customer account</span>
        <h1>Create account</h1>
        <p>Register to keep your order history, invoices and profile together.</p>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="auth-field"><label for="name">Full name</label><input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">@error('name')<p class="auth-error">{{ $message }}</p>@enderror</div>
            <div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
            <div class="auth-field"><label for="phone">Phone number <small>(optional)</small></label><input id="phone" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel">@error('phone')<p class="auth-error">{{ $message }}</p>@enderror</div>
            <div class="auth-field"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="new-password">@error('password')<p class="auth-error">{{ $message }}</p>@enderror</div>
            <div class="auth-field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
            <div class="auth-row"><a href="{{ route('login') }}">Already registered?</a><button class="auth-submit" type="submit">Create account</button></div>
        </form>
    </div>
</section>
@endsection
