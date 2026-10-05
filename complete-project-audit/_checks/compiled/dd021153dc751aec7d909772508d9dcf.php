<div class="admin-panel">

    <div class="admin-panel-header">

        <div>
            <span class="admin-panel-eyebrow">
                Fulfilment
            </span>

            <h3>
                Order Overview
            </h3>
        </div>

    </div>

    <div class="admin-section-links">

        <a
            href="<?php echo e(route('admin.orders.index', [
                            'order_status' => 'pending',
                        ])); ?>">
            <i class="fa-regular fa-clock"></i>

            <span>
                Pending
                (<?php echo e(number_format(
                                $orderStatusOverview['pending']
                            )); ?>)
            </span>

            <i class="fa-solid fa-arrow-right"></i>
        </a>

        <a
            href="<?php echo e(route('admin.orders.index', [
                            'order_status' => 'processing',
                        ])); ?>">
            <i class="fa-solid fa-box"></i>

            <span>
                Processing
                (<?php echo e(number_format(
                                $orderStatusOverview['processing']
                            )); ?>)
            </span>

            <i class="fa-solid fa-arrow-right"></i>
        </a>

        <a
            href="<?php echo e(route('admin.orders.index', [
                            'order_status' => 'completed',
                        ])); ?>">
            <i class="fa-solid fa-circle-check"></i>

            <span>
                Completed
                (<?php echo e(number_format(
                                $orderStatusOverview['completed']
                            )); ?>)
            </span>

            <i class="fa-solid fa-arrow-right"></i>
        </a>

        <a
            href="<?php echo e(route('admin.orders.index', [
                            'order_status' => 'cancelled',
                        ])); ?>">
            <i class="fa-solid fa-circle-xmark"></i>

            <span>
                Cancelled
                (<?php echo e(number_format(
                                $orderStatusOverview['cancelled']
                            )); ?>)
            </span>

            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

</div><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\dashboard\order-overview.blade.php ENDPATH**/ ?>