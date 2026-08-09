@extends('layouts.app')
@section('title', 'Confirm Password')
@include('auth.partials.frontend-styles')
@section('content')
<section class="auth-page"><div class="auth-card"><span>Secure area</span><h1>Confirm your password</h1><p>Please confirm your password before continuing to this protected account area.</p>
<form method="POST" action="{{ route('password.confirm') }}">@csrf<div class="auth-field"><label for="password">Password</label><input id="password" type="password" name="password" required autofocus autocomplete="current-password">@error('password')<p class="auth-error">{{ $message }}</p>@enderror</div><div class="auth-row"><a href="{{ route('customer.dashboard') }}">Return to account</a><button class="auth-submit" type="submit">Confirm password</button></div></form>
</div></section>
@endsection
