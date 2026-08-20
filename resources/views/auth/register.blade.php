@extends('layouts.app')
@section('title', 'Create Customer Account')
@include('auth.partials.frontend-styles')
@section('content')
<section class="auth-page">
@include('auth.partials.visual-copy', ['heading' => 'Your wardrobe, your way.', 'message' => 'Create an account for faster checkout, order tracking and a shopping experience built around you.'])
<div class="auth-card auth-card-scroll">
<span>Customer account</span><h1>Create account</h1><p>Register to keep your order history, invoices and profile together.</p>
<form method="POST" action="{{ route('register') }}">@csrf
<div class="auth-field"><label for="name">Full name</label><input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">@error('name')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-field"><label for="phone">Phone number <small>(optional)</small></label><input id="phone" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel">@error('phone')<p class="auth-error">{{ $message }}</p>@enderror</div>
<div class="auth-field"><label for="password">Password</label><div class="auth-password-input"><input id="password" type="password" name="password" required autocomplete="new-password" aria-describedby="password-requirements password-strength-text"><button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>@error('password')<p class="auth-error">{{ $message }}</p>@enderror
<div class="auth-password-strength" aria-live="polite"><div class="auth-strength-track"><span id="password-strength-bar"></span></div><strong id="password-strength-text">Enter a password</strong></div>
<ul class="auth-password-rules" id="password-requirements"><li data-rule="length">10 or more characters</li><li data-rule="lower">One lowercase letter</li><li data-rule="upper">One uppercase letter</li><li data-rule="number">One number</li><li data-rule="symbol">One symbol</li></ul></div>
<div class="auth-field"><label for="password_confirmation">Confirm password</label><div class="auth-password-input"><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"><button type="button" class="auth-password-toggle" data-password-toggle="password_confirmation" aria-label="Show confirmed password"><i class="fa-regular fa-eye"></i></button></div><p class="auth-password-match" id="password-match" aria-live="polite"></p></div>
<div class="auth-row"><a href="{{ route('login') }}">Already registered?</a><button class="auth-submit" type="submit">Create account</button></div>
</form></div></section>
@include('auth.partials.password-tools')
@endsection
