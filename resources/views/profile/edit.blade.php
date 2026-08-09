@extends('layouts.app')
@section('title', 'My Profile')
@include('customer.partials.styles')
@section('content')
<section class="customer-account"><div class="account-shell"><header class="account-heading"><div><small>Customer account</small><h1>My profile</h1><p>Manage your contact details, password and account settings.</p></div></header>@include('customer.partials.navigation')<div class="profile-stack"><section class="account-panel profile-block">@include('profile.partials.update-profile-information-form')</section><section class="account-panel profile-block">@include('profile.partials.update-password-form')</section><section class="account-panel profile-block">@include('profile.partials.delete-user-form')</section></div></div></section>
@endsection
