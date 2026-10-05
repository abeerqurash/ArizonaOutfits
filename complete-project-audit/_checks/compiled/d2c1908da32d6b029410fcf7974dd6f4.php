<?php $__env->startSection('title', 'Create Blog Category'); ?>
<?php $__env->startSection('page-heading', 'Blog Categories'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('admin.categories._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\categories\create.blade.php ENDPATH**/ ?>