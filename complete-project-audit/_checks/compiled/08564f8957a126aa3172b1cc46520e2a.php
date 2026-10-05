

<?php $__env->startSection('title', 'Add Supplier'); ?>

<?php $__env->startSection('content'); ?>

<div class="supplier-editor-page">

    
    <section class="supplier-editor-header">

        <div class="supplier-editor-header-content">

            <div class="supplier-editor-header-icon">

                <i class="fa-solid fa-truck-field"></i>

            </div>

            <div>

                <span class="supplier-editor-eyebrow">
                    Supplier management
                </span>

                <h1>
                    Add Supplier
                </h1>

                <p>
                    Create a new supplier record with contact, commercial,
                    banking and purchasing information.
                </p>

            </div>

        </div>

        <div class="supplier-editor-header-actions">

            <a
                href="<?php echo e(route('admin.suppliers.index')); ?>"
                class="supplier-editor-button secondary">

                <i class="fa-solid fa-arrow-left"></i>

                Back to Suppliers

            </a>

            <a
                href="<?php echo e(route('admin.purchase-orders.index')); ?>"
                class="supplier-editor-button purchase">

                <i class="fa-solid fa-file-invoice-dollar"></i>

                Purchase Orders

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
        action="<?php echo e(route('admin.suppliers.store')); ?>"
        id="supplierEditorForm">

        <?php echo csrf_field(); ?>

        <?php echo $__env->make(
            'admin.suppliers.form',
            [
                'supplier' => null,
                'submitLabel' => 'Create Supplier',
            ]
        , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </form>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.suppliers.partials.editor-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('admin.suppliers.partials.editor-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\suppliers\create.blade.php ENDPATH**/ ?>