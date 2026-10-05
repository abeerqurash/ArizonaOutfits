<div class="admin-panel">

    <div class="admin-panel-header">

        <div>
            <span class="admin-panel-eyebrow">
                Inventory alerts
            </span>

            <h3>
                Low-Stock Products
            </h3>
        </div>

        <a href="<?php echo e(route('admin.products.index')); ?>">
            Manage inventory
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

    <div class="admin-table-wrapper">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <?php $__empty_1 = true; $__currentLoopData = $lowStockProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                <?php
                $productStatus = strtolower(
                $product->status ?? 'draft'
                );
                ?>

                <tr>

                    <td>
                        <strong>
                            <?php echo e($product->title); ?>

                        </strong>
                    </td>

                    <td>
                        <?php echo e($product->sku ?: 'No SKU'); ?>

                    </td>

                    <td>
                        <strong>
                            <?php echo e(number_format($product->stock)); ?>

                        </strong>

                        <small>
                            <?php echo e($product->stock <= 0
                                                    ? 'Out of stock'
                                                    : 'Low stock'); ?>

                        </small>
                    </td>

                    <td>
                        <span
                            class="admin-badge admin-badge-<?php echo e($productStatus); ?>">
                            <?php echo e(ucfirst($productStatus)); ?>

                        </span>
                    </td>

                    <td>
                        <a
                            href="<?php echo e(route(
                                                'admin.products.edit',
                                                $product
                                            )); ?>"
                            class="admin-table-action"
                            title="Edit product">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </td>

                </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                <tr>
                    <td colspan="5">

                        <div class="admin-empty-state">

                            <span>
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </span>

                            <h4>
                                Inventory looks healthy
                            </h4>

                            <p>
                                No products have stock at or below
                                <?php echo e($lowStockThreshold); ?> units.
                            </p>

                        </div>

                    </td>
                </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\dashboard\low-stock-products.blade.php ENDPATH**/ ?>