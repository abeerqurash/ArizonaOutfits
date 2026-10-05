<?php $__env->startSection('title', 'My Orders'); ?>
<?php $__env->startSection('page-heading', 'My Orders'); ?>

<?php $__env->startSection('content'); ?>
<header class="customer-page-heading">
    <div>
        <span>Order history</span>
        <h2>My orders</h2>
        <p>Find an order, follow its delivery status, or open its invoice.</p>
    </div>
</header>

<section class="customer-panel">
    <form method="GET" action="<?php echo e(route('customer.orders.index')); ?>" class="customer-filters">
        <label>
            <span>Search orders</span>
            <input
                type="search"
                name="search"
                value="<?php echo e(request('search')); ?>"
                placeholder="Order or tracking number">
        </label>

        <label>
            <span>Order status</span>
            <select name="status">
                <option value="">All statuses</option>

                <?php $__currentLoopData = [
                    'pending',
                    'confirmed',
                    'processing',
                    'packed',
                    'shipped',
                    'out_for_delivery',
                    'completed',
                    'delivered',
                    'cancelled',
                    'refunded',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option
                        value="<?php echo e($status); ?>"
                        <?php if(request('status') === $status): echo 'selected'; endif; ?>>
                        <?php echo e(str($status)->headline()); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>

        <label>
            <span>Payment status</span>
            <select name="payment_status">
                <option value="">All payments</option>
                <option value="awaiting_payment" <?php if(request('payment_status') === 'awaiting_payment'): echo 'selected'; endif; ?>>Awaiting full payment</option>
                <?php $__currentLoopData = ['pending','paid','partially_paid','completed','succeeded','failed','declined','cancelled','refunded']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paymentStatus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($paymentStatus); ?>" <?php if(request('payment_status') === $paymentStatus): echo 'selected'; endif; ?>><?php echo e(str($paymentStatus)->headline()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>

        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
            Search
        </button>

        <?php if(request()->hasAny(['search', 'status', 'payment_status'])): ?>
            <a href="<?php echo e(route('customer.orders.index')); ?>">Reset</a>
        <?php endif; ?>
    </form>

    <?php if($orders->isEmpty()): ?>
        <div class="customer-empty">
            <span>
                <i class="fa-solid fa-box-open"></i>
            </span>

            <h3>No orders found</h3>
            <p>Try changing your filters or visit the shop.</p>

            <a href="<?php echo e(route('products.index')); ?>">
                Visit shop
            </a>
        </div>
    <?php else: ?>
        <div class="customer-table-wrap">
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Placed</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php echo e($order->order_number ?: 'Order #' . $order->id); ?>

                                </strong>

                                <small>
                                    <?php echo e($order->tracking_number ?: 'Tracking pending'); ?>

                                </small>
                            </td>

                            <td>
                                <?php echo e($order->created_at?->format('d M Y')); ?>

                            </td>

                            <td>
                                <?php echo e(number_format($order->items_count)); ?>

                            </td>

                            <td>
                                <strong>
                                    <?php echo e(strtoupper($order->currency ?: 'USD')); ?>

                                    <?php echo e(number_format((float) $order->total, 2)); ?>

                                </strong>
                            </td>

                            <td>
                                <span class="customer-status <?php echo e($order->payment_status); ?>">
                                    <?php echo e(str($order->payment_status ?: 'pending')->headline()); ?>

                                </span>
                            </td>

                            <td>
                                <span class="customer-status <?php echo e($order->order_status); ?>">
                                    <?php echo e(str($order->order_status ?: 'pending')->headline()); ?>

                                </span>
                            </td>

                            <td>
                                <a
                                    class="customer-row-action"
                                    href="<?php echo e(route('customer.orders.show', $order->id)); ?>">
                                    View
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <?php if($orders->hasPages()): ?>
            <div class="customer-pagination">
                <?php echo e($orders->links()); ?>

            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('customer.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\customer\orders\index.blade.php ENDPATH**/ ?>