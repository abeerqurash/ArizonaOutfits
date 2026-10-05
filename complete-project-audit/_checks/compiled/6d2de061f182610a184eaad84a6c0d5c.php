<div class="admin-panel">

    <div class="admin-panel-header">

        <div>
            <span class="admin-panel-eyebrow">
                Product performance
            </span>

            <h3>
                Best Sellers
            </h3>
        </div>

    </div>

    <div class="admin-section-links">

        <?php $__empty_1 = true; $__currentLoopData = $bestSellingProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

        <a
            href="<?php echo e(route(
                                'admin.products.edit',
                                $product
                            )); ?>">
            <i class="fa-solid fa-ranking-star"></i>

            <span>
                <?php echo e($product->title); ?>


                <small>
                    <?php echo e(number_format(
                                        $product->purchase_count
                                    )); ?>

                    purchases
                </small>
            </span>

            <i class="fa-solid fa-arrow-right"></i>
        </a>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

        <div class="admin-empty-state">

            <span>
                <i class="fa-solid fa-chart-line"></i>
            </span>

            <h4>
                No sales data yet
            </h4>

            <p>
                Best-selling products will appear here.
            </p>

        </div>

        <?php endif; ?>

    </div>

</div><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\dashboard\best-sellers.blade.php ENDPATH**/ ?>