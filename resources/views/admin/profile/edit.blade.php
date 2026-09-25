@extends('admin.layouts.app')

@section('title', 'Admin Profile')
@section('page-heading', 'Edit Profile')

@section('content')
@php
    $profileErrors = $errors->getBag('default');
    $passwordErrors = $errors->getBag('updatePassword');
    $popupTitle = null;
    $popupMessage = null;
    $popupType = 'success';

    if (session('status')) {
        $popupTitle = 'Profile updated';
        $popupMessage = session('status');
    } elseif (session('password_status')) {
        $popupTitle = 'Password updated';
        $popupMessage = session('password_status');
    } elseif ($profileErrors->any()) {
        $popupTitle = 'Unable to update profile';
        $popupMessage = $profileErrors->first();
        $popupType = 'error';
    } elseif ($passwordErrors->any()) {
        $popupTitle = 'Unable to update password';
        $popupMessage = $passwordErrors->first();
        $popupType = 'error';
    }
@endphp

<div class="admin-profile-page">
    <div class="admin-profile-page-heading">
        <div>
            <span class="admin-profile-eyebrow">Administrator account</span>
            <h2>Profile & security</h2>
            <p>Manage your dedicated administrator account details and password.</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="admin-profile-back">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Dashboard</span>
        </a>
    </div>

    <div class="admin-profile-grid">
        <section class="admin-profile-card">
            <div class="admin-profile-card-heading">
                <span class="admin-profile-card-icon">
                    <i class="fa-regular fa-user"></i>
                </span>
                <div>
                    <h3>Administrator details</h3>
                    <p>This updates the dedicated admin account only.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="admin-profile-form">
                @csrf
                @method('PATCH')

                <div class="admin-profile-field">
                    <label for="admin-name">Name</label>
                    <input
                        id="admin-name"
                        type="text"
                        name="name"
                        value="{{ old('name', $admin->name) }}"
                        maxlength="255"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="admin-profile-field">
                    <label for="admin-email">Email address</label>
                    <input
                        id="admin-email"
                        type="email"
                        name="email"
                        value="{{ old('email', $admin->email) }}"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >
                    <small>Changing this does not change a customer account with the same email.</small>
                </div>

                <div class="admin-profile-field">
                    <label for="admin-phone">Phone</label>
                    <input
                        id="admin-phone"
                        type="text"
                        name="phone"
                        value="{{ old('phone', $admin->phone) }}"
                        maxlength="50"
                        autocomplete="tel"
                    >
                </div>

                <div class="admin-profile-meta">
                    <div>
                        <span>Account type</span>
                        <strong>{{ $admin->isSuperAdmin() ? 'Super Administrator' : 'Administrator' }}</strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong>{{ ucfirst($admin->status ?? 'active') }}</strong>
                    </div>
                </div>

                <button type="submit" class="admin-profile-submit">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Save profile
                </button>
            </form>
        </section>

        <section class="admin-profile-card">
            <div class="admin-profile-card-heading">
                <span class="admin-profile-card-icon">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <div>
                    <h3>Change password</h3>
                    <p>Confirm your current administrator password first.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.password.update') }}" class="admin-profile-form">
                @csrf
                @method('PUT')

                <div class="admin-profile-field">
                    <label for="admin-current-password">Current password</label>
                    <input
                        id="admin-current-password"
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="admin-profile-field">
                    <label for="admin-new-password">New password</label>
                    <input
                        id="admin-new-password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="admin-profile-field">
                    <label for="admin-password-confirmation">Confirm new password</label>
                    <input
                        id="admin-password-confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="admin-profile-submit">
                    <i class="fa-solid fa-key"></i>
                    Update password
                </button>
            </form>
        </section>
    </div>
</div>

@if ($popupTitle)
<div
    class="admin-profile-popup-backdrop"
    id="adminProfilePopup"
    role="dialog"
    aria-modal="true"
    aria-labelledby="adminProfilePopupTitle"
>
    <div class="admin-profile-popup">
        <span class="admin-profile-popup-icon {{ $popupType }}">
            <i class="fa-solid {{ $popupType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' }}"></i>
        </span>

        <h3 id="adminProfilePopupTitle">{{ $popupTitle }}</h3>
        <p>{{ $popupMessage }}</p>

        <button type="button" class="admin-profile-popup-close" id="adminProfilePopupClose">
            OK
        </button>
    </div>
</div>
@endif

<style>
.admin-profile-page{display:grid;gap:20px}
.admin-profile-page-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:22px;border:1px solid #e5eaf1;border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(23,32,51,.05)}
.admin-profile-eyebrow{display:block;margin-bottom:5px;color:#635bff;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.admin-profile-page-heading h2{margin:0;color:#172033;font-size:24px}
.admin-profile-page-heading p{margin:7px 0 0;color:#64748b;font-size:13px}
.admin-profile-back,.admin-profile-submit,.admin-profile-popup-close{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:10px;font-weight:800;text-decoration:none;cursor:pointer}
.admin-profile-back{padding:10px 13px;background:#f1f5f9;color:#172033}
.admin-profile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
.admin-profile-card{padding:22px;border:1px solid #e5eaf1;border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(23,32,51,.05)}
.admin-profile-card-heading{display:flex;gap:12px;margin-bottom:20px}
.admin-profile-card-icon{display:grid;width:38px;height:38px;flex:0 0 38px;place-items:center;border-radius:10px;background:#eef2ff;color:#635bff}
.admin-profile-card-heading h3{margin:0;color:#172033;font-size:17px}
.admin-profile-card-heading p{margin:5px 0 0;color:#64748b;font-size:12px}
.admin-profile-form{display:grid;gap:15px}
.admin-profile-field{display:grid;gap:7px}
.admin-profile-field label{color:#334155;font-size:12px;font-weight:800}
.admin-profile-field input{width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid #dbe2ea;border-radius:10px;background:#fff;color:#172033;outline:none}
.admin-profile-field input:focus{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.1)}
.admin-profile-field small{color:#64748b;font-size:10px}
.admin-profile-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.admin-profile-meta>div{padding:11px;border-radius:10px;background:#f8fafc}
.admin-profile-meta span,.admin-profile-meta strong{display:block}
.admin-profile-meta span{color:#64748b;font-size:10px}
.admin-profile-meta strong{margin-top:3px;color:#172033;font-size:12px}
.admin-profile-submit{width:max-content;padding:11px 15px;background:#635bff;color:#fff}
.admin-profile-popup-backdrop{position:fixed;inset:0;z-index:99999;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.48)}
.admin-profile-popup{width:min(390px,100%);padding:28px 24px;border-radius:16px;background:#fff;text-align:center;box-shadow:0 24px 70px rgba(15,23,42,.24)}
.admin-profile-popup-icon{display:grid;width:48px;height:48px;margin:0 auto 13px;place-items:center;border-radius:50%;background:#dcfce7;color:#15803d;font-size:20px}
.admin-profile-popup-icon.error{background:#fee2e2;color:#b91c1c}
.admin-profile-popup h3{margin:0;color:#172033;font-size:18px}
.admin-profile-popup p{margin:8px 0 18px;color:#64748b;font-size:13px}
.admin-profile-popup-close{min-width:100px;padding:10px 15px;background:#635bff;color:#fff}

@media(max-width:900px){
    .admin-profile-grid{grid-template-columns:1fr}
    .admin-profile-page-heading{align-items:flex-start;flex-direction:column}
    .admin-profile-back{width:100%}
}

@media(max-width:520px){
    .admin-profile-page-heading,.admin-profile-card{padding:17px}
    .admin-profile-meta{grid-template-columns:1fr}
}
</style>

@if ($popupTitle)
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const popup = document.getElementById('adminProfilePopup');
    const closeButton = document.getElementById('adminProfilePopupClose');

    if (!popup || !closeButton) {
        return;
    }

    const closePopup = function () {
        popup.remove();
    };

    closeButton.addEventListener('click', closePopup);

    popup.addEventListener('click', function (event) {
        if (event.target === popup) {
            closePopup();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePopup();
        }
    });
});
</script>
@endif
@endsection
