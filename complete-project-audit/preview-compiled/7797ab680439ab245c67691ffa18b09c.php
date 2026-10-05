<?php $__env->startSection('title', 'Login With Phone'); ?>

<?php echo $__env->make('auth.partials.frontend-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $phoneLoginHasError = $errors->any();

    $phoneLoginMessage = $phoneLoginHasError
        ? $errors->first()
        : session('status');
?>

<section class="auth-page">

    <?php echo $__env->make(
        'auth.partials.visual-copy',
        [
            'heading' => 'Welcome back.',
            'message' => 'Enter your verified phone number to securely access your Arizona Outfits account.'
        ]
    , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="auth-card">

        <span>
            Phone login
        </span>

        <h1>
            Log in with phone
        </h1>

        <p>
            We will send a 6-digit verification code to your verified phone number.
        </p>


        <form
            method="POST"
            action="<?php echo e(route('phone.login.send')); ?>"
        >

            <?php echo csrf_field(); ?>


            <div class="auth-field">

                <label for="phone">
                    Phone number
                </label>

                <input
                    id="phone"
                    type="tel"
                    name="phone"
                    value="<?php echo e(old('phone')); ?>"
                    required
                    autofocus
                    inputmode="tel"
                    autocomplete="tel"
                    maxlength="30"
                    pattern="[0-9+() .-]{8,30}"
                    title="Enter a valid phone number using digits and common phone symbols."
                    placeholder="e.g. +92 312 3456789"
                    oninput="this.value=this.value.replace(/[^0-9+() .-]/g, '')"
                    aria-describedby="phone-help"
                >

                <small
                    id="phone-help"
                    style="display:block;margin-top:6px;color:#64748b;font-size:12px;line-height:1.45;"
                >
                    You can use an international format such as +92 312 3456789.
                </small>

            </div>


            <button
                type="submit"
                class="auth-submit auth-submit-wide"
            >

                Send login code

                <i
                    class="fa-solid fa-arrow-right"
                    aria-hidden="true"
                ></i>

            </button>

        </form>


        <p class="auth-switch">

            Don't have an account?

            <a href="<?php echo e(route('phone.register')); ?>">
                Create one with phone
            </a>

        </p>


        <p class="auth-switch">

            <a href="<?php echo e(route('login')); ?>">
                Use another login method
            </a>

        </p>

    </div>

</section>


<?php if($phoneLoginMessage): ?>
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
            class="customer-auth-popup-icon <?php echo e($phoneLoginHasError ? 'is-error' : 'is-success'); ?>"
            aria-hidden="true"
        >
            <i class="fa-solid <?php echo e($phoneLoginHasError ? 'fa-circle-exclamation' : 'fa-circle-check'); ?>"></i>
        </div>

        <h2 id="customer-auth-popup-title">
            <?php echo e($phoneLoginHasError ? 'Unable to continue' : 'Success'); ?>

        </h2>

        <p id="customer-auth-popup-message">
            <?php echo e($phoneLoginMessage); ?>

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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/auth/phone-login.blade.php ENDPATH**/ ?>