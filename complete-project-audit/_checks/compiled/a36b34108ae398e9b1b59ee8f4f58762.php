<?php $__env->startSection('title', 'Reset Password'); ?>

<?php echo $__env->make('auth.partials.frontend-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $resetPasswordMessage = $errors->any()
        ? $errors->first()
        : session('status');

    $resetPasswordHasError = $errors->any();
?>

<section class="auth-page">

    <?php echo $__env->make(
        'auth.partials.visual-copy',
        [
            'heading' => 'A fresh start, securely.',
            'message' => 'Choose a new password and get back to your orders, invoices and saved account details.'
        ]
    , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="auth-card auth-card-scroll">

        <span>Account recovery</span>

        <div class="auth-heading-icon" aria-hidden="true">
            <i class="fa-solid fa-lock"></i>
        </div>

        <h1>Choose a new password</h1>

        <p>
            Use a strong password that you do not use on another website.
        </p>


        <form method="POST" action="<?php echo e(route('password.store')); ?>">

            <?php echo csrf_field(); ?>

            <input
                type="hidden"
                name="token"
                value="<?php echo e($request->route('token')); ?>"
            >


            <div class="auth-field">

                <label for="email">Email address</label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?php echo e(old('email', $request->email)); ?>"
                    required
                    autofocus
                    maxlength="255"
                    autocomplete="username"
                    placeholder="you@example.com"
                >

            </div>


            <div class="auth-field">

                <label for="password">New password</label>

                <div class="auth-password-input">

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        aria-describedby="password-requirements password-strength-text"
                        placeholder="Create a secure password"
                    >

                    <button
                        type="button"
                        class="auth-password-toggle"
                        data-password-toggle="password"
                        aria-label="Show password"
                        aria-controls="password"
                    >
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>

                </div>


                <div
                    class="auth-password-strength"
                    aria-live="polite"
                >

                    <div class="auth-strength-track">
                        <span id="password-strength-bar"></span>
                    </div>

                    <strong id="password-strength-text">
                        Enter a password
                    </strong>

                </div>


                <ul
                    class="auth-password-rules"
                    id="password-requirements"
                >
                    <li data-rule="length">10 or more characters</li>
                    <li data-rule="lower">One lowercase letter</li>
                    <li data-rule="upper">One uppercase letter</li>
                    <li data-rule="number">One number</li>
                    <li data-rule="symbol">One symbol</li>
                </ul>

            </div>


            <div class="auth-field">

                <label for="password_confirmation">
                    Confirm new password
                </label>

                <div class="auth-password-input">

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        aria-describedby="password-match"
                        placeholder="Enter the new password again"
                    >

                    <button
                        type="button"
                        class="auth-password-toggle"
                        data-password-toggle="password_confirmation"
                        aria-label="Show confirmed password"
                        aria-controls="password_confirmation"
                    >
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                    </button>

                </div>

                <p
                    class="auth-password-match"
                    id="password-match"
                    aria-live="polite"
                ></p>

            </div>


            <button
                class="auth-submit auth-submit-wide"
                type="submit"
            >
                Reset password

                <i
                    class="fa-solid fa-arrow-right"
                    aria-hidden="true"
                ></i>
            </button>

        </form>


        <p class="auth-switch">

            <a href="<?php echo e(route('login')); ?>">
                <i
                    class="fa-solid fa-arrow-left"
                    aria-hidden="true"
                ></i>

                Back to login
            </a>

        </p>

    </div>

</section>

<?php if($resetPasswordMessage): ?>
<div
    class="customer-auth-popup-backdrop"
    data-customer-auth-popup
    role="presentation"
>
    <div
        class="customer-auth-popup"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="customer-auth-popup-title"
        aria-describedby="customer-auth-popup-message"
    >
        <button
            type="button"
            class="customer-auth-popup-close"
            data-customer-auth-popup-close
            aria-label="Close message"
        >
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>

        <div
            class="customer-auth-popup-icon <?php echo e($resetPasswordHasError ? 'is-error' : 'is-success'); ?>"
            aria-hidden="true"
        >
            <i class="fa-solid <?php echo e($resetPasswordHasError ? 'fa-circle-exclamation' : 'fa-circle-check'); ?>"></i>
        </div>

        <h2 id="customer-auth-popup-title">
            <?php echo e($resetPasswordHasError ? 'Unable to reset password' : 'Success'); ?>

        </h2>

        <p id="customer-auth-popup-message">
            <?php echo e($resetPasswordMessage); ?>

        </p>

        <button
            type="button"
            class="customer-auth-popup-button"
            data-customer-auth-popup-close
        >
            OK
        </button>
    </div>
</div>
<?php endif; ?>


<?php $__env->startPush('page-styles'); ?>
<style>
    .auth-switch a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

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

    .customer-auth-popup-icon.is-success {
        background: #f0fdf4;
        color: #16a34a;
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

    @media (max-width: 575px) {
        .customer-auth-popup {
            padding: 28px 20px 22px;
            border-radius: 15px;
        }
    }
</style>
<?php $__env->stopPush(); ?>


<?php $__env->startPush('page-scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const popup = document.querySelector('[data-customer-auth-popup]');

    if (!popup) {
        return;
    }

    const closePopup = function () {
        popup.remove();
    };

    popup.querySelectorAll('[data-customer-auth-popup-close]')
        .forEach(function (button) {
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
<?php $__env->stopPush(); ?>


<?php echo $__env->make('auth.partials.password-tools', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\auth\reset-password.blade.php ENDPATH**/ ?>