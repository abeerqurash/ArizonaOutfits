@extends('layouts.app')
@section('title', 'Reset Password')
@include('auth.partials.frontend-styles')
@section('content')
<section class="auth-page">
@include('auth.partials.visual-copy', ['heading' => 'A fresh start, securely.', 'message' => 'Choose a new password and get back to your orders, invoices and saved account details.'])
<div class="auth-card auth-card-scroll"><span>Account recovery</span><h1>Choose a new password</h1><p>Use a strong password that you do not use on another website.</p>
<form method="POST" action="{{ route('password.store') }}">@csrf<input type="hidden" name="token" value="{{ $request->route('token') }}">
<div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email',$request->email) }}" required autofocus autocomplete="username">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-field"><label for="password">New password</label><div class="auth-password-input"><input id="password" type="password" name="password" required autocomplete="new-password" aria-describedby="password-requirements password-strength-text"><button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>@error('password')<p class="auth-error">{{ $message }}</p>@enderror
<div class="auth-password-strength" aria-live="polite"><div class="auth-strength-track"><span id="password-strength-bar"></span></div><strong id="password-strength-text">Enter a password</strong></div>
<ul class="auth-password-rules" id="password-requirements"><li data-rule="length">10 or more characters</li><li data-rule="lower">One lowercase letter</li><li data-rule="upper">One uppercase letter</li><li data-rule="number">One number</li><li data-rule="symbol">One symbol</li></ul></div>
<div class="auth-field"><label for="password_confirmation">Confirm new password</label><div class="auth-password-input"><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"><button type="button" class="auth-password-toggle" data-password-toggle="password_confirmation" aria-label="Show confirmed password"><i class="fa-regular fa-eye"></i></button></div><p class="auth-password-match" id="password-match" aria-live="polite"></p></div>
<div class="auth-row"><a href="{{ route('login') }}">Back to login</a><button class="auth-submit" type="submit">Reset password</button></div></form>
</div></section>
@include('auth.partials.password-tools')
@endsection
