@extends('layouts.app')

@section('title', 'Confirm Password')

@include('auth.partials.frontend-styles')

@section('content')

<section class="auth-page">

    @include(
        'auth.partials.visual-copy',
        [
            'heading' => 'One more security check.',
            'message' => 'Confirm your password before continuing to this protected area of your Arizona Outfits account.'
        ]
    )

    <div class="auth-card">

        <span>
            Secure area
        </span>

        <div
            class="auth-heading-icon"
            aria-hidden="true"
        >
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <h1>
            Confirm your password
        </h1>

        <p>
            For your security, please enter your current password before continuing.
        </p>


        <form
            method="POST"
            action="{{ route('password.confirm') }}"
        >

            @csrf


            <div class="auth-field">

                <label for="password">
                    Current password
                </label>

                <div class="auth-password-input">

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autofocus
                        autocomplete="current-password"
                        placeholder="Enter your current password"
                    >

                    <button
                        type="button"
                        class="auth-password-toggle"
                        data-password-toggle="password"
                        aria-label="Show password"
                        aria-controls="password"
                    >
                        <i
                            class="fa-regular fa-eye"
                            aria-hidden="true"
                        ></i>
                    </button>

                </div>

            </div>


            <div class="auth-confirm-actions">

                <a
                    href="{{ route('customer.dashboard') }}"
                    class="auth-confirm-back"
                >
                    <i
                        class="fa-solid fa-arrow-left"
                        aria-hidden="true"
                    ></i>

                    Return to account
                </a>


                <button
                    class="auth-submit"
                    type="submit"
                >
                    Confirm password

                    <i
                        class="fa-solid fa-arrow-right"
                        aria-hidden="true"
                    ></i>
                </button>

            </div>

        </form>

    </div>

</section>

@if ($errors->any())
<div class="customer-auth-popup-backdrop" data-customer-auth-popup role="presentation">
    <div class="customer-auth-popup" role="alertdialog" aria-modal="true" aria-labelledby="customer-auth-popup-title" aria-describedby="customer-auth-popup-message">
        <button type="button" class="customer-auth-popup-close" data-customer-auth-popup-close aria-label="Close message">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
        <div class="customer-auth-popup-icon is-error" aria-hidden="true">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <h2 id="customer-auth-popup-title">Password confirmation failed</h2>
        <p id="customer-auth-popup-message">{{ $errors->first() }}</p>
        <button type="button" class="customer-auth-popup-button" data-customer-auth-popup-close>OK</button>
    </div>
</div>
@endif

@push('page-styles')
<style>
    .auth-confirm-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 20px;
    }

    .auth-confirm-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0f766e;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
    }

    .auth-confirm-back:hover { text-decoration: underline; }

    .customer-auth-popup-backdrop {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(3px);
    }

    .customer-auth-popup {
        position: relative;
        width: min(100%, 430px);
        padding: 30px 28px 26px;
        border: 1px solid rgba(148, 163, 184, .24);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
        text-align: center;
    }

    .customer-auth-popup-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 50%;
        background: #f1f5f9;
        color: #475569;
        cursor: pointer;
    }

    .customer-auth-popup-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin-bottom: 16px;
        border-radius: 50%;
        font-size: 25px;
    }

    .customer-auth-popup-icon.is-error {
        background: #fef2f2;
        color: #dc2626;
    }

    .customer-auth-popup h2 {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 22px;
        line-height: 1.25;
    }

    .customer-auth-popup p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.65;
    }

    .customer-auth-popup-button {
        min-width: 110px;
        margin-top: 22px;
        padding: 11px 22px;
        border: 0;
        border-radius: 10px;
        background: #111827;
        color: #fff;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
    }

    @media (max-width: 550px) {
        .auth-confirm-actions {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .auth-confirm-actions .auth-submit { width: 100%; }

        .auth-confirm-back {
            justify-content: center;
            min-height: 40px;
        }

        .customer-auth-popup {
            padding: 28px 20px 22px;
            border-radius: 15px;
        }
    }
</style>
@endpush

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.passwordToggle);

            if (!input) {
                return;
            }

            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';

            button.setAttribute(
                'aria-label',
                showing ? 'Show password' : 'Hide password'
            );

            const icon = button.querySelector('i');

            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }
        });
    });

    const popup = document.querySelector('[data-customer-auth-popup]');

    if (!popup) {
        return;
    }

    const closePopup = function () {
        popup.remove();
    };

    popup.querySelectorAll('[data-customer-auth-popup-close]').forEach(function (button) {
        button.addEventListener('click', closePopup);
    });

    popup.addEventListener('click', function (event) {
        if (event.target === popup) {
            closePopup();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && document.body.contains(popup)) {
            closePopup();
        }
    });
});
</script>
@endpush

@endsection
