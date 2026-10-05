<?php $__env->startSection('title', 'Verify Email'); ?>

<?php echo $__env->make('auth.partials.frontend-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $emailVerificationHasError = $errors->any();

    $emailVerificationMessage = $emailVerificationHasError
        ? $errors->first()
        : (
            session('status') === 'verification-link-sent'
                ? 'A fresh verification link has been sent to your email address.'
                : null
        );
?>

<section class="auth-page">

    <?php echo $__env->make(
        'auth.partials.visual-copy',
        [
            'heading' => 'Almost there.',
            'message' => 'Verify your email address to secure your Arizona Outfits account and continue using your account features.'
        ]
    , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
                action="<?php echo e(route('verification.send')); ?>"
            >
                <?php echo csrf_field(); ?>

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
                action="<?php echo e(route('logout')); ?>"
            >
                <?php echo csrf_field(); ?>

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


<?php if($emailVerificationMessage): ?>
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
            class="customer-auth-popup-icon <?php echo e($emailVerificationHasError ? 'is-error' : 'is-success'); ?>"
            aria-hidden="true"
        >
            <i class="fa-solid <?php echo e($emailVerificationHasError ? 'fa-circle-exclamation' : 'fa-circle-check'); ?>"></i>
        </div>

        <h2 id="customer-auth-popup-title">
            <?php echo e($emailVerificationHasError ? 'Unable to continue' : 'Verification email sent'); ?>

        </h2>

        <p id="customer-auth-popup-message">
            <?php echo e($emailVerificationMessage); ?>

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\auth\verify-email.blade.php ENDPATH**/ ?>