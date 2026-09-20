<header class="customer-danger-header">
    <div class="customer-danger-header-icon" aria-hidden="true">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>

    <div>
        <span>Danger zone</span>
        <h3>Delete account</h3>
        <p>
            Permanently delete your ArizonaOutfits account.
            This action cannot be undone.
        </p>
    </div>
</header>

@if (filled(auth()->user()->password))

<div class="customer-danger-notice">
    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
    <p>
        Your profile will be permanently removed. Completed order records
        may still be retained where required for legal or accounting purposes.
    </p>
</div>

<details class="customer-delete-account">
    <summary>
        <span class="customer-delete-summary-icon" aria-hidden="true">
            <i class="fa-regular fa-trash-can"></i>
        </span>

        <span class="customer-delete-summary-copy">
            <strong>Delete my account</strong>
            <small>Review the permanent deletion details.</small>
        </span>

        <i
            class="fa-solid fa-chevron-down customer-delete-summary-arrow"
            aria-hidden="true"></i>
    </summary>

    <div class="customer-delete-expanded">
        <div class="customer-delete-explainer">
            <div class="customer-delete-explainer-icon" aria-hidden="true">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div>
                <strong>Before you continue</strong>
                <p>
                    Account deletion is permanent. To protect your account,
                    your current password is required before the request can be submitted.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('profile.destroy') }}"
            class="customer-delete-confirmation">
            @csrf
            @method('DELETE')

            <label class="customer-field customer-delete-password-field">
                <span>Current password</span>

                <div class="customer-delete-password-input">
                    <input
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        placeholder="Enter your current password"
                        data-delete-password>

                    <button
                        type="button"
                        class="customer-delete-password-toggle"
                        aria-label="Show password"
                        aria-pressed="false"
                        data-delete-password-toggle>
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>
                </div>

                @foreach ($errors->userDeletion->get('password') as $message)
                <small>{{ $message }}</small>
                @endforeach
            </label>

            <div class="customer-delete-footer">
                <div class="customer-delete-final-warning">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    <span>
                        Permanent action. You will be signed out immediately.
                    </span>
                </div>

                <button
                    type="submit"
                    class="customer-delete-submit">
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                    Permanently delete account
                </button>
            </div>
        </form>
    </div>
</details>

@else

<div
    class="customer-delete-security-required"
    role="alert">
    <span class="customer-delete-security-icon" aria-hidden="true">
        <i class="fa-solid fa-shield-halved"></i>
    </span>

    <div class="customer-delete-security-copy">
        <strong>Additional verification required</strong>
        <p>
            This account does not currently have a password.
            Add and verify an email and password before requesting
            permanent account deletion.
        </p>
    </div>

    <a
        href="{{ route('customer.security') }}"
        class="customer-delete-security-action">
        Login &amp; Security
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </a>
</div>

@endif

<style>
    .customer-form-card.danger {
        border-color: #f3d6dc;
        background: #fff;
    }

    .customer-danger-header {
        display: flex;
        align-items: flex-start;
        gap: 13px;
    }

    .customer-danger-header>div:last-child>span {
        color: #be123c !important;
    }

    .customer-danger-header-icon {
        display: grid;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 10px;
        background: #fff1f2;
        color: #be123c;
        font-size: 14px;
    }

    .customer-danger-notice {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 14px;
        padding: 9px 11px;
        border-left: 3px solid #e11d48;
        border-radius: 7px;
        background: #fff7f8;
        color: #9f1239;
    }

    .customer-danger-notice>i {
        margin-top: 3px;
        font-size: 10px;
    }

    .customer-danger-notice p {
        margin: 0;
        color: #881337;
        font-size: 10px;
        line-height: 1.55;
    }

    .customer-delete-account {
        width: 100%;
    }

    .customer-delete-account>summary {
        display: flex;
        width: 100%;
        box-sizing: border-box;
        align-items: center;
        gap: 11px;
        padding: 11px 12px;
        border: 1px solid #e5eaf1;
        border-radius: 9px;
        background: #fff;
        color: #172033;
        list-style: none;
        cursor: pointer;
        transition:
            border-color .18s ease,
            background .18s ease,
            box-shadow .18s ease;
    }

    .customer-delete-account>summary::-webkit-details-marker {
        display: none;
    }

    .customer-delete-account>summary:hover {
        border-color: #fecdd3;
        background: #fffafa;
    }

    .customer-delete-account[open]>summary {
        border-color: #fecdd3;
        border-radius: 9px 9px 0 0;
        background: #fffafa;
        box-shadow: 0 4px 12px rgba(190, 18, 60, .035);
    }

    .customer-delete-summary-icon {
        display: grid;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 8px;
        background: #fff1f2;
        color: #be123c;
        font-size: 12px;
    }

    .customer-delete-summary-copy {
        display: block;
        min-width: 0;
        flex: 1;
    }

    .customer-delete-summary-copy strong,
    .customer-delete-summary-copy small {
        display: block;
    }

    .customer-delete-summary-copy strong {
        color: #172033;
        font-size: 11px;
        font-weight: 900;
    }

    .customer-delete-summary-copy small {
        margin-top: 2px;
        color: #64748b;
        font-size: 9px;
        line-height: 1.4;
    }

    .customer-delete-summary-arrow {
        color: #94a3b8;
        font-size: 9px;
        transition: transform .18s ease;
    }

    .customer-delete-account[open] .customer-delete-summary-arrow {
        color: #be123c;
        transform: rotate(180deg);
    }

    .customer-delete-expanded {
        padding: 14px;
        border: 1px solid #fecdd3;
        border-top: 0;
        border-radius: 0 0 9px 9px;
        background: #fff;
    }

    .customer-delete-explainer {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 13px;
        border-bottom: 1px solid #edf0f5;
    }

    .customer-delete-explainer-icon {
        display: grid;
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 8px;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
    }

    .customer-delete-explainer strong {
        display: block;
        color: #172033;
        font-size: 11px;
        font-weight: 900;
    }

    .customer-delete-explainer p {
        max-width: 680px;
        margin: 3px 0 0 !important;
        color: #64748b !important;
        font-size: 10px !important;
        line-height: 1.55 !important;
    }

    .customer-delete-account .customer-delete-confirmation {
        max-width: none;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
    }

    .customer-delete-password-field {
        max-width: 620px;
        margin-bottom: 13px;
    }

    .customer-delete-password-input {
        position: relative;
    }

    .customer-delete-password-input input {
        padding-right: 44px;
    }

    .customer-delete-password-input input::placeholder {
        color: #94a3b8;
    }

    .customer-delete-password-toggle {
        position: absolute;
        top: 50%;
        right: 7px;
        display: grid;
        width: 32px;
        height: 32px;
        min-height: 32px !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 7px !important;
        place-items: center;
        background: transparent !important;
        color: #64748b !important;
        font-size: 12px !important;
        box-shadow: none !important;
        cursor: pointer;
        transform: translateY(-50%);
    }

    .customer-delete-password-toggle:hover,
    .customer-delete-password-toggle:focus-visible {
        background: #f1f5f9 !important;
        color: #172033 !important;
        outline: none;
    }

    .customer-delete-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding-top: 12px;
        border-top: 1px solid #edf0f5;
    }

    .customer-delete-final-warning {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #9f1239;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.45;
    }

    .customer-delete-final-warning i {
        flex: 0 0 auto;
    }

    .customer-delete-account .customer-delete-submit {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 38px;
        padding: 0 12px;
        border: 0;
        border-radius: 8px;
        background: #be123c;
        color: #fff;
        font-size: 10px;
        font-weight: 850;
        cursor: pointer;
        transition:
            background .18s ease,
            transform .18s ease;
    }

    .customer-delete-account .customer-delete-submit:hover {
        background: #9f1239;
        transform: translateY(-1px);
    }

    .customer-delete-security-required {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px;
        border: 1px solid #fde68a;
        border-radius: 10px;
        background: #fffbeb;
    }

    .customer-delete-security-icon {
        display: grid;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 9px;
        background: #fef3c7;
        color: #b45309;
        font-size: 13px;
    }

    .customer-delete-security-copy {
        min-width: 0;
        flex: 1;
    }

    .customer-delete-security-copy strong {
        display: block;
        color: #92400e;
        font-size: 11px;
        font-weight: 900;
    }

    .customer-delete-security-copy p {
        margin: 3px 0 0 !important;
        color: #a16207 !important;
        font-size: 10px !important;
        line-height: 1.5 !important;
    }

    .customer-delete-security-action {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 36px;
        padding: 0 11px;
        border-radius: 8px;
        background: #172033;
        color: #fff;
        font-size: 10px;
        font-weight: 850;
        text-decoration: none;
    }

    .customer-delete-security-action:hover {
        color: #fff;
        background: #0f172a;
    }

    .customer-delete-security-action i {
        font-size: 8px;
    }

    @media(max-width:760px) {
        .customer-delete-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .customer-delete-account .customer-delete-submit {
            width: 100%;
        }

        .customer-delete-security-required {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .customer-delete-security-action {
            width: 100%;
            box-sizing: border-box;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const passwordInput =
            document.querySelector('[data-delete-password]');

        const toggle =
            document.querySelector('[data-delete-password-toggle]');

        if (!passwordInput || !toggle) {
            return;
        }

        toggle.addEventListener('click', function() {
            const showPassword =
                passwordInput.type === 'password';

            passwordInput.type =
                showPassword ? 'text' : 'password';

            toggle.setAttribute(
                'aria-pressed',
                showPassword ? 'true' : 'false'
            );

            toggle.setAttribute(
                'aria-label',
                showPassword ?
                'Hide password' :
                'Show password'
            );

            const icon = toggle.querySelector('i');

            if (icon) {
                icon.classList.toggle(
                    'fa-eye',
                    !showPassword
                );

                icon.classList.toggle(
                    'fa-eye-slash',
                    showPassword
                );
            }
        });
    });
</script>