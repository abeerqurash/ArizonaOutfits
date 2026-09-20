@extends('layouts.app')

@section('title', 'Verify Email')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'Almost there.',
            'message' => 'Verify your email address to secure your Arizona Outfits account and continue using your account features.'
        ]
    )

    <div class="auth-card">

        <span>Account security</span>

        <div
            class="auth-heading-icon"
            aria-hidden="true"
        >
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>

        <h1>Verify your email</h1>

        <p>
            Open the verification message we sent to your email address and select the verification link.
        </p>


        @if (session('status') === 'verification-link-sent')

            <div
                class="auth-status"
                role="status"
                aria-live="polite"
            >
                <i
                    class="fa-solid fa-circle-check"
                    aria-hidden="true"
                ></i>

                A fresh verification link has been sent to your email address.
            </div>

        @endif


        <div class="auth-verification-note">

            <i
                class="fa-regular fa-envelope"
                aria-hidden="true"
            ></i>

            <div>
                <strong>Didn't receive the email?</strong>

                <p>
                    Check your spam or junk folder first. You can also request another verification email below.
                </p>
            </div>

        </div>


        <div class="auth-verification-actions">

            <form
                method="POST"
                action="{{ route('verification.send') }}"
            >
                @csrf

                <button
                    class="auth-submit auth-submit-wide"
                    type="submit"
                >
                    <i
                        class="fa-regular fa-paper-plane"
                        aria-hidden="true"
                    ></i>

                    Resend verification email
                </button>
            </form>


            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    class="auth-secondary-button"
                    type="submit"
                >
                    <i
                        class="fa-solid fa-arrow-right-from-bracket"
                        aria-hidden="true"
                    ></i>

                    Log out
                </button>
            </form>

        </div>

    </div>

</section>


@push('page-styles')
<style>
    .auth-status {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .auth-status i {
        margin-top: 2px;
        flex: 0 0 auto;
    }

    .auth-verification-note {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin: 18px 0 22px;
        padding: 14px;
        border: 1px solid #dbe4ea;
        border-radius: 10px;
        background: rgba(248, 250, 252, .82);
        color: #475569;
    }

    .auth-verification-note > i {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 9px;
        background: #ccfbf1;
        color: #0f766e;
    }

    .auth-verification-note strong {
        display: block;
        margin-bottom: 3px;
        color: #172033;
        font-size: 12px;
    }

    .auth-verification-note p {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        line-height: 1.55;
    }

    .auth-verification-actions {
        display: grid;
        gap: 10px;
    }

    .auth-verification-actions form {
        width: 100%;
        margin: 0;
    }

    .auth-secondary-button {
        display: inline-flex;
        width: 100%;
        min-height: 44px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        box-sizing: border-box;
        padding: 0 18px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        color: #475569;
        font: inherit;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
        transition: border-color .2s, background .2s, color .2s;
    }

    .auth-secondary-button:hover {
        border-color: #94a3b8;
        background: #f8fafc;
        color: #172033;
    }
</style>
@endpush

@endsection
