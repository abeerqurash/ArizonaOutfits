<header class="customer-password-header">
    <div class="customer-password-header-icon" aria-hidden="true">
        <i class="fa-solid fa-key"></i>
    </div>

    <div>
        <span>Password</span>

        <h3>Update password</h3>

        <p>
            Choose a strong, unique password that you do not use on another account.
        </p>
    </div>
</header>

<form
    method="POST"
    action="{{ route('password.update') }}"
    class="customer-password-form"
    data-password-form
>
    @csrf
    @method('PUT')

    <label class="customer-field">
        <span>Current password</span>

        <div class="customer-password-input">
            <input
                name="current_password"
                type="password"
                autocomplete="current-password"
                required
                data-password-input
            >

            <button
                type="button"
                class="customer-password-toggle"
                aria-label="Show current password"
                aria-pressed="false"
                data-password-toggle
            >
                <i class="fa-regular fa-eye" aria-hidden="true"></i>
            </button>
        </div>

        @foreach ($errors->updatePassword->get('current_password') as $message)
            <small>{{ $message }}</small>
        @endforeach
    </label>

    <label class="customer-field">
        <span>New password</span>

        <div class="customer-password-input">
            <input
                name="password"
                type="password"
                autocomplete="new-password"
                required
                data-new-password
                data-password-input
            >

            <button
                type="button"
                class="customer-password-toggle"
                aria-label="Show new password"
                aria-pressed="false"
                data-password-toggle
            >
                <i class="fa-regular fa-eye" aria-hidden="true"></i>
            </button>
        </div>

        @foreach ($errors->updatePassword->get('password') as $message)
            <small>{{ $message }}</small>
        @endforeach
    </label>

    <div
        class="customer-password-guide"
        aria-live="polite"
    >
        <div class="customer-password-strength">
            <div>
                <span>Password strength</span>
                <strong data-password-strength-label>Not entered</strong>
            </div>

            <div
                class="customer-password-strength-track"
                aria-hidden="true"
            >
                <span data-password-strength-bar></span>
            </div>
        </div>

        <p class="customer-password-guide-title">
            For a strong, protected password:
        </p>

        <div class="customer-password-rules">
            <span data-password-rule="length">
                <i class="fa-regular fa-circle" aria-hidden="true"></i>
                Use at least 8 characters
            </span>

            <span data-password-rule="lower">
                <i class="fa-regular fa-circle" aria-hidden="true"></i>
                Add a lowercase letter
            </span>

            <span data-password-rule="upper">
                <i class="fa-regular fa-circle" aria-hidden="true"></i>
                Add an uppercase letter
            </span>

            <span data-password-rule="number">
                <i class="fa-regular fa-circle" aria-hidden="true"></i>
                Add a number
            </span>

            <span data-password-rule="symbol">
                <i class="fa-regular fa-circle" aria-hidden="true"></i>
                Add a symbol
            </span>
        </div>

        <p class="customer-password-guide-note">
            Avoid names, birthdays, common words and passwords you already use elsewhere.
        </p>
    </div>

    <label class="customer-field">
        <span>Confirm new password</span>

        <div class="customer-password-input">
            <input
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                data-password-confirmation
                data-password-input
            >

            <button
                type="button"
                class="customer-password-toggle"
                aria-label="Show password confirmation"
                aria-pressed="false"
                data-password-toggle
            >
                <i class="fa-regular fa-eye" aria-hidden="true"></i>
            </button>
        </div>

        <small
            class="customer-password-match"
            data-password-match
            hidden
        ></small>

        @foreach ($errors->updatePassword->get('password_confirmation') as $message)
            <small>{{ $message }}</small>
        @endforeach
    </label>

    <div class="customer-form-actions customer-password-actions">
        <button type="submit">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            Save password
        </button>

        @if (session('status') === 'password-updated')
            <em>
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                Password updated.
            </em>
        @endif
    </div>
</form>

<style>
.customer-password-header{
    display:flex;
    align-items:flex-start;
    gap:13px;
}

.customer-password-header-icon{
    display:grid;
    flex:0 0 40px;
    width:40px;
    height:40px;
    place-items:center;
    border-radius:10px;
    background:#ede9fe;
    color:#7c3aed;
    font-size:14px;
}

.customer-password-form .customer-field>span{
    display:block;
}

.customer-password-input{
    position:relative;
}

.customer-password-input input{
    padding-right:44px;
}

.customer-password-toggle{
    position:absolute;
    top:50%;
    right:7px;
    display:grid;
    width:32px;
    height:32px;
    padding:0;
    border:0;
    border-radius:7px;
    place-items:center;
    background:transparent;
    color:#64748b;
    cursor:pointer;
    transform:translateY(-50%);
}

.customer-password-toggle:hover,
.customer-password-toggle:focus-visible{
    background:#f1f5f9;
    color:#172033;
    outline:none;
}

.customer-password-guide{
    margin:-2px 0 15px;
    padding:13px;
    border:1px solid #e5eaf1;
    border-radius:10px;
    background:#f8fafc;
}

.customer-password-strength>div:first-child{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.customer-password-strength span{
    color:#64748b;
    font-size:10px;
    font-weight:800;
}

.customer-password-strength strong{
    color:#64748b;
    font-size:10px;
    font-weight:900;
}

.customer-password-strength-track{
    overflow:hidden;
    height:5px;
    margin-top:7px;
    border-radius:999px;
    background:#e2e8f0;
}

.customer-password-strength-track span{
    display:block;
    width:0;
    height:100%;
    border-radius:inherit;
    background:#94a3b8;
    transition:
        width .2s ease,
        background .2s ease;
}

.customer-password-guide-title{
    margin:12px 0 8px;
    color:#475569;
    font-size:10px;
    font-weight:900;
}

.customer-password-rules{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:7px 10px;
}

.customer-password-rules span{
    display:flex;
    align-items:center;
    gap:6px;
    color:#64748b;
    font-size:10px;
    line-height:1.4;
}

.customer-password-rules span i{
    flex:0 0 auto;
    color:#94a3b8;
    font-size:9px;
}

.customer-password-rules span.is-valid{
    color:#047857;
}

.customer-password-rules span.is-valid i{
    color:#047857;
}

.customer-password-guide-note{
    margin:10px 0 0;
    color:#94a3b8;
    font-size:9px;
    line-height:1.5;
}

.customer-password-match{
    margin-top:0;
    color:#be123c !important;
}

.customer-password-match.is-valid{
    color:#047857 !important;
}

.customer-password-actions button,
.customer-password-actions em{
    display:inline-flex;
    align-items:center;
    gap:7px;
}

@media(max-width:480px){
    .customer-password-rules{
        grid-template-columns:1fr;
    }

    .customer-password-actions{
        align-items:stretch;
        flex-direction:column;
    }

    .customer-password-actions button{
        width:100%;
        justify-content:center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form = document.querySelector('[data-password-form]');

    if (!form) {
        return;
    }

    form.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const wrapper = button.closest('.customer-password-input');
            const input = wrapper ? wrapper.querySelector('[data-password-input]') : null;
            const icon = button.querySelector('i');

            if (!input) {
                return;
            }

            const showPassword = input.type === 'password';

            input.type = showPassword ? 'text' : 'password';
            button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
            button.setAttribute(
                'aria-label',
                showPassword ? 'Hide password' : 'Show password'
            );

            if (icon) {
                icon.classList.toggle('fa-eye', !showPassword);
                icon.classList.toggle('fa-eye-slash', showPassword);
            }
        });
    });

    const password = form.querySelector('[data-new-password]');
    const confirmation = form.querySelector('[data-password-confirmation]');
    const strengthLabel = form.querySelector('[data-password-strength-label]');
    const strengthBar = form.querySelector('[data-password-strength-bar]');
    const matchMessage = form.querySelector('[data-password-match]');

    const rules = {
        length: form.querySelector('[data-password-rule="length"]'),
        lower: form.querySelector('[data-password-rule="lower"]'),
        upper: form.querySelector('[data-password-rule="upper"]'),
        number: form.querySelector('[data-password-rule="number"]'),
        symbol: form.querySelector('[data-password-rule="symbol"]')
    };

    function setRuleState(element, passed) {
        if (!element) {
            return;
        }

        const icon = element.querySelector('i');

        element.classList.toggle('is-valid', passed);

        if (icon) {
            icon.className = passed
                ? 'fa-solid fa-circle-check'
                : 'fa-regular fa-circle';
        }
    }

    function updatePasswordGuide() {
        if (!password) {
            return;
        }

        const value = password.value;

        const checks = {
            length: value.length >= 8,
            lower: /[a-z]/.test(value),
            upper: /[A-Z]/.test(value),
            number: /[0-9]/.test(value),
            symbol: /[^A-Za-z0-9]/.test(value)
        };

        Object.keys(checks).forEach(function (key) {
            setRuleState(rules[key], checks[key]);
        });

        const score = Object.values(checks).filter(Boolean).length;

        if (!strengthLabel || !strengthBar) {
            return;
        }

        let label = 'Not entered';
        let width = '0%';
        let color = '#94a3b8';

        if (value.length > 0) {
            if (score <= 2) {
                label = 'Weak';
                width = '25%';
                color = '#be123c';
            } else if (score === 3) {
                label = 'Fair';
                width = '50%';
                color = '#b45309';
            } else if (score === 4) {
                label = 'Good';
                width = '75%';
                color = '#1d4ed8';
            } else {
                label = 'Strong';
                width = '100%';
                color = '#047857';
            }
        }

        strengthLabel.textContent = label;
        strengthLabel.style.color = color;
        strengthBar.style.width = width;
        strengthBar.style.background = color;

        updateConfirmation();
    }

    function updateConfirmation() {
        if (!password || !confirmation || !matchMessage) {
            return;
        }

        if (confirmation.value === '') {
            matchMessage.hidden = true;
            matchMessage.textContent = '';
            matchMessage.classList.remove('is-valid');
            return;
        }

        const matches = password.value === confirmation.value;

        matchMessage.hidden = false;
        matchMessage.textContent = matches
            ? 'Passwords match.'
            : 'Passwords do not match.';
        matchMessage.classList.toggle('is-valid', matches);
    }

    if (password) {
        password.addEventListener('input', updatePasswordGuide);
    }

    if (confirmation) {
        confirmation.addEventListener('input', updateConfirmation);
    }

    updatePasswordGuide();
});
</script>
