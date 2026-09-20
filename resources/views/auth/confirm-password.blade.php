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

                @error('password')

                    <p
                        class="auth-error"
                        role="alert"
                    >
                        {{ $message }}
                    </p>

                @enderror

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

    .auth-confirm-back:hover {
        text-decoration: underline;
    }

    @media (max-width: 550px) {
        .auth-confirm-actions {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .auth-confirm-actions .auth-submit {
            width: 100%;
        }

        .auth-confirm-back {
            justify-content: center;
            min-height: 40px;
        }
    }
</style>
@endpush


@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document
        .querySelectorAll('[data-password-toggle]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(
                    button.dataset.passwordToggle
                );

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
});
</script>
@endpush

@endsection
