@extends('layouts.app')

@section('title', 'Verify Phone Number')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'Verify your phone.',
            'message' => 'Enter the 6-digit security code sent to your phone number.'
        ]
    )

    <div class="auth-card">

        <span>
            Phone verification
        </span>

        <h1>
            Enter verification code
        </h1>

        <p>
            We sent a verification code to:
        </p>

        <p>
            <strong>
                {{ $phone }}
            </strong>
        </p>


        @if (session('status'))

            <div class="auth-status">
                {{ session('status') }}
            </div>

        @endif


        <form
            method="POST"
            action="{{
                $mode === 'register'
                    ? route('phone.register.verify.store')
                    : route('phone.login.verify.store')
            }}"
        >

            @csrf


            <div class="auth-field">

                <label for="code">
                    6-digit code
                </label>

                <input
                    id="code"
                    type="text"
                    name="code"
                    value="{{ old('code') }}"
                    required
                    autofocus
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    placeholder="000000"
                    style="
                        text-align:center;
                        font-size:24px;
                        letter-spacing:8px;
                    "
                >

                @error('code')

                    <p class="auth-error">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <button
                type="submit"
                class="auth-submit auth-submit-wide"
            >

                Verify and continue

                <i class="fa-solid fa-arrow-right"></i>

            </button>

        </form>


        <p class="auth-switch">

            Didn't receive the code?

            @if ($mode === 'register')

                <a href="{{ route('phone.register') }}">
                    Request another
                </a>

            @else

                <a href="{{ route('phone.login') }}">
                    Request another
                </a>

            @endif

        </p>

    </div>

</section>

@endsection