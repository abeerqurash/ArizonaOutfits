<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'IdeoStream'); ?></title>
    <meta name="description" content="<?php echo e($meta_description ?? ''); ?>">
    <meta name="robots" content="<?php echo e($robots ?? 'index, follow'); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('asset/css/style.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
</head>

<body id="<?php echo e(str_replace('.', '-', Route::currentRouteName())); ?>" 
    class="<?php echo e(str_replace('.', ' ', Route::currentRouteName())); ?>">

    <?php echo $__env->make('partials.headerportfolio', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>
    <?php echo $__env->make('partials.footerportfolio', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldPushContent('page-scripts'); ?>


<?php if(Route::currentRouteName() === 'abeerKhan-page'): ?>
<script src="<?php echo e(asset('asset/js/main.js')); ?>"></script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"></script>
</body>

</html><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\layouts\portfolio.blade.php ENDPATH**/ ?>