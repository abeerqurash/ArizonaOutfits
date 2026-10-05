<?php $__env->startSection('title', 'Create Post'); ?>
<?php $__env->startSection('page-heading', 'Posts'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('admin.posts._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/posts/create.blade.php ENDPATH**/ ?>