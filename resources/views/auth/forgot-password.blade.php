@extends('layouts.app')

@section('title', 'Forgot Password')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'We will help you back in.',
            'message' => 'Account recovery is secure and simple. Your reset link will be sent only to your registered email.'
        ]
    )

    <div class="auth-card">

        <span>
            Account recovery
        </span>

        <div class="auth-heading-icon" aria-hidden="true">
            <i class="fa-solid fa-key"></i>
        </div>

        <h1>
            Forgot password?
        </h1>

        <p>
            Enter your account email and we will send you a secure password-reset link.
        </p>


        @if (session('status'))

            <div
                class="auth-status"
                role="status"
            >
                {{ session('status') }}
            </div>

        @endif


        <form
            method="POST"
            action="{{ route('password.email') }}"
        >

            @csrf


            <div class="auth-field">

                <label for="email">
                    Email address
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    maxlength="255"
                    autocomplete="email"
                    placeholder="you@example.com"
                    aria-describedby="email-help"
                >

                <small
                    id="email-help"
                    class="auth-recovery-help"
                >
                    Use the email address connected to your Arizona Outfits account.
                </small>

                @error('email')

                    <p
                        class="auth-error"
                        role="alert"
                    >
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <button
                class="auth-submit auth-submit-wide"
                type="submit"
            >
                Send reset link

                <i
                    class="fa-regular fa-paper-plane"
                    aria-hidden="true"
                ></i>
            </button>

        </form>


        <p class="auth-switch">

            <a href="{{ route('login') }}">
                <i
                    class="fa-solid fa-arrow-left"
                    aria-hidden="true"
                ></i>

                Back to login
            </a>

        </p>

    </div>

</section>


@push('page-styles')
<style>
    .auth-recovery-help {
        display: block;
        margin-top: 7px;
        color: #64748b;
        font-size: 11px;
        line-height: 1.5;
    }

    .auth-switch a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endpush

@endsection
