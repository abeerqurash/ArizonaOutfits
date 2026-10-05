<li>
    <a
        href="<?php echo e(route('admin.inventory-alerts.index')); ?>"
        class="<?php echo e(request()->routeIs('admin.inventory-alerts.*') ? 'active' : ''); ?>"
    >
        <span>Inventory Alerts</span>

        <?php if(($activeInventoryAlertCount ?? 0) > 0): ?>
            <span class="inventory-alert-count">
                <?php echo e($activeInventoryAlertCount > 99 ? '99+' : $activeInventoryAlertCount); ?>

            </span>
        <?php endif; ?>
    </a>
</li><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\layouts\sidebar.blade.php ENDPATH**/ ?>