@extends('layouts.app')
@section('title', 'Forgot Password')
@include('auth.partials.frontend-styles')
@section('content')
<section class="auth-page"><div class="auth-card"><span>Account recovery</span><h1>Reset your password</h1><p>Enter your account email and we will send you a secure password-reset link.</p>
@if(session('status'))<div class="auth-status">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf<div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div><div class="auth-row"><a href="{{ route('login') }}">Back to login</a><button class="auth-submit" type="submit">Email reset link</button></div></form>
</div></section>
@endsection
