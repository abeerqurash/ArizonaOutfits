<?php $__env->startSection('title', 'Favorites'); ?>

<?php $__env->startSection('content'); ?>

<div class="container">

    <h1>Favorites</h1>

    <div class="products-grid">
        <?php $__empty_1 = true; $__currentLoopData = $favorites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $favorite): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('products.partials.product-card', ['product' => $favorite->product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p>No favorite products yet.</p>
        <?php endif; ?>
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\customer-dashboard-files\_checks/../_staged/resources/views/favorites/index.blade.php ENDPATH**/ ?>