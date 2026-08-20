@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            button.querySelector('i')?.classList.toggle('fa-eye', showing);
            button.querySelector('i')?.classList.toggle('fa-eye-slash', !showing);
        });
    });

    const password = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    const matchText = document.getElementById('password-match');
    if (!password) return;

    const tests = {
        length: value => value.length >= 10,
        lower: value => /[a-z]/.test(value),
        upper: value => /[A-Z]/.test(value),
        number: value => /[0-9]/.test(value),
        symbol: value => /[^A-Za-z0-9]/.test(value)
    };

    function refreshPasswordStatus() {
        const value = password.value;
        let score = 0;
        Object.entries(tests).forEach(function ([rule, test]) {
            const passed = test(value);
            score += passed ? 1 : 0;
            document.querySelector('[data-rule="' + rule + '"]')?.classList.toggle('passed', passed);
        });
        const levels = value.length === 0 ? ['Enter a password', 'empty'] : score <= 2 ? ['Weak password', 'weak'] : score <= 4 ? ['Good password', 'good'] : ['Strong password', 'strong'];
        if (strengthBar) { strengthBar.style.width = (score * 20) + '%'; strengthBar.className = levels[1]; }
        if (strengthText) { strengthText.textContent = levels[0]; strengthText.className = levels[1]; }
        refreshMatchStatus();
    }

    function refreshMatchStatus() {
        if (!confirmation || !matchText) return;
        if (!confirmation.value) { matchText.textContent = ''; matchText.className = 'auth-password-match'; return; }
        const matches = password.value === confirmation.value;
        matchText.textContent = matches ? 'Passwords match.' : 'Passwords do not match.';
        matchText.className = 'auth-password-match ' + (matches ? 'matches' : 'mismatch');
    }

    password.addEventListener('input', refreshPasswordStatus);
    confirmation?.addEventListener('input', refreshMatchStatus);
    refreshPasswordStatus();
});
</script>
@endpush
