@extends('layouts.app')
@section('title', 'Verify Email')
@include('auth.partials.frontend-styles')
@section('content')
<section class="auth-page"><div class="auth-card"><span>Account security</span><h1>Verify your email</h1><p>Open the verification message we sent to your email address and select its verification link.</p>
@if(session('status')==='verification-link-sent')<div class="auth-status">A fresh verification link has been sent to your email address.</div>@endif
<div class="auth-row"><form method="POST" action="{{ route('verification.send') }}">@csrf<button class="auth-submit" type="submit">Resend verification email</button></form><form method="POST" action="{{ route('logout') }}">@csrf<button class="auth-submit" type="submit">Log out</button></form></div>
</div></section>
@endsection
