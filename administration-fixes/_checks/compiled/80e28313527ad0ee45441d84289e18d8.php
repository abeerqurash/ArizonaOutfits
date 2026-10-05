
<?php $__env->startSection('title','Create CMS Page'); ?>
<?php $__env->startSection('page-heading','Create CMS Page'); ?>
<?php $__env->startSection('content'); ?><div class="page-editor"><header class="editor-heading"><a href="<?php echo e(route('admin.pages.index')); ?>"><i class="fa-solid fa-arrow-left"></i> CMS Pages</a><h2>Create a new page</h2><p>Draft it privately or publish it immediately.</p></header><?php echo $__env->make('admin.pages._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div><?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/pages/create.blade.php ENDPATH**/ ?>