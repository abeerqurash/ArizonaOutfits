<?php $__env->startSection('title', 'Verify Phone Number'); ?>
<?php $__env->startSection('page-heading', 'Login & Security'); ?>

<?php $__env->startSection('content'); ?>

<?php
    $phoneVerificationHasError = $errors->any();

    $phoneVerificationMessage = $phoneVerificationHasError
        ? $errors->first()
        : session('status');
?>

<div
    class="security-phone-verification"
    role="dialog"
    aria-modal="true"
    aria-labelledby="security-phone-verification-title"
>
    <div
        class="security-phone-verification__backdrop"
        aria-hidden="true"
    ></div>

    <div class="security-phone-verification__dialog">

        <a
            href="<?php echo e(route('customer.security')); ?>"
            class="security-phone-verification__close"
            aria-label="Return to Login & Security"
            title="Back to Login & Security"
        >
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </a>

        <span class="security-phone-verification__eyebrow">
            Phone verification
        </span>

        <h1 id="security-phone-verification-title">
            Enter verification code
        </h1>

        <p class="security-phone-verification__copy">
            We sent a 6-digit security code to:
        </p>

        <p class="security-phone-verification__number">
            <?php echo e($phone); ?>

        </p>

        <?php if($phoneVerificationMessage): ?>
            <div
                class="security-phone-verification__message <?php echo e($phoneVerificationHasError ? 'is-error' : 'is-success'); ?>"
                role="<?php echo e($phoneVerificationHasError ? 'alert' : 'status'); ?>"
            >
                <i
                    class="fa-solid <?php echo e($phoneVerificationHasError ? 'fa-circle-exclamation' : 'fa-circle-check'); ?>"
                    aria-hidden="true"
                ></i>

                <span><?php echo e($phoneVerificationMessage); ?></span>
            </div>
        <?php endif; ?>

        <form
            method="POST"
            action="<?php echo e(route('customer.security.phone.verify.store')); ?>"
            class="security-phone-verification__form"
        >
            <?php echo csrf_field(); ?>

            <div class="security-phone-verification__field">
                <label for="security-phone-code">
                    6-digit code
                </label>

                <input
                    id="security-phone-code"
                    type="text"
                    name="code"
                    value="<?php echo e(old('code')); ?>"
                    required
                    autofocus
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    minlength="6"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    placeholder="000000"
                    aria-describedby="security-phone-code-help"
                    oninput="this.value=this.value.replace(/\D/g, '').slice(0, 6)"
                >

                <small id="security-phone-code-help">
                    Enter exactly 6 numbers.
                </small>
            </div>

            <button
                type="submit"
                class="security-phone-verification__submit"
            >
                <span>Verify and connect</span>

                <i
                    class="fa-solid fa-arrow-right"
                    aria-hidden="true"
                ></i>
            </button>
        </form>

        <div class="security-phone-verification__footer">
            <span>Didn't receive the code?</span>

            <a href="<?php echo e(route('customer.security')); ?>">
                Request another
            </a>
        </div>

    </div>
</div>

<?php $__env->startPush('page-styles'); ?>
<style>
    .security-phone-verification {
        position: fixed;
        inset: 0;
        z-index: 99998;
        display: grid;
        place-items: center;
        padding: 20px;
    }

    .security-phone-verification__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(4px);
    }

    .security-phone-verification__dialog {
        position: relative;
        z-index: 1;
        width: min(100%, 490px);
        max-height: calc(100vh - 40px);
        box-sizing: border-box;
        overflow-y: auto;
        padding: 36px 38px 32px;
        border: 1px solid rgba(148, 163, 184, .25);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .28);
    }

    .security-phone-verification__close {
        position: absolute;
        top: 14px;
        right: 14px;
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fff;
        color: #64748b;
        text-decoration: none;
        transition:
            background .15s ease,
            border-color .15s ease,
            color .15s ease;
    }

    .security-phone-verification__close:hover,
    .security-phone-verification__close:focus-visible {
        border-color: #99f6e4;
        background: #f0fdfa;
        color: #0f766e;
        outline: none;
    }

    .security-phone-verification__eyebrow {
        display: block;
        margin: 0 46px 10px 0;
        color: #0f766e;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .security-phone-verification__dialog h1 {
        margin: 0 0 10px;
        color: #172033;
        font-size: clamp(28px, 4vw, 38px);
        line-height: 1.12;
        letter-spacing: -.03em;
    }

    .security-phone-verification__copy {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .security-phone-verification__number {
        margin: 12px 0 22px;
        color: #334155;
        font-size: 14px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .security-phone-verification__message {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 0 0 18px;
        padding: 12px 13px;
        border: 1px solid;
        border-radius: 9px;
        font-size: 13px;
        line-height: 1.45;
    }

    .security-phone-verification__message i {
        flex: 0 0 auto;
        margin-top: 2px;
    }

    .security-phone-verification__message.is-success {
        border-color: #86efac;
        background: #f0fdf4;
        color: #047857;
    }

    .security-phone-verification__message.is-error {
        border-color: #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .security-phone-verification__form {
        display: grid;
        gap: 18px;
    }

    .security-phone-verification__field label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .security-phone-verification__field input {
        width: 100%;
        height: 54px;
        box-sizing: border-box;
        padding: 0 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        outline: none;
        background: #fff;
        color: #172033;
        text-align: center;
        font: inherit;
        font-size: 24px;
        letter-spacing: 8px;
        transition:
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .security-phone-verification__field input:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .12);
    }

    .security-phone-verification__field small {
        display: block;
        margin-top: 6px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.45;
    }

    .security-phone-verification__submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        min-height: 46px;
        padding: 11px 18px;
        border: 0;
        border-radius: 10px;
        background: #1e293b;
        color: #fff;
        font: inherit;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition:
            background .15s ease,
            transform .15s ease;
    }

    .security-phone-verification__submit:hover {
        background: #0f172a;
    }

    .security-phone-verification__submit:active {
        transform: translateY(1px);
    }

    .security-phone-verification__submit:focus-visible {
        outline: 3px solid rgba(15, 118, 110, .2);
        outline-offset: 2px;
    }

    .security-phone-verification__footer {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 22px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 12px;
    }

    .security-phone-verification__footer a {
        color: #0f766e;
        font-weight: 800;
        text-decoration: underline;
    }

    @media (max-width: 575px) {
        .security-phone-verification {
            padding: 14px;
        }

        .security-phone-verification__dialog {
            max-height: calc(100vh - 28px);
            padding: 28px 20px 24px;
            border-radius: 17px;
        }

        .security-phone-verification__dialog h1 {
            font-size: 29px;
        }

        .security-phone-verification__field input {
            font-size: 22px;
            letter-spacing: 6px;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('customer.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\auth\verify-phone.blade.php ENDPATH**/ ?>