<?php $__env->startSection('title', 'My Account'); ?>
<?php $__env->startSection('page-heading', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<header class="customer-page-heading">
    <div><span>Account overview</span><h2>Welcome back, <?php echo e(auth('web')->user()->name); ?></h2><p>Review your orders, delivery progress, account details and invoices.</p></div>
    <a href="<?php echo e(route('products.index')); ?>" class="customer-primary-button"><i class="fa-solid fa-bag-shopping"></i> Shop products</a>
</header>

<div class="customer-stat-grid">
    <article class="customer-stat-card"><span class="customer-stat-icon blue"><i class="fa-solid fa-bag-shopping"></i></span><div><small>Total orders</small><strong><?php echo e(number_format($stats['orders'])); ?></strong><p>All purchases</p></div></article>
    <article class="customer-stat-card"><span class="customer-stat-icon amber"><i class="fa-solid fa-clock"></i></span><div><small>Processing</small><strong><?php echo e(number_format($stats['processing'])); ?></strong><p>Being prepared</p></div></article>
    <article class="customer-stat-card"><span class="customer-stat-icon violet"><i class="fa-solid fa-truck-fast"></i></span><div><small>In transit</small><strong><?php echo e(number_format($stats['shipped'])); ?></strong><p>On the way</p></div></article>
    <article class="customer-stat-card"><span class="customer-stat-icon green"><i class="fa-solid fa-circle-check"></i></span><div><small>Completed</small><strong><?php echo e(number_format($stats['completed'])); ?></strong><p>Delivered orders</p></div></article>
</div>

<section class="customer-panel customer-payment-summary">
    <header class="customer-panel-heading"><div><span>Payment overview</span><h3>Paid purchases</h3></div></header>
    <div class="customer-summary-body">
        <?php $__empty_1 = true; $__currentLoopData = $paidSpend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div><small><?php echo e($currency); ?></small><strong><?php echo e(number_format($amount, 2)); ?></strong></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p>No paid purchases yet.</p>
        <?php endif; ?>
        <a href="<?php echo e(route('customer.orders.index', ['payment_status' => 'awaiting_payment'])); ?>"><?php echo e(number_format($stats['pending_payment'])); ?> order(s) awaiting full payment</a>
    </div>
</section>

<section class="customer-panel">
    <header class="customer-panel-heading"><div><span>Order activity</span><h3>Recent orders</h3></div><a href="<?php echo e(route('customer.orders.index')); ?>">View all orders <i class="fa-solid fa-arrow-right"></i></a></header>
    <?php if($recentOrders->isEmpty()): ?>
        <div class="customer-empty"><span><i class="fa-solid fa-bag-shopping"></i></span><h3>No orders yet</h3><p>Your purchases will appear here after checkout.</p><a href="<?php echo e(route('products.index')); ?>">Start shopping</a></div>
    <?php else: ?>
        <div class="customer-table-wrap"><table class="customer-table"><thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead><tbody>
        <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><strong><?php echo e($order->order_number ?: 'Order #'.$order->id); ?></strong><small><?php echo e($order->tracking_number ?: 'Tracking pending'); ?></small></td><td><?php echo e($order->created_at?->format('d M Y')); ?></td><td><?php echo e(number_format($order->items_count)); ?></td><td><strong><?php echo e(strtoupper($order->currency ?: 'USD')); ?> <?php echo e(number_format((float) $order->total, 2)); ?></strong></td><td><span class="customer-status <?php echo e($order->payment_status); ?>"><?php echo e(str($order->payment_status ?: 'pending')->headline()); ?></span></td><td><span class="customer-status <?php echo e($order->order_status); ?>"><?php echo e(str($order->order_status ?: 'pending')->headline()); ?></span></td><td><a class="customer-row-action" href="<?php echo e(route('customer.orders.show', $order->id)); ?>">Details <i class="fa-solid fa-chevron-right"></i></a></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('customer.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\customer-dashboard-files\_checks/../_staged/resources/views/customer/dashboard.blade.php ENDPATH**/ ?>