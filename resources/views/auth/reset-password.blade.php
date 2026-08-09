@extends('layouts.app')
@section('title', 'Reset Password')
@include('auth.partials.frontend-styles')
@section('content')
<section class="auth-page"><div class="auth-card"><span>Account recovery</span><h1>Choose a new password</h1><p>Use a strong password that you do not use on another website.</p>
<form method="POST" action="{{ route('password.store') }}">@csrf<input type="hidden" name="token" value="{{ $request->route('token') }}"><div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email',$request->email) }}" required autofocus autocomplete="username">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div><div class="auth-field"><label for="password">New password</label><input id="password" type="password" name="password" required autocomplete="new-password">@error('password')<p class="auth-error">{{ $message }}</p>@enderror</div><div class="auth-field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div><div class="auth-row"><a href="{{ route('login') }}">Back to login</a><button class="auth-submit" type="submit">Reset password</button></div></form>
</div></section>
@endsection
