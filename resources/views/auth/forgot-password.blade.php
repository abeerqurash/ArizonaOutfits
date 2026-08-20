@extends('layouts.app')
@section('title', 'Forgot Password')
@include('auth.partials.frontend-styles')
@section('content')
<section class="auth-page">
@include('auth.partials.visual-copy', ['heading' => 'We will help you back in.', 'message' => 'Account recovery is secure and simple. Your reset link will be sent only to your registered email.'])
<div class="auth-card"><span>Account recovery</span><div class="auth-heading-icon"><i class="fa-solid fa-key"></i></div><h1>Forgot password?</h1><p>Enter your account email and we will send you a secure password-reset link.</p>
@if(session('status'))<div class="auth-status">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf
<div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@example.com">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
<button class="auth-submit auth-submit-wide" type="submit">Send reset link <i class="fa-regular fa-paper-plane"></i></button>
</form><p class="auth-switch"><a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to login</a></p>
</div></section>
@endsection
