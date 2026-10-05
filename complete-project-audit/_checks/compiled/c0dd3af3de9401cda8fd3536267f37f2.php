<form
    id="ordersFilterForm"
    action="<?php echo e(route('admin.orders.index')); ?>"
    method="GET"
    class="admin-orders-filter-form"
>

    <div class="admin-orders-search-group">

        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="search"
            id="ordersSearchInput"
            name="search"
            value="<?php echo e($filters['search'] ?? ''); ?>"
            placeholder="Order, customer, email, phone or tracking"
            autocomplete="off"
            aria-label="Search orders"
        >

    </div>

    <select
        name="order_status"
        id="ordersOrderStatus"
        aria-label="Filter by order status"
    >
        <option value="">All order statuses</option>

        <?php $__currentLoopData = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <option
                value="<?php echo e($value); ?>"
                <?php if(
                    ($filters['order_status'] ?? '') === $value
                ): echo 'selected'; endif; ?>
            >
                <?php echo e($label); ?>

            </option>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <select
        name="payment_status"
        id="ordersPaymentStatus"
        aria-label="Filter by payment status"
    >
        <option value="">All payment statuses</option>

        <?php $__currentLoopData = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'completed' => 'Completed',
            'succeeded' => 'Succeeded',
            'failed' => 'Failed',
            'declined' => 'Declined',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <option
                value="<?php echo e($value); ?>"
                <?php if(
                    ($filters['payment_status'] ?? '') === $value
                ): echo 'selected'; endif; ?>
            >
                <?php echo e($label); ?>

            </option>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <select
        name="date_range"
        id="ordersDateRange"
        aria-label="Filter orders by date"
    >
        <option value="">All dates</option>

        <option
            value="today"
            <?php if(
                ($filters['date_range'] ?? '') === 'today'
            ): echo 'selected'; endif; ?>
        >
            Today
        </option>

        <option
            value="7days"
            <?php if(
                ($filters['date_range'] ?? '') === '7days'
            ): echo 'selected'; endif; ?>
        >
            Last 7 days
        </option>

        <option
            value="30days"
            <?php if(
                ($filters['date_range'] ?? '') === '30days'
            ): echo 'selected'; endif; ?>
        >
            Last 30 days
        </option>

        <option
            value="month"
            <?php if(
                ($filters['date_range'] ?? '') === 'month'
            ): echo 'selected'; endif; ?>
        >
            This month
        </option>

        <option
            value="year"
            <?php if(
                ($filters['date_range'] ?? '') === 'year'
            ): echo 'selected'; endif; ?>
        >
            This year
        </option>

        <option
            value="custom"
            <?php if(
                ($filters['date_range'] ?? '') === 'custom'
            ): echo 'selected'; endif; ?>
        >
            Custom dates
        </option>
    </select>

    <select
        name="sort"
        id="ordersSort"
        aria-label="Sort orders"
    >
        <option
            value="newest"
            <?php if(
                ($filters['sort'] ?? 'newest') === 'newest'
            ): echo 'selected'; endif; ?>
        >
            Newest first
        </option>

        <option
            value="oldest"
            <?php if(
                ($filters['sort'] ?? '') === 'oldest'
            ): echo 'selected'; endif; ?>
        >
            Oldest first
        </option>

        <option
            value="total_high"
            <?php if(
                ($filters['sort'] ?? '') === 'total_high'
            ): echo 'selected'; endif; ?>
        >
            Highest total
        </option>

        <option
            value="total_low"
            <?php if(
                ($filters['sort'] ?? '') === 'total_low'
            ): echo 'selected'; endif; ?>
        >
            Lowest total
        </option>
    </select>

    <div
        id="ordersCustomDateFields"
        class="admin-orders-custom-date-fields <?php echo e(($filters['date_range'] ?? '') === 'custom'
                ? 'is-visible'
                : ''); ?>"
    >

        <div class="admin-orders-date-field">

            <label for="ordersDateFrom">
                From date
            </label>

            <input
                type="date"
                id="ordersDateFrom"
                name="date_from"
                value="<?php echo e($filters['date_from'] ?? ''); ?>"
                max="<?php echo e(now()->format('Y-m-d')); ?>"
                <?php if(
                    ($filters['date_range'] ?? '') !== 'custom'
                ): echo 'disabled'; endif; ?>
            >

        </div>

        <div class="admin-orders-date-field">

            <label for="ordersDateTo">
                To date
            </label>

            <input
                type="date"
                id="ordersDateTo"
                name="date_to"
                value="<?php echo e($filters['date_to'] ?? ''); ?>"
                max="<?php echo e(now()->format('Y-m-d')); ?>"
                <?php if(
                    ($filters['date_range'] ?? '') !== 'custom'
                ): echo 'disabled'; endif; ?>
            >

        </div>

    </div>

    <div class="admin-orders-filter-actions">

        <button type="submit">
            <i class="fa-solid fa-filter"></i>
            Apply
        </button>

        <a
            href="<?php echo e(route('admin.orders.index')); ?>"
            id="ordersResetFilters"
        >
            <i class="fa-solid fa-rotate-left"></i>
            Reset
        </a>

    </div>

</form><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\orders\partials\filters.blade.php ENDPATH**/ ?>