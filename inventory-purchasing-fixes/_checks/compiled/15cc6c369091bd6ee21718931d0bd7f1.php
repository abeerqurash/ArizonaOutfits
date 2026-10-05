<?php if(!$purchaseOrder->isDraft()): ?>
    <a
        href="<?php echo e(route(
            'admin.purchase-orders.receiving.index',
            $purchaseOrder
        )); ?>"
        class="purchase-order-button reorder">
        <i class="fa-solid fa-arrow-right-arrow-left"></i>
        Receiving & Returns
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/purchase-orders/partials/receiving-returns-button.blade.php ENDPATH**/ ?>