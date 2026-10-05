<?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

    <?php
        $customerName =
            $order->billing_name
            ?: $order->shipping_name
            ?: $order->user?->name
            ?: 'Guest customer';

        $customerEmail =
            $order->billing_email
            ?: $order->shipping_email
            ?: $order->user?->email
            ?: 'No email';

        $paymentStatus =
            $order->payment_status ?: 'pending';

        $orderStatus =
            $order->order_status ?: 'pending';

        $currency =
            strtoupper($order->currency ?: 'USD');
    ?>

    <tr data-order-row="<?php echo e($order->id); ?>">

        <td class="admin-orders-checkbox-column">

            <input
                type="checkbox"
                class="admin-order-checkbox js-order-checkbox"
                value="<?php echo e($order->id); ?>"
                aria-label="Select order <?php echo e($order->order_number ?: '#' . $order->id); ?>"
            >

        </td>

        <td>
            <span class="admin-orders-number">

                <strong>
                    <?php echo e($order->order_number ?: '#' . $order->id); ?>

                </strong>

                <small>
                    ID: <?php echo e($order->id); ?>

                </small>

            </span>
        </td>

        <td class="admin-orders-customer">

            <strong><?php echo e($customerName); ?></strong>

            <small><?php echo e($customerEmail); ?></small>

        </td>

        <td>
            <?php echo e(number_format((int) $order->items_count)); ?>

        </td>

        <td class="admin-orders-money">
            <?php echo e($currency); ?>

            <?php echo e(number_format((float) $order->total, 2)); ?>

        </td>

        <td>
            <span
                class="admin-status admin-status-<?php echo e(strtolower($paymentStatus)); ?>"
            >
                <?php echo e(ucfirst($paymentStatus)); ?>

            </span>
        </td>

        <td>
            <span
                class="admin-status admin-status-<?php echo e(strtolower($orderStatus)); ?>"
            >
                <?php echo e(ucfirst($orderStatus)); ?>

            </span>
        </td>

        <td class="admin-orders-tracking">
            <?php echo e($order->tracking_number ?: '—'); ?>

        </td>

        <td class="admin-orders-date">

            <?php echo e($order->created_at?->format('M d, Y')); ?>


            <br>

            <small>
                <?php echo e($order->created_at?->format('g:i A')); ?>

            </small>

        </td>

        <td>
            <a
                href="<?php echo e(route('admin.orders.show', $order)); ?>"
                class="admin-orders-view-button"
            >
                View
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </td>

    </tr>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

    <tr>
        <td
            colspan="10"
            class="admin-orders-empty"
        >

            <div class="admin-orders-empty-state">

                <span class="admin-orders-empty-icon">
                    <i class="fa-solid fa-box-open"></i>
                </span>

                <strong>No orders found</strong>

                <p>
                    No orders match your current search and filters.
                </p>

            </div>

        </td>
    </tr>

<?php endif; ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\orders\partials\rows.blade.php ENDPATH**/ ?>