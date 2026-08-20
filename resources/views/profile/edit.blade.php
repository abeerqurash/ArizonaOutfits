@extends(auth()->user()?->is_admin ? 'admin.layouts.app' : 'customer.layouts.app')
@section('title', 'My Profile')
@section('page-heading', 'Profile & Security')
@push('page-styles')
    @include('customer.partials.styles')
@endpush
@section('content')
<header class="customer-page-heading"><div><span>Account settings</span><h2>My profile</h2><p>Manage your contact details, password and account security.</p></div></header>
<div class="customer-profile-grid"><section class="customer-panel customer-form-card">@include('profile.partials.update-profile-information-form')</section><section class="customer-panel customer-form-card">@include('profile.partials.update-password-form')</section><section class="customer-panel customer-form-card danger">@include('profile.partials.delete-user-form')</section></div>
@endsection
