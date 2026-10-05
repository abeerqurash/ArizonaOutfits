
<?php $__env->startSection('title','Edit '.$page->title); ?>
<?php $__env->startSection('page-heading','Edit CMS Page'); ?>
<?php $__env->startSection('content'); ?><div class="page-editor"><header class="editor-heading"><a href="<?php echo e(route('admin.pages.index')); ?>"><i class="fa-solid fa-arrow-left"></i> CMS Pages</a><h2><?php echo e($page->title); ?></h2><p>Last updated <?php echo e($page->updated_at?->diffForHumans()); ?>.</p></header><?php echo $__env->make('admin.pages._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div><?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/pages/edit.blade.php ENDPATH**/ ?>