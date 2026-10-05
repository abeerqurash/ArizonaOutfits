<?php $__env->startSection('title', 'Administrator Password Recovery'); ?>

<?php echo $__env->make('auth.partials.frontend-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startSection('content'); ?>

<section class="auth-page">
    <?php echo $__env->make('auth.partials.visual-copy', [
        'heading' => 'Recover administrator access.',
        'message' => 'Request a secure password reset link for your Arizona Outfits administrator account.'
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="auth-card">
        <span>Administrator security</span>
        <h1>Forgot password?</h1>
        <p>Enter your administrator email address and we will send password reset instructions if the account is active.</p>

        <form method="POST" action="<?php echo e(route('admin.password.email')); ?>">
            <?php echo csrf_field(); ?>

            <div class="auth-field">
                <label for="email">Administrator email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?php echo e(old('email')); ?>"
                    required
                    autofocus
                    maxlength="255"
                    autocomplete="email"
                    placeholder="admin@example.com"
                >
            </div>

            <button class="auth-submit auth-submit-wide" type="submit">
                Send reset instructions
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
        </form>

        <p class="auth-switch">
            Remembered your password?
            <a href="<?php echo e(route('admin.login')); ?>">Back to admin login</a>
        </p>
    </div>
</section>

<?php if($errors->any() || session('status')): ?>
    <div
        class="admin-auth-popup-layer is-open"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminAuthMessageTitle"
    >
        <div class="admin-auth-popup">
            <div class="admin-auth-popup-icon <?php echo e($errors->any() ? 'admin-auth-popup-icon-error' : 'admin-auth-popup-icon-success'); ?>">
                <i class="fa-solid <?php echo e($errors->any() ? 'fa-triangle-exclamation' : 'fa-circle-check'); ?>" aria-hidden="true"></i>
            </div>

            <h2 id="adminAuthMessageTitle">
                <?php echo e($errors->any() ? 'Unable to send reset instructions' : 'Check your email'); ?>

            </h2>

            <p><?php echo e($errors->any() ? $errors->first() : session('status')); ?></p>

            <button type="button" class="auth-submit auth-submit-wide" data-admin-auth-popup-close>
                Close
            </button>
        </div>
    </div>
<?php endif; ?>

<?php $__env->startPush('page-styles'); ?>
<style>
.admin-auth-popup-layer{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.62);backdrop-filter:blur(5px)}
.admin-auth-popup-layer.is-open{display:flex}
.admin-auth-popup{width:min(100%,440px);padding:30px 26px;border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(15,23,42,.24);text-align:center}
.admin-auth-popup-icon{display:grid;width:54px;height:54px;margin:0 auto 16px;place-items:center;border-radius:50%;font-size:21px}
.admin-auth-popup-icon-error{background:#fef2f2;color:#dc2626}
.admin-auth-popup-icon-success{background:#ecfdf5;color:#047857}
.admin-auth-popup h2{margin:0 0 10px;color:#172033;font-size:21px;line-height:1.3}
.admin-auth-popup p{margin:0 0 20px;color:#64748b;font-size:13px;line-height:1.65}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('page-scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-admin-auth-popup-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            const popup = button.closest('.admin-auth-popup-layer');
            if (popup) popup.classList.remove('is-open');
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\auth\forgot-password.blade.php ENDPATH**/ ?>