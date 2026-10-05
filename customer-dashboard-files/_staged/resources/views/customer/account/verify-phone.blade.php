@extends('customer.layouts.app')

@section('title', 'Verify Phone')

@section('page-heading', 'Verify Phone')

@push('page-styles')

@include('customer.partials.styles')

<style>
    .customer-otp-wrap {
        width: 100%;
        max-width: 560px;
        margin: 0 auto;
    }

    .customer-otp-card {
        padding: 22px;
    }

    .customer-otp-icon {
        display: grid;
        width: 44px;
        height: 44px;
        margin-bottom: 14px;
        place-items: center;
        border-radius: 11px;
        background: #ccfbf1;
        color: #0f766e;
        font-size: 16px;
    }

    .customer-otp-card h3 {
        margin: 0 0 6px;
        color: #172033;
        font-size: 17px;
    }

    .customer-otp-card > p {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
    }

    .customer-otp-phone {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 16px 0 18px;
        padding: 11px 12px;
        border: 1px solid #e5eaf1;
        border-radius: 9px;
        background: #f8fafc;
        color: #172033;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .customer-otp-phone i {
        color: #0f766e;
    }

    .customer-otp-message {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 14px;
        padding: 11px 12px;
        border: 1px solid #a7f3d0;
        border-radius: 9px;
        background: #ecfdf5;
        color: #047857;
        font-size: 12px;
        line-height: 1.5;
    }

    .customer-otp-error {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 14px;
        padding: 11px 12px;
        border: 1px solid #fecdd3;
        border-radius: 9px;
        background: #fff1f2;
        color: #be123c;
        font-size: 12px;
        line-height: 1.5;
    }

    .customer-otp-error ul {
        margin: 0;
        padding-left: 17px;
    }

    .customer-otp-form {
        display: grid;
        gap: 14px;
    }

    .customer-otp-field {
        display: grid;
        gap: 6px;
    }

    .customer-otp-field label {
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .customer-otp-field input {
        box-sizing: border-box;
        width: 100%;
        min-height: 48px;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #172033;
        outline: none;
        text-align: center;
        font-size: 20px;
        font-weight: 850;
        letter-spacing: .32em;
    }

    .customer-otp-field input:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .09);
    }

    .customer-otp-help {
        color: #64748b;
        font-size: 11px;
        line-height: 1.45;
    }

    .customer-otp-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .customer-otp-submit,
    .customer-otp-back {
        display: inline-flex;
        min-height: 41px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
    }

    .customer-otp-submit {
        border: 1px solid #0f766e;
        background: #0f766e;
        color: #fff;
        cursor: pointer;
    }

    .customer-otp-submit:hover {
        background: #115e59;
    }

    .customer-otp-back {
        border: 1px solid #dbe2ea;
        background: #fff;
        color: #334155;
    }

    .customer-otp-back:hover {
        border-color: #99f6e4;
        background: #f0fdfa;
        color: #0f766e;
    }

    @media (max-width: 560px) {
        .customer-otp-card {
            padding: 17px;
        }

        .customer-otp-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .customer-otp-submit,
        .customer-otp-back {
            width: 100%;
        }
    }
</style>

@endpush


@section('content')

<header class="customer-page-heading">

    <div>

        <span>
            Account security
        </span>

        <h2>
            Verify phone number
        </h2>

        <p>
            Enter the 6-digit verification code to confirm your phone number.
        </p>

    </div>

</header>


<div class="customer-otp-wrap">

    <section class="customer-panel customer-form-card customer-otp-card">

        <div class="customer-otp-icon" aria-hidden="true">
            <i class="fa-solid fa-mobile-screen-button"></i>
        </div>

        <h3>
            Phone verification
        </h3>

        <p>
            We sent a 6-digit verification code to the phone number below.
        </p>


        <div class="customer-otp-phone">

            <i class="fa-solid fa-phone" aria-hidden="true"></i>

            <strong>
                {{ $phone }}
            </strong>

        </div>


        @if (session('status'))

            <div
                class="customer-otp-message"
                role="status"
            >
                <i
                    class="fa-solid fa-circle-check"
                    aria-hidden="true"
                ></i>

                <span>
                    {{ session('status') }}
                </span>
            </div>

        @endif


        @if ($errors->any())

            <div
                class="customer-otp-error"
                role="alert"
            >
                <i
                    class="fa-solid fa-circle-exclamation"
                    aria-hidden="true"
                ></i>

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            method="POST"
            action="{{ route('customer.security.phone.verify.store') }}"
            class="customer-otp-form"
        >

            @csrf


            <div class="customer-otp-field">

                <label for="code">
                    Verification code
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
                    minlength="6"
                    pattern="[0-9]{6}"
                    placeholder="000000"
                    aria-describedby="code-help"
                    oninput="this.value=this.value.replace(/\D/g, '').slice(0, 6)"
                >

                <span
                    id="code-help"
                    class="customer-otp-help"
                >
                    Enter numbers only. The code must contain exactly 6 digits.
                </span>

            </div>


            <div class="customer-otp-actions">

                <button
                    type="submit"
                    class="customer-otp-submit"
                >
                    <i
                        class="fa-solid fa-shield-halved"
                        aria-hidden="true"
                    ></i>

                    Verify phone
                </button>


                <a
                    href="{{ route('customer.security') }}"
                    class="customer-otp-back"
                >
                    <i
                        class="fa-solid fa-arrow-left"
                        aria-hidden="true"
                    ></i>

                    Back to Login &amp; Security
                </a>

            </div>

        </form>

    </section>

</div>

@endsection
