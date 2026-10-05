

<?php $__env->startSection('title', 'Edit ' . $supplier->company_name); ?>

<?php $__env->startSection('content'); ?>

<div class="supplier-editor-page">

    
    <section class="supplier-editor-header">

        <div class="supplier-editor-header-content">

            <div class="supplier-editor-header-icon">

                <i class="fa-regular fa-pen-to-square"></i>

            </div>

            <div>

                <span class="supplier-editor-eyebrow">
                    Supplier management
                </span>

                <div class="supplier-editor-title-row">

                    <h1>
                        Edit Supplier
                    </h1>

                    <span
                        class="supplier-editor-status <?php echo e($supplier->status); ?>">

                        <?php echo e($supplier->status_label); ?>


                    </span>

                    <?php if($supplier->is_preferred): ?>

                        <span class="supplier-editor-preferred">

                            <i class="fa-solid fa-star"></i>

                            Preferred

                        </span>

                    <?php endif; ?>

                </div>

                <p>
                    Update the supplier account for
                    <strong>
                        <?php echo e($supplier->company_name); ?>

                    </strong>.
                </p>

            </div>

        </div>

        <div class="supplier-editor-header-actions">

            <a
                href="<?php echo e(route(
                    'admin.suppliers.show',
                    $supplier
                )); ?>"
                class="supplier-editor-button secondary">

                <i class="fa-solid fa-arrow-left"></i>

                Supplier Profile

            </a>

            <a
                href="<?php echo e(route('admin.suppliers.index')); ?>"
                class="supplier-editor-button purchase">

                <i class="fa-solid fa-list"></i>

                All Suppliers

            </a>

        </div>

    </section>

    
    <?php if($errors->any()): ?>

        <section class="supplier-validation-summary">

            <span class="supplier-validation-icon">

                <i class="fa-solid fa-circle-exclamation"></i>

            </span>

            <div>

                <h2>
                    Please correct the following information
                </h2>

                <ul>

                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <li>
                            <?php echo e($error); ?>

                        </li>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </ul>

            </div>

        </section>

    <?php endif; ?>

    <form
        method="POST"
        action="<?php echo e(route(
            'admin.suppliers.update',
            $supplier
        )); ?>"
        id="supplierEditorForm">

        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <?php echo $__env->make(
            'admin.suppliers.form',
            [
                'supplier' => $supplier,
                'submitLabel' => 'Update Supplier',
            ]
        , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </form>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.suppliers.partials.editor-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('admin.suppliers.partials.editor-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\suppliers\edit.blade.php ENDPATH**/ ?>