<?php $__env->startSection('title', 'Create Product Tag'); ?>
<?php $__env->startSection('page-heading', 'Create Product Tag'); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-page-header tag-page-header">
    <div>
        <span class="admin-page-eyebrow">Catalog organization</span>
        <h2>Create Product Tag</h2>
        <p>Create a reusable tag for organizing and identifying related products.</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?php echo e(route('admin.product-tags.index')); ?>" class="admin-button admin-button-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Tags
        </a>
    </div>
</div>

<form action="<?php echo e(route('admin.product-tags.store')); ?>" method="POST" novalidate>
    <?php echo csrf_field(); ?>
    <?php echo $__env->make('admin.product-tags.partials.form', ['productTag' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page-styles'); ?>
<style>
.tag-page-header{margin-bottom:18px}.tag-page-header h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800;letter-spacing:-.025em}.tag-page-header p{color:#7b8497;font-size:12px}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\product-tags\create.blade.php ENDPATH**/ ?>