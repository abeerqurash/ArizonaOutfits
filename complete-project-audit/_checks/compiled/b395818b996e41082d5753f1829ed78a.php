<?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

    <?php
        $paymentStatus = strtolower(
            $order->payment_status ?? 'pending'
        );

        $orderStatus = strtolower(
            $order->order_status ?? 'pending'
        );

        $customerName =
            $order->customer_name
            ?: $order->user?->name
            ?: 'Guest';

        $customerEmail =
            $order->customer_email
            ?: $order->user?->email
            ?: 'Guest order';
    ?>

    <tr>

        <td>
            <a
                href="<?php echo e(route('admin.orders.show', $order)); ?>"
                class="admin-order-number"
            >
                <?php echo e($order->order_number ?: '#' . $order->id); ?>

            </a>
        </td>

        <td>
            <div class="admin-customer-cell">

                <span class="admin-table-avatar">
                    <?php echo e(strtoupper(substr($customerName, 0, 1))); ?>

                </span>

                <div>
                    <strong>
                        <?php echo e($customerName); ?>

                    </strong>

                    <small>
                        <?php echo e($customerEmail); ?>

                    </small>
                </div>

            </div>
        </td>

        <td>
            <strong>
                $<?php echo e(number_format((float) $order->total, 2)); ?>

            </strong>
        </td>

        <td>
            <span
                class="admin-badge admin-badge-<?php echo e($paymentStatus); ?>"
            >
                <?php echo e(ucfirst(
                        str_replace('_', ' ', $paymentStatus)
                    )); ?>

            </span>
        </td>

        <td>
            <span
                class="admin-badge admin-badge-<?php echo e($orderStatus); ?>"
            >
                <?php echo e(ucfirst(
                        str_replace('_', ' ', $orderStatus)
                    )); ?>

            </span>
        </td>

        <td>
            <span class="admin-table-date">
                <?php echo e($order->created_at?->format('M d, Y')); ?>

            </span>

            <small class="admin-table-time">
                <?php echo e($order->created_at?->format('h:i A')); ?>

            </small>
        </td>

        <td>
            <a
                href="<?php echo e(route('admin.orders.show', $order)); ?>"
                class="admin-table-action"
                title="View order"
            >
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </td>

    </tr>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

    <tr>
        <td colspan="7">

            <div class="admin-empty-state">

                <span>
                    <i class="fa-solid fa-bag-shopping"></i>
                </span>

                <h4>
                    No orders found
                </h4>

                <p>
                    No orders were placed during this selected period.
                </p>

            </div>

        </td>
    </tr>

<?php endif; ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\dashboard\recent-orders-rows.blade.php ENDPATH**/ ?>