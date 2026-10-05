<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $__env->yieldContent('title', 'Admin Dashboard'); ?> | Arizona Outfits</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?php echo e(asset('asset/css/admin-dashboard.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('asset/css/admin-hardening.css')); ?>">
    <?php echo $__env->yieldPushContent('page-styles'); ?>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>
        <div class="admin-main">
            <?php echo $__env->make('admin.partials.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <main class="admin-content" id="mainContent">
                <?php if(session('success')): ?>
                    <div class="admin-alert admin-alert-success" role="status"><i class="fa-solid fa-circle-check"></i><span><?php echo e(session('success')); ?></span><button type="button" class="admin-alert-close" aria-label="Close success message"><i class="fa-solid fa-xmark"></i></button></div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="admin-alert admin-alert-danger" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span><?php echo e(session('error')); ?></span><button type="button" class="admin-alert-close" aria-label="Close error message"><i class="fa-solid fa-xmark"></i></button></div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                    <div class="admin-alert admin-alert-danger" role="alert"><i class="fa-solid fa-circle-exclamation"></i><div><strong>Please correct the following:</strong><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><button type="button" class="admin-alert-close" aria-label="Close validation messages"><i class="fa-solid fa-xmark"></i></button></div>
                <?php endif; ?>
                <?php echo $__env->yieldContent('content'); ?>
            </main>
            <?php echo $__env->make('admin.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
    <script src="<?php echo e(asset('asset/js/admin-dashboard.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('page-scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/layouts/app.blade.php ENDPATH**/ ?>