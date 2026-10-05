<?php $__env->startSection('title', 'Edit Product Category'); ?>
<?php $__env->startSection('page-heading', 'Edit Product Category'); ?>

<?php $__env->startSection('content'); ?>

<div class="admin-page-header">
    <div>
        <span class="admin-page-eyebrow">Catalog organization</span>
        <h2>Edit Product Category</h2>
        <p>
            Update <strong><?php echo e($productCategory->title); ?></strong> including hierarchy, image and SEO.
        </p>
    </div>

    <div class="admin-page-actions">
        <a
            href="<?php echo e(route('admin.product-categories.index')); ?>"
            class="admin-button admin-button-secondary"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to Categories
        </a>
    </div>
</div>

<form
    action="<?php echo e(route('admin.product-categories.update', $productCategory)); ?>"
    method="POST"
    enctype="multipart/form-data"
    data-category-form
>
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <?php echo $__env->make('admin.product-categories.partials.form', [
        'productCategory' => $productCategory
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</form>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\product-categories\edit.blade.php ENDPATH**/ ?>