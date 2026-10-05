<?php if($purchaseOrder->isDraft()): ?>
    <a
        href="<?php echo e(route(
            'admin.purchase-orders.draft.edit',
            $purchaseOrder
        )); ?>"
        class="purchase-order-button reorder">
        <i class="fa-regular fa-pen-to-square"></i>
        Edit Draft
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\purchase-orders\partials\draft-edit-button.blade.php ENDPATH**/ ?>