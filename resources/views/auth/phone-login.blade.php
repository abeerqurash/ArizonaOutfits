@extends('layouts.app')

@section('title', 'Login With Phone')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'Welcome back.',
            'message' => 'Enter your verified phone number to securely access your Arizona Outfits account.'
        ]
    )

    <div class="auth-card">

        <span>
            Phone login
        </span>

        <h1>
            Log in with phone
        </h1>

        <p>
            We will send a 6-digit verification code to your verified phone number.
        </p>


        @if (session('status'))

            <div class="auth-status" role="status">
                {{ session('status') }}
            </div>

        @endif


        <form
            method="POST"
            action="{{ route('phone.login.send') }}"
        >

            @csrf


            <div class="auth-field">

                <label for="phone">
                    Phone number
                </label>

                <input
                    id="phone"
                    type="tel"
                    name="phone"
                    value="{{ old('phone') }}"
                    required
                    autofocus
                    inputmode="tel"
                    autocomplete="tel"
                    maxlength="30"
                    pattern="[0-9+() .-]{8,30}"
                    title="Enter a valid phone number using digits and common phone symbols."
                    placeholder="e.g. +92 312 3456789"
                    oninput="this.value=this.value.replace(/[^0-9+() .-]/g, '')"
                    aria-describedby="phone-help"
                >

                <small
                    id="phone-help"
                    style="display:block;margin-top:6px;color:#64748b;font-size:12px;line-height:1.45;"
                >
                    You can use an international format such as +92 312 3456789.
                </small>

                @error('phone')

                    <p class="auth-error" role="alert">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <button
                type="submit"
                class="auth-submit auth-submit-wide"
            >

                Send login code

                <i
                    class="fa-solid fa-arrow-right"
                    aria-hidden="true"
                ></i>

            </button>

        </form>


        <p class="auth-switch">

            Don't have an account?

            <a href="{{ route('phone.register') }}">
                Create one with phone
            </a>

        </p>


        <p class="auth-switch">

            <a href="{{ route('login') }}">
                Use another login method
            </a>

        </p>

    </div>

</section>

@endsection
