@extends('layouts.app')

@section('title', 'Create Account With Phone')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'Create your account.',
            'message' => 'Use your phone number to create a secure Arizona Outfits customer account.'
        ]
    )

    <div class="auth-card">

        <span>
            Phone registration
        </span>

        <h1>
            Continue with phone
        </h1>

        <p>
            We will send a 6-digit verification code to your phone number.
        </p>


        @if (session('status'))

            <div class="auth-status">
                {{ session('status') }}
            </div>

        @endif


        <form
            method="POST"
            action="{{ route('phone.register.send') }}"
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
                    autocomplete="tel"
                    placeholder="03123456789"
                >

                <small>
                    Pakistan: 03123456789 or +923123456789.
                    International numbers should include the country code.
                </small>

                @error('phone')

                    <p class="auth-error">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <button
                type="submit"
                class="auth-submit auth-submit-wide"
            >

                Send verification code

                <i class="fa-solid fa-arrow-right"></i>

            </button>

        </form>


        <p class="auth-switch">

            Already have an account?

            <a href="{{ route('phone.login') }}">
                Log in with phone
            </a>

        </p>


        <p class="auth-switch">

            <a href="{{ route('register') }}">
                Use another signup method
            </a>

        </p>

    </div>

</section>

@endsection