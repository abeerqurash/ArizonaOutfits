<div class="analytics-card">

    <div class="analytics-card-header">

        <div class="analytics-icon <?php echo e($colour); ?>">
            <i class="fa-solid <?php echo e($icon); ?>"></i>
        </div>

        <div>

            <h3><?php echo e($title); ?></h3>

            <span>Top 5 Products</span>

        </div>

    </div>

    <div class="analytics-list">

        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php

                $selling = $product->sale_price && $product->sale_price>0
                    ? $product->sale_price
                    : $product->regular_price;

                $margin = $selling>0
                    ? (($selling-$product->cost_price)/$selling)*100
                    : 0;

            ?>

            <div class="analytics-row">

                <div>

                    <strong>

                        <?php echo e($product->title); ?>


                    </strong>

                    <small>

                        <?php echo e($product->stock); ?> units

                    </small>

                </div>

                <strong>

                    <?php if($field=='margin'): ?>

                        <?php echo e(number_format($margin,1)); ?>%

                    <?php else: ?>

                        <?php echo e(config('inventory.currency', 'GBP')); ?> <?php echo e(number_format($product->$field,2)); ?>


                    <?php endif; ?>

                </strong>

            </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>

</div>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\stock-valuation\partials\analytics-card.blade.php ENDPATH**/ ?>