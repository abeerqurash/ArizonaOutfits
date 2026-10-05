<?php $__env->startSection('title', 'Order Details'); ?>

<?php $__env->startSection('content'); ?>
<?php
/*
|--------------------------------------------------------------------------
| Order values
|--------------------------------------------------------------------------
*/

$orderNumber =
$order->order_number
?? $order->invoice_number
?? ('ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));

$orderStatus = strtolower(
$order->status
?? $order->order_status
?? 'pending'
);

$paymentStatus = strtolower(
$order->payment_status
?? 'pending'
);

$customerName =
$order->customer_name
?? trim(
($order->billing_first_name ?? '')
. ' '
. ($order->billing_last_name ?? '')
)
?: $order->user?->name
?: 'Guest customer';

$customerEmail =
$order->customer_email
?? $order->billing_email
?? $order->email
?? $order->user?->email;

$customerPhone =
$order->customer_phone
?? $order->billing_phone
?? $order->phone;

$currencyCode = strtoupper(
$order->currency
?? $order->currency_code
?? 'PKR'
);

$currencySymbols = [
'PKR' => 'Rs ',
'USD' => '$',
'GBP' => '£',
'EUR' => '€',
'AED' => 'AED ',
'SAR' => 'SAR ',
'CAD' => 'CA$',
'AUD' => 'A$',
];

$currencySymbol =
$currencySymbols[$currencyCode]
?? ($currencyCode . ' ');

$money = function ($amount) use ($currencySymbol) {
return $currencySymbol . number_format((float) $amount, 2);
};

$subtotal = (float) (
$order->subtotal
?? $order->sub_total
?? $order->items->sum(
fn ($item) => (float) $item->subtotal
)
);

$discount = (float) (
$order->discount
?? $order->discount_amount
?? 0
);

$shipping = (float) (
$order->shipping
?? $order->shipping_amount
?? $order->shipping_cost
?? 0
);

$tax = (float) (
$order->tax
?? $order->tax_amount
?? 0
);

$grandTotal = (float) (
$order->total
?? $order->grand_total
?? ($subtotal - $discount + $shipping + $tax)
);

$paidAmount = (float) (
$order->paid_amount
?? (
$paymentStatus === 'paid'
? $grandTotal
: 0
)
);

$balance = max(
0,
$grandTotal - $paidAmount
);

/*
|--------------------------------------------------------------------------
| Addresses
|--------------------------------------------------------------------------
*/

$shippingName = trim(
($order->shipping_first_name ?? '')
. ' '
. ($order->shipping_last_name ?? '')
);

$shippingName =
$shippingName
?: $customerName;

$shippingAddressLines = array_filter([
$order->shipping_address
?? $order->shipping_address_line_1
?? $order->shipping_address1
?? null,

$order->shipping_address_line_2
?? $order->shipping_address2
?? null,

trim(
($order->shipping_city ?? '')
. (
!empty($order->shipping_state)
? ', ' . $order->shipping_state
: ''
)
),

trim(
($order->shipping_postcode
?? $order->shipping_zip
?? '')
. (
!empty($order->shipping_country)
? ', ' . $order->shipping_country
: ''
)
),
]);

$billingName = trim(
($order->billing_first_name ?? '')
. ' '
. ($order->billing_last_name ?? '')
);

$billingName =
$billingName
?: $customerName;

$billingAddressLines = array_filter([
$order->billing_address
?? $order->billing_address_line_1
?? $order->billing_address1
?? null,

$order->billing_address_line_2
?? $order->billing_address2
?? null,

trim(
($order->billing_city ?? '')
. (
!empty($order->billing_state)
? ', ' . $order->billing_state
: ''
)
),

trim(
($order->billing_postcode
?? $order->billing_zip
?? '')
. (
!empty($order->billing_country)
? ', ' . $order->billing_country
: ''
)
),
]);

/*
|--------------------------------------------------------------------------
| Fulfilment progress
|--------------------------------------------------------------------------
*/

$fulfilmentSteps = [
'pending' => 1,
'confirmed' => 2,
'processing' => 2,
'preparing' => 2,
'packed' => 3,
'shipped' => 4,
'out_for_delivery' => 5,
'out for delivery' => 5,
'delivered' => 6,
'completed' => 6,
];

$currentStep =
$fulfilmentSteps[$orderStatus]
?? 1;

$isCancelled = in_array(
$orderStatus,
[
'cancelled',
'canceled',
'refunded',
'failed',
],
true
);

/*
|--------------------------------------------------------------------------
| Status classes
|--------------------------------------------------------------------------
*/

$statusClass = match ($orderStatus) {
'delivered',
'completed' => 'badge-success',

'shipped',
'out_for_delivery',
'out for delivery' => 'badge-info',

'processing',
'preparing',
'packed',
'confirmed' => 'badge-warning',

'cancelled',
'canceled',
'failed',
'refunded' => 'badge-danger',

default => 'badge-neutral',
};

$paymentClass = match ($paymentStatus) {
'paid',
'completed' => 'badge-success',

'partially_paid',
'partially paid' => 'badge-warning',

'failed',
'cancelled',
'canceled',
'refunded' => 'badge-danger',

default => 'badge-neutral',
};

$trackingNumber =
$order->tracking_number
?? $order->tracking_code
?? null;

$courierTrackingNumber =
$order->courier
?? null;

$courierProvider =
$order->courier_provider
?? $order->courier_name
?? $order->shipping_provider
?? null;

$paymentMethod =
$order->payment_method
?? $order->payment_gateway
?? 'Not specified';

$transactionId =
$order->transaction_id
?? $order->payment_reference
?? null;

$shipmentTrackingMode =
$latestShipment?->tracking_mode
?? 'manual';

$shipmentProviderStatus =
$latestShipment?->provider_status;

$shipmentNormalizedStatus =
$latestShipment?->normalized_status;

$shipmentLastEventAt =
$latestShipment?->last_event_at;

$shipmentTrackingEvents =
$latestShipment?->trackingEvents
?? collect();
?>

<div class="premium-order-page">

    
    <section class="order-hero">
        <div class="order-hero-top">
            <div class="order-heading-area">
                <a
                    href="<?php echo e(route('admin.orders.index')); ?>"
                    class="order-back-link">
                    <span>←</span>
                    Back to orders
                </a>

                <div class="order-title-line">
                    <h1><?php echo e($orderNumber); ?></h1>

                    <span class="premium-badge <?php echo e($statusClass); ?>">
                        <span class="badge-dot"></span>
                        <?php echo e(ucwords(str_replace('_', ' ', $orderStatus))); ?>

                    </span>

                    <span class="premium-badge <?php echo e($paymentClass); ?>">
                        <span class="badge-dot"></span>
                        Payment:
                        <?php echo e(ucwords(str_replace('_', ' ', $paymentStatus))); ?>

                    </span>
                </div>

                <p class="order-created-text">
                    Placed
                    <?php echo e(optional($order->created_at)->format('d M Y \a\t h:i A')); ?>


                    <?php if($order->created_at): ?>
                    <span>
                        · <?php echo e($order->created_at->diffForHumans()); ?>

                    </span>
                    <?php endif; ?>
                </p>
            </div>

            <div class="order-header-actions">
                <button
                    type="button"
                    class="premium-button premium-button-light"
                    onclick="window.print()">
                    <span>🖨</span>
                    Print
                </button>

                <?php if(Route::has('admin.orders.shipping-label')): ?>
                <a
                    href="<?php echo e(route('admin.orders.shipping-label', $order)); ?>"
                    class="premium-button premium-button-light">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Shipping Label</span>
                </a>
                <?php endif; ?>
                <form
                    action="<?php echo e(route('admin.orders.email-invoice', $order)); ?>"
                    method="POST"
                    style="display:inline-block;">
                    <?php echo csrf_field(); ?>

                    <button
                        type="submit"
                        class="btn premium-button premium-button-light">
                        Email Invoice
                    </button>
                </form>
                <?php if(Route::has('admin.orders.invoice')): ?>
                <a
                    href="<?php echo e(route('admin.orders.invoice', $order)); ?>"
                    class="premium-button premium-button-light">
                    <span>↓</span>
                    Invoice
                </a>
                <?php endif; ?>
                <button
                    type="button"
                    class="premium-button premium-button-menu"
                    id="orderMoreButton">
                    More actions
                    <span>⌄</span>
                </button>

                <div
                    class="order-actions-menu"
                    id="orderActionsMenu">
                    <button
                        type="button"
                        onclick="window.print()">
                        Print order
                    </button>

                    <?php if($customerEmail): ?>
                    <button
                        type="button"
                        data-open-email-customer-modal>
                        Email customer
                    </button>
                    <?php endif; ?>

                    <button
                        type="button"
                        class="danger-menu-action"
                        data-open-delete-modal>
                        Delete order
                    </button>
                </div>
            </div>
        </div>

        <div class="order-hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-label">
                    Customer
                </span>

                <strong><?php echo e($customerName); ?></strong>

                <small>
                    <?php echo e($customerEmail ?: 'No email provided'); ?>

                </small>
            </div>

            <div class="hero-stat">
                <span class="hero-stat-label">
                    Order total
                </span>

                <strong><?php echo e($money($grandTotal)); ?></strong>

                <small>
                    <?php echo e($order->items->sum('quantity')); ?>

                    item(s)
                </small>
            </div>

            <div class="hero-stat">
                <span class="hero-stat-label">
                    Payment
                </span>

                <strong>
                    <?php echo e(ucwords(str_replace('_', ' ', $paymentStatus))); ?>

                </strong>

                <small><?php echo e($paymentMethod); ?></small>
            </div>

            <div class="hero-stat">
                <span class="hero-stat-label">
                    Fulfilment
                </span>

                <strong>
                    <?php echo e(ucwords(str_replace('_', ' ', $orderStatus))); ?>

                </strong>

                <small>
                    <?php echo e($trackingNumber ?: 'No tracking number'); ?>

                </small>
            </div>
        </div>
    </section>

    
    <section class="premium-panel fulfilment-panel">
        <div class="panel-heading">
            <div>
                <span class="panel-eyebrow">
                    Fulfilment
                </span>

                <h2>Order progress</h2>
            </div>

            <?php if($isCancelled): ?>
            <span class="premium-badge badge-danger">
                Order closed
            </span>
            <?php else: ?>
            <span class="premium-badge <?php echo e($statusClass); ?>">
                Current:
                <?php echo e(ucwords(str_replace('_', ' ', $orderStatus))); ?>

            </span>
            <?php endif; ?>
        </div>

        <?php if($isCancelled): ?>
        <div class="cancelled-order-state">
            <span class="cancelled-state-icon">!</span>

            <div>
                <strong>
                    This order is
                    <?php echo e(str_replace('_', ' ', $orderStatus)); ?>.
                </strong>

                <p>
                    The normal fulfilment process has been stopped.
                </p>
            </div>
        </div>
        <?php else: ?>
        <div class="fulfilment-progress">
            <?php
            $steps = [
            [
            'number' => 1,
            'label' => 'Order placed',
            'icon' => '✓',
            ],
            [
            'number' => 2,
            'label' => 'Processing',
            'icon' => '⚙',
            ],
            [
            'number' => 3,
            'label' => 'Packed',
            'icon' => '□',
            ],
            [
            'number' => 4,
            'label' => 'Shipped',
            'icon' => '→',
            ],
            [
            'number' => 5,
            'label' => 'Out for delivery',
            'icon' => '⌖',
            ],
            [
            'number' => 6,
            'label' => 'Delivered',
            'icon' => '✓',
            ],
            ];
            ?>

            <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
            $stepClass =
            $step['number'] < $currentStep
                ? 'step-complete'
                : (
                $step['number']===$currentStep
                ? 'step-current'
                : 'step-pending'
                );
                ?>

                <div class="fulfilment-step <?php echo e($stepClass); ?>">
                <div class="step-marker">
                    <?php echo e($step['number'] < $currentStep
                                ? '✓'
                                : $step['icon']); ?>

                </div>

                <span><?php echo e($step['label']); ?></span>
        </div>

        <?php if(!$loop->last): ?>
        <div
            class="step-line <?php echo e($step['number'] < $currentStep ? 'line-complete' : ''); ?>"></div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>
</section>

<div class="order-layout">

    
    <main class="order-main-column">

        
        <section class="premium-panel">
            <div class="panel-heading">
                <div>
                    <span class="panel-eyebrow">
                        Order contents
                    </span>

                    <h2>
                        Products
                        <span class="heading-count">
                            <?php echo e($order->items->count()); ?>

                        </span>
                    </h2>
                </div>

                <span class="panel-heading-meta">
                    <?php echo e($order->items->sum('quantity')); ?>

                    unit(s)
                </span>
            </div>

            <div class="premium-product-list">
                <?php $__empty_1 = true; $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                $options = $item->display_options ?? [];

                $itemSubtotal =
                $item->subtotal !== null
                ? (float) $item->subtotal
                : (
                (float) $item->price
                * (int) $item->quantity
                );

                $productName =
                $item->product_name
                ?: $item->product?->title
                ?: $item->product_title
                ?: 'Deleted product';

                $productImage =
                $item->product?->featured_image_url;

                $productUrl =
                $item->product
                && Route::has('admin.products.edit')
                ? route(
                'admin.products.edit',
                $item->product
                )
                : null;
                ?>

                <article class="premium-product-card">
                    <div class="premium-product-image">
                        <?php if($productImage): ?>
                        <img
                            src="<?php echo e($productImage); ?>"
                            alt="<?php echo e($productName); ?>"
                            loading="lazy"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                        <div
                            class="product-image-placeholder"
                            style="display:none;">
                            <span>▧</span>
                        </div>
                        <?php else: ?>
                        <div class="product-image-placeholder">
                            <span>▧</span>
                        </div>
                        <?php endif; ?>

                        <span class="product-quantity-badge">
                            <?php echo e($item->quantity); ?>

                        </span>
                    </div>

                    <div class="premium-product-content">
                        <div class="product-primary-info">
                            <?php if($productUrl): ?>
                            <a
                                href="<?php echo e($productUrl); ?>"
                                class="product-name-link">
                                <?php echo e($productName); ?>

                            </a>
                            <?php else: ?>
                            <h3><?php echo e($productName); ?></h3>
                            <?php endif; ?>

                            <div class="product-meta-row">
                                <span>
                                    SKU:
                                    <strong>
                                        <?php echo e($item->sku
                                                    ?: $item->product?->sku
                                                    ?: 'N/A'); ?>

                                    </strong>
                                </span>

                                <?php if($item->variant_id): ?>
                                <span>
                                    Variant:
                                    <strong>
                                        #<?php echo e($item->variant_id); ?>

                                    </strong>
                                </span>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($options)): ?>
                            <div class="premium-product-options">
                                <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="product-option-chip">
                                    <small>
                                        <?php echo e($option['name']); ?>

                                    </small>

                                    <strong>
                                        <?php echo e($option['value']); ?>

                                    </strong>
                                </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-price-information">
                            <div>
                                <small>Unit price</small>
                                <span>
                                    <?php echo e($money($item->price)); ?>

                                </span>
                            </div>

                            <span class="multiplication-symbol">
                                ×
                            </span>

                            <div>
                                <small>Quantity</small>
                                <span><?php echo e($item->quantity); ?></span>
                            </div>

                            <div class="product-line-total">
                                <small>Total</small>
                                <strong>
                                    <?php echo e($money($itemSubtotal)); ?>

                                </strong>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="premium-empty-state">
                    <span class="empty-state-icon">▧</span>
                    <h3>No products found</h3>
                    <p>
                        This order does not contain any product items.
                    </p>
                </div>
                <?php endif; ?>
            </div>

            
            <div class="order-totals-area">
                <div class="totals-spacer"></div>

                <div class="totals-card">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <strong><?php echo e($money($subtotal)); ?></strong>
                    </div>

                    <div class="total-row">
                        <span>
                            Discount

                            <?php if(!empty($order->coupon_code)): ?>
                            <small class="coupon-code">
                                <?php echo e($order->coupon_code); ?>

                            </small>
                            <?php endif; ?>
                        </span>

                        <strong class="<?php echo e($discount > 0 ? 'discount-value' : ''); ?>">
                            <?php echo e($discount > 0 ? '-' : ''); ?>

                            <?php echo e($money($discount)); ?>

                        </strong>
                    </div>

                    <div class="total-row">
                        <span>Shipping</span>
                        <strong><?php echo e($money($shipping)); ?></strong>
                    </div>

                    <div class="total-row">
                        <span>Tax</span>
                        <strong><?php echo e($money($tax)); ?></strong>
                    </div>

                    <div class="total-divider"></div>

                    <div class="total-row grand-total-row">
                        <span>Total</span>

                        <div>
                            <small><?php echo e($currencyCode); ?></small>
                            <strong><?php echo e($money($grandTotal)); ?></strong>
                        </div>
                    </div>

                    <?php if($paidAmount > 0): ?>
                    <div class="total-row payment-total-row">
                        <span>Paid</span>
                        <strong>
                            <?php echo e($money($paidAmount)); ?>

                        </strong>
                    </div>
                    <?php endif; ?>

                    <?php if($balance > 0): ?>
                    <div class="total-row balance-total-row">
                        <span>Balance due</span>
                        <strong>
                            <?php echo e($money($balance)); ?>

                        </strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        
        <div class="order-info-grid">
            <section class="premium-panel info-card">
                <div class="panel-heading compact-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Customer
                        </span>

                        <h2>Customer details</h2>
                    </div>

                    <span class="panel-icon">♙</span>
                </div>

                <div class="customer-profile">
                    <div class="customer-avatar">
                        <?php echo e(strtoupper(
                                mb_substr(
                                    $customerName,
                                    0,
                                    1
                                )
                            )); ?>

                    </div>

                    <div>
                        <strong><?php echo e($customerName); ?></strong>

                        <span>
                            <?php echo e($order->user
                                    ? 'Registered customer'
                                    : 'Guest checkout'); ?>

                        </span>
                    </div>
                </div>

                <div class="information-list">
                    <div class="information-row">
                        <span>Email</span>

                        <?php if($customerEmail): ?>
                        <a href="mailto:<?php echo e($customerEmail); ?>">
                            <?php echo e($customerEmail); ?>

                        </a>
                        <?php else: ?>
                        <strong>Not provided</strong>
                        <?php endif; ?>
                    </div>

                    <div class="information-row">
                        <span>Phone</span>

                        <?php if($customerPhone): ?>
                        <a href="tel:<?php echo e($customerPhone); ?>">
                            <?php echo e($customerPhone); ?>

                        </a>
                        <?php else: ?>
                        <strong>Not provided</strong>
                        <?php endif; ?>
                    </div>

                    <div class="information-row">
                        <span>Customer ID</span>

                        <strong>
                            <?php echo e($order->user_id
                                    ? '#' . $order->user_id
                                    : 'Guest'); ?>

                        </strong>
                    </div>
                </div>
            </section>

            <section class="premium-panel info-card">
                <div class="panel-heading compact-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Delivery
                        </span>

                        <h2>Shipping address</h2>
                    </div>

                    <span class="panel-icon">⌖</span>
                </div>

                <address class="premium-address">
                    <strong><?php echo e($shippingName); ?></strong>

                    <?php $__empty_1 = true; $__currentLoopData = $shippingAddressLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <span><?php echo e($line); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span>No shipping address provided.</span>
                    <?php endif; ?>

                    <?php if(
                    $order->shipping_phone
                    && $order->shipping_phone !== $customerPhone
                    ): ?>
                    <a href="tel:<?php echo e($order->shipping_phone); ?>">
                        <?php echo e($order->shipping_phone); ?>

                    </a>
                    <?php endif; ?>
                </address>
            </section>

            <section class="premium-panel info-card">
                <div class="panel-heading compact-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Billing
                        </span>

                        <h2>Billing address</h2>
                    </div>

                    <span class="panel-icon">▤</span>
                </div>

                <address class="premium-address">
                    <strong><?php echo e($billingName); ?></strong>

                    <?php $__empty_1 = true; $__currentLoopData = $billingAddressLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <span><?php echo e($line); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span>No billing address provided.</span>
                    <?php endif; ?>
                </address>
            </section>

            <section class="premium-panel info-card">
                <div class="panel-heading compact-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Payment
                        </span>

                        <h2>Payment details</h2>
                    </div>

                    <span class="premium-badge <?php echo e($paymentClass); ?>">
                        <?php echo e(ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $paymentStatus
                                )
                            )); ?>

                    </span>
                </div>

                <div class="payment-method-card">
                    <span class="payment-method-icon">
                        ▣
                    </span>

                    <div>
                        <strong>
                            <?php echo e(ucwords(
                                    str_replace(
                                        ['_', '-'],
                                        ' ',
                                        $paymentMethod
                                    )
                                )); ?>

                        </strong>

                        <span>
                            <?php echo e($transactionId
                                    ? 'Transaction recorded'
                                    : 'No transaction ID'); ?>

                        </span>
                    </div>
                </div>

                <div class="information-list">
                    <div class="information-row">
                        <span>Transaction ID</span>

                        <strong class="breakable-value">
                            <?php echo e($transactionId ?: 'N/A'); ?>

                        </strong>
                    </div>

                    <div class="information-row">
                        <span>Paid amount</span>

                        <strong>
                            <?php echo e($money($paidAmount)); ?>

                        </strong>
                    </div>

                    <div class="information-row">
                        <span>Balance</span>

                        <strong>
                            <?php echo e($money($balance)); ?>

                        </strong>
                    </div>
                </div>
            </section>
        </div>

        
        <?php if(!empty(trim((string) ($order->order_notes ?? '')))): ?>
        <section class="premium-panel customer-order-note-panel">
            <div class="panel-heading compact-heading">
                <div>
                    <span class="panel-eyebrow">
                        Customer message
                    </span>

                    <h2>Checkout order note</h2>
                </div>

                <span class="panel-icon">✎</span>
            </div>

            <div class="customer-order-note">
                <?php echo nl2br(e($order->order_notes)); ?>

            </div>
        </section>
        <?php endif; ?>

        
        <section class="premium-panel">
            <div class="panel-heading">
                <div>
                    <span class="panel-eyebrow">
                        Internal communication
                    </span>

                    <h2>
                        Order notes
                        <span class="heading-count">
                            <?php echo e($order->notes?->count() ?? 0); ?>

                        </span>
                    </h2>
                </div>
            </div>

            <form
                action="<?php echo e(route('admin.orders.notes.store', $order)); ?>"
                method="POST"
                class="note-form"
                id="orderNoteForm">
                <?php echo csrf_field(); ?>

                <div class="note-compose">
                    <div class="note-avatar">
                        <?php echo e(strtoupper(
                                mb_substr(
                                    auth()->user()?->name
                                    ?? 'A',
                                    0,
                                    1
                                )
                            )); ?>

                    </div>

                    <div class="note-input-wrapper">
                        <textarea
                            name="note"
                            id="orderNoteInput"
                            rows="3"
                            placeholder="Add a private note about this order..."
                            required><?php echo e(old('note')); ?></textarea>

                        <div class="note-form-footer">
                            <small>
                                Only administrators can see this note.
                            </small>

                            <button
                                type="submit"
                                class="premium-button premium-button-primary"
                                id="addNoteButton">
                                Add note
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div
                class="notes-container"
                id="orderNotesContainer">
                <?php $__empty_1 = true; $__currentLoopData = $order->notes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article
                    class="note-message"
                    data-note-id="<?php echo e($note->id); ?>">
                    <div class="note-avatar">
                        <?php echo e(strtoupper(
                                    mb_substr(
                                        $note->user?->name
                                        ?? 'A',
                                        0,
                                        1
                                    )
                                )); ?>

                    </div>

                    <div class="note-message-content">
                        <div class="note-message-header">
                            <div>
                                <strong>
                                    <?php echo e($note->user?->name
                                                ?? 'Administrator'); ?>

                                </strong>

                                <span>
                                    <?php echo e(optional($note->created_at)
                                                ->format('d M Y, h:i A')); ?>

                                </span>
                            </div>

                            <form
                                action="<?php echo e(route(
                                            'admin.orders.notes.destroy',
                                            [$order, $note]
                                        )); ?>"
                                method="POST"
                                class="delete-note-form">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>

                                <button
                                    type="submit"
                                    class="delete-note-button"
                                    title="Delete note">
                                    ×
                                </button>
                            </form>
                        </div>

                        <p><?php echo e($note->note); ?></p>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div
                    class="premium-empty-state small-empty-state"
                    id="notesEmptyState">
                    <span class="empty-state-icon">✎</span>

                    <h3>No notes yet</h3>

                    <p>
                        Add an internal note to keep your team informed.
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        
        <section class="premium-panel">
            <div class="panel-heading">
                <div>
                    <span class="panel-eyebrow">
                        Order history
                    </span>

                    <h2>Activity timeline</h2>
                </div>

                <span class="panel-heading-meta">
                    Latest first
                </span>
            </div>

            <div class="activity-timeline">
                <?php $__empty_1 = true; $__currentLoopData = $order->activities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                $activityType = strtolower(
                $activity->type
                ?? $activity->event
                ?? 'update'
                );

                $activityIcon = match (true) {
                str_contains($activityType, 'payment') => '₨',
                str_contains($activityType, 'status') => '↻',
                str_contains($activityType, 'tracking') => '→',
                str_contains($activityType, 'note') => '✎',
                str_contains($activityType, 'create') => '+',
                str_contains($activityType, 'delete') => '×',
                default => '•',
                };
                ?>

                <article class="activity-item">
                    <div class="activity-marker">
                        <?php echo e($activityIcon); ?>

                    </div>

                    <div class="activity-content">
                        <div class="activity-title-row">
                            <strong>
                                <?php echo e($activity->description
                                            ?? $activity->message
                                            ?? ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $activityType
                                                )
                                            )); ?>

                            </strong>

                            <span>
                                <?php echo e(optional($activity->created_at)
                                            ->diffForHumans()); ?>

                            </span>
                        </div>

                        <?php if(
                        !empty($activity->old_value)
                        || !empty($activity->new_value)
                        ): ?>
                        <div class="activity-change">
                            <?php if(!empty($activity->old_value)): ?>
                            <span>
                                <?php echo e($activity->old_value); ?>

                            </span>
                            <?php endif; ?>

                            <?php if(
                            !empty($activity->old_value)
                            && !empty($activity->new_value)
                            ): ?>
                            <b>→</b>
                            <?php endif; ?>

                            <?php if(!empty($activity->new_value)): ?>
                            <span class="new-activity-value">
                                <?php echo e($activity->new_value); ?>

                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <small>
                            By
                            <?php echo e($activity->user?->name
                                        ?? 'System'); ?>


                            ·

                            <?php echo e(optional($activity->created_at)
                                        ->format('d M Y, h:i A')); ?>

                        </small>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="premium-empty-state">
                    <span class="empty-state-icon">↻</span>

                    <h3>No activity recorded</h3>

                    <p>
                        Order changes will appear here.
                    </p>
                </div>
                <?php endif; ?>

                <article class="activity-item activity-order-created">
                    <div class="activity-marker">
                        ✓
                    </div>

                    <div class="activity-content">
                        <div class="activity-title-row">
                            <strong>Order created</strong>

                            <span>
                                <?php echo e(optional($order->created_at)
                                        ->diffForHumans()); ?>

                            </span>
                        </div>

                        <small>
                            <?php echo e(optional($order->created_at)
                                    ->format('d M Y, h:i A')); ?>

                        </small>
                    </div>
                </article>
            </div>
        </section>
    </main>

    
    <aside class="order-sidebar">
        <form
            action="<?php echo e(route('admin.orders.update', $order)); ?>"
            method="POST"
            class="premium-panel order-management-panel"
            id="orderManagementForm">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="panel-heading">
                <div>
                    <span class="panel-eyebrow">
                        Management
                    </span>

                    <h2>Update order</h2>
                </div>

                <span class="panel-icon">⚙</span>
            </div>

            <div class="premium-form-group">
                <label for="order_status">
                    Order status
                </label>

                <select
                    name="order_status"
                    id="order_status"
                    class="premium-select">
                    <?php $__currentLoopData = [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'processing' => 'Processing',
                    'packed' => 'Packed',
                    'shipped' => 'Shipped',
                    'out_for_delivery' => 'Out for delivery',
                    'delivered' => 'Delivered',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'refunded' => 'Refunded',
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option
                        value="<?php echo e($value); ?>"
                        <?php if(
                        old( 'order_status' ,
                        $orderStatus
                        )===$value
                        ): echo 'selected'; endif; ?>
                        <?php if(
                            in_array($value, ['cancelled', 'refunded'], true)
                            && $orderStatus !== $value
                        ): echo 'disabled'; endif; ?>>
                        <?php echo e($label); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="premium-form-group">
                <label for="payment_status">
                    Payment status
                </label>

                <select
                    name="payment_status"
                    id="payment_status"
                    class="premium-select">
                    <?php $__currentLoopData = [
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'partially_paid' => 'Partially paid',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                    'cancelled' => 'Cancelled',
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option
                        value="<?php echo e($value); ?>"
                        <?php if(
                        old( 'payment_status' ,
                        $paymentStatus
                        )===$value
                        ): echo 'selected'; endif; ?>>
                        <?php echo e($label); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-divider"></div>

            <div class="premium-form-group">
                <label for="tracking_number_display">
                    ArizonaOutfits tracking number
                </label>

                <div class="input-with-action">
                    <input
                        type="text"
                        id="tracking_number_display"
                        class="premium-input premium-input-readonly"
                        value="<?php echo e($trackingNumber ?: 'Not generated'); ?>"
                        readonly
                        aria-readonly="true">

                    <?php if($trackingNumber): ?>
                    <button
                        type="button"
                        id="copyTrackingButton"
                        title="Copy ArizonaOutfits tracking number">
                        Copy
                    </button>
                    <?php endif; ?>
                </div>

                <small class="field-help-text">
                    Generated by ArizonaOutfits after checkout. This code cannot be changed here.
                </small>
            </div>

            <div class="premium-form-group">
                <label for="courier_provider">
                    Courier provider
                </label>

                <input
                    type="text"
                    name="courier_provider"
                    id="courier_provider"
                    class="premium-input"
                    value="<?php echo e(old('courier_provider', $courierProvider)); ?>"
                    maxlength="100"
                    placeholder="For example, DHL, Leopards, TCS or Bykea">

                <small class="field-help-text">
                    Enter the courier or rider service handling this shipment.
                </small>
            </div>

            <div class="premium-form-group">
                <label for="courier">
                    Courier tracking number
                </label>

                <input
                    type="text"
                    name="courier"
                    id="courier"
                    class="premium-input"
                    value="<?php echo e(old('courier', $courierTrackingNumber)); ?>"
                    maxlength="255"
                    placeholder="Enter courier/rider tracking number">

                <small class="field-help-text">
                    Enter the tracking ID supplied by the courier or rider.
                </small>
            </div>

            <?php if(
            property_exists($order, 'admin_note')
            || array_key_exists(
            'admin_note',
            $order->getAttributes()
            )
            ): ?>
            <div class="premium-form-group">
                <label for="admin_note">
                    Admin message
                </label>

                <textarea
                    name="admin_note"
                    id="admin_note"
                    class="premium-textarea"
                    rows="4"
                    placeholder="Optional internal message"><?php echo e(old(
                            'admin_note',
                            $order->admin_note
                        )); ?></textarea>
            </div>
            <?php endif; ?>

            <label class="premium-checkbox">
                <input
                    type="checkbox"
                    name="notify_customer"
                    value="1"
                    <?php if(old('notify_customer')): echo 'checked'; endif; ?>>

                <span class="custom-checkbox"></span>

                <span>
                    Notify customer about this update
                </span>
            </label>

            <button
                type="submit"
                class="premium-button premium-button-primary full-width-button"
                id="saveOrderButton">
                Save changes
            </button>

            <small class="management-help-text">
                Changes are recorded in the activity timeline.
            </small>
        </form>

        
        <section class="premium-panel sidebar-summary-panel">
            <div class="panel-heading compact-heading">
                <div>
                    <span class="panel-eyebrow">
                        Shipment
                    </span>

                    <h2>Tracking</h2>
                </div>

                <span class="panel-icon">→</span>
            </div>

            <?php if($trackingNumber): ?>
            <div class="tracking-card">
                <span>ArizonaOutfits tracking number</span>

                <strong id="trackingDisplay">
                    <?php echo e($trackingNumber); ?>

                </strong>

                <div class="tracking-card-footer">
                    <span>
                        Permanent customer tracking code
                    </span>

                    <button
                        type="button"
                        id="copyTrackingCardButton">
                        Copy
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="sidebar-empty-message">
                <span>→</span>

                <p>
                    No ArizonaOutfits tracking number is available for this order.
                </p>
            </div>
            <?php endif; ?>

            <?php if($latestShipment): ?>
            <div class="courier-shipment-summary">
                <div class="courier-summary-row">
                    <span>Courier provider</span>
                    <strong><?php echo e($latestShipment->courier_provider ?: ($courierProvider ?: 'Not specified')); ?></strong>
                </div>

                <div class="courier-summary-row">
                    <span>Courier tracking number</span>
                    <strong class="breakable-value">
                        <?php echo e($latestShipment->courier_tracking_number ?: ($courierTrackingNumber ?: 'Not provided')); ?>

                    </strong>
                </div>

                <div class="courier-summary-row">
                    <span>Tracking mode</span>
                    <strong>
                        <?php echo e(ucwords(str_replace('_', ' ', $shipmentTrackingMode))); ?>

                    </strong>
                </div>

                <div class="courier-summary-row">
                    <span>Provider status</span>
                    <strong class="breakable-value">
                        <?php echo e($shipmentProviderStatus ?: 'No provider status yet'); ?>

                    </strong>
                </div>

                <div class="courier-summary-row">
                    <span>ArizonaOutfits status</span>
                    <strong>
                        <?php echo e($shipmentNormalizedStatus
                            ? ucwords(str_replace('_', ' ', $shipmentNormalizedStatus))
                            : 'Not normalized yet'); ?>

                    </strong>
                </div>

                <div class="courier-summary-row">
                    <span>Last courier event</span>
                    <strong>
                        <?php echo e($shipmentLastEventAt
                            ? $shipmentLastEventAt->format('d M Y, h:i A')
                            : 'No courier event yet'); ?>

                    </strong>
                </div>
            </div>

            <div class="shipment-events-block">
                <div class="shipment-events-heading">
                    <div>
                        <span>Internal courier history</span>
                        <strong><?php echo e($shipmentTrackingEvents->count()); ?> event<?php echo e($shipmentTrackingEvents->count() === 1 ? '' : 's'); ?></strong>
                    </div>

                    <small>Admin only</small>
                </div>

                <?php $__empty_1 = true; $__currentLoopData = $shipmentTrackingEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trackingEvent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="shipment-event-item">
                    <div class="shipment-event-marker"></div>

                    <div class="shipment-event-content">
                        <div class="shipment-event-topline">
                            <strong>
                                <?php echo e($trackingEvent->provider_status ?: 'Courier event'); ?>

                            </strong>

                            <span>
                                <?php echo e(($trackingEvent->event_time ?? $trackingEvent->received_at)
                                    ? ($trackingEvent->event_time ?? $trackingEvent->received_at)->format('d M Y, h:i A')
                                    : 'Time unavailable'); ?>

                            </span>
                        </div>

                        <div class="shipment-event-meta">
                            <span>
                                ArizonaOutfits:
                                <strong>
                                    <?php echo e($trackingEvent->normalized_status
                                        ? ucwords(str_replace('_', ' ', $trackingEvent->normalized_status))
                                        : 'Unmapped / internal review'); ?>

                                </strong>
                            </span>

                            <?php if($trackingEvent->location): ?>
                            <span>
                                Location:
                                <strong><?php echo e($trackingEvent->location); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>

                        <?php if($trackingEvent->description): ?>
                        <p><?php echo e($trackingEvent->description); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="courier-shipment-empty shipment-events-empty">
                    No courier tracking events have been recorded for this shipment yet.
                </div>
                <?php endif; ?>
            </div>
            <?php elseif($courierProvider || $courierTrackingNumber): ?>
            <div class="courier-shipment-summary">
                <div class="courier-summary-row">
                    <span>Courier provider</span>
                    <strong><?php echo e($courierProvider ?: 'Not specified'); ?></strong>
                </div>

                <div class="courier-summary-row">
                    <span>Courier tracking number</span>
                    <strong class="breakable-value">
                        <?php echo e($courierTrackingNumber ?: 'Not provided'); ?>

                    </strong>
                </div>
            </div>
            <?php else: ?>
            <div class="courier-shipment-empty">
                Courier shipment details have not been added yet.
            </div>
            <?php endif; ?>
        </section>

        
        <section class="premium-panel sidebar-summary-panel">
            <div class="panel-heading compact-heading">
                <div>
                    <span class="panel-eyebrow">
                        Financial
                    </span>

                    <h2>Payment summary</h2>
                </div>

                <span class="premium-badge <?php echo e($paymentClass); ?>">
                    <?php echo e(ucwords(
                            str_replace(
                                '_',
                                ' ',
                                $paymentStatus
                            )
                        )); ?>

                </span>
            </div>

            <div class="sidebar-payment-summary">
                <div>
                    <span>Order total</span>
                    <strong><?php echo e($money($grandTotal)); ?></strong>
                </div>

                <div>
                    <span>Paid</span>
                    <strong><?php echo e($money($paidAmount)); ?></strong>
                </div>

                <div class="sidebar-balance-row">
                    <span>Balance</span>
                    <strong><?php echo e($money($balance)); ?></strong>
                </div>
            </div>
        </section>

        
        <section class="premium-panel danger-zone-panel">
            <div>
                <span class="panel-eyebrow danger-eyebrow">
                    Order lifecycle
                </span>

                <h2>Cancel / Refund</h2>

                <p>
                    Cancellation is only for unpaid orders. Paid orders must use
                    the refund workflow so payment and inventory stay synchronized.
                </p>
            </div>

            <?php
                $isPaidOrder = in_array(
                    (string) $paymentStatus,
                    ['paid', 'completed', 'succeeded'],
                    true
                );

                $isClosedOrder = in_array(
                    (string) $orderStatus,
                    ['cancelled', 'refunded'],
                    true
                );

                $isStripeOrder =
                    $order->payment_provider === 'stripe'
                    || $order->payment_method === 'stripe';

                $isBankTransferOrder =
                    $order->payment_provider === 'bank_transfer'
                    || $order->payment_method === 'bank_transfer';
            ?>

            <?php if(!$isClosedOrder && !$isPaidOrder): ?>
                <form
                    action="<?php echo e(route('admin.orders.cancel', $order)); ?>"
                    method="POST"
                    class="order-lifecycle-form">
                    <?php echo csrf_field(); ?>

                    <div class="premium-form-group">
                        <label for="cancel_admin_notes">
                            Cancellation note
                        </label>

                        <textarea
                            name="admin_notes"
                            id="cancel_admin_notes"
                            class="premium-input"
                            rows="3"
                            maxlength="5000"
                            placeholder="Optional internal reason for cancellation"></textarea>
                    </div>

                    <button
                        type="submit"
                        class="premium-button premium-button-danger full-width-button"
                        onclick="return confirm('Cancel this unpaid order?');">
                        Cancel unpaid order
                    </button>
                </form>
            <?php elseif(!$isClosedOrder && $isPaidOrder && ($isStripeOrder || $isBankTransferOrder)): ?>
                <form
                    action="<?php echo e(route('admin.orders.refund', $order)); ?>"
                    method="POST"
                    class="order-lifecycle-form">
                    <?php echo csrf_field(); ?>

                    <?php if($isBankTransferOrder): ?>
                        <div class="premium-form-group">
                            <label for="refund_reference">
                                Bank refund reference
                            </label>

                            <input
                                type="text"
                                name="refund_reference"
                                id="refund_reference"
                                class="premium-input"
                                maxlength="255"
                                required
                                placeholder="Bank transaction/reference number">
                        </div>
                    <?php endif; ?>

                    <div class="premium-form-group">
                        <label for="refund_admin_notes">
                            Refund note
                        </label>

                        <textarea
                            name="admin_notes"
                            id="refund_admin_notes"
                            class="premium-input"
                            rows="3"
                            maxlength="5000"
                            placeholder="Optional internal refund note"></textarea>
                    </div>

                    <button
                        type="submit"
                        class="premium-button premium-button-danger full-width-button"
                        onclick="return confirm('Issue a FULL refund for this paid order? This action cannot be undone from this screen.');">
                        <?php echo e($isStripeOrder ? 'Issue full Stripe refund' : 'Confirm bank-transfer refund'); ?>

                    </button>
                </form>
            <?php else: ?>
                <div class="sidebar-empty-message">
                    <p>
                        This order is already <?php echo e(ucwords(str_replace('_', ' ', $orderStatus))); ?>.
                    </p>
                </div>
            <?php endif; ?>
        </section>

        
        <section class="premium-panel danger-zone-panel">
            <div>
                <span class="panel-eyebrow danger-eyebrow">
                    Danger zone
                </span>

                <h2>Delete order</h2>

                <p>
                    This permanently removes the order and its associated
                    records.
                </p>
            </div>

            <button
                type="button"
                class="premium-button premium-button-danger full-width-button"
                data-open-delete-modal>
                Delete this order
            </button>
        </section>
    </aside>
</div>
</div>


<div
    class="premium-modal"
    id="trackingCopiedModal"
    aria-hidden="true">
    <div
        class="premium-modal-backdrop"
        data-close-tracking-copied-modal></div>

    <div
        class="premium-modal-dialog order-feedback-dialog"
        role="status"
        aria-modal="true"
        aria-labelledby="trackingCopiedTitle">
        <button
            type="button"
            class="modal-close-button"
            data-close-tracking-copied-modal
            aria-label="Close copied message">
            ×
        </button>

        <div class="modal-feedback-icon modal-feedback-success">
            <i class="fas fa-check"></i>
        </div>

        <h2 id="trackingCopiedTitle">
            Copied
        </h2>

        <p>
            ArizonaOutfits tracking number copied to clipboard.
        </p>

        <div class="modal-actions">
            <button
                type="button"
                class="premium-button premium-button-primary"
                data-close-tracking-copied-modal>
                OK
            </button>
        </div>
    </div>
</div>


<div
    class="premium-modal"
    id="emailCustomerModal"
    aria-hidden="<?php echo e($errors->any() ? 'false' : 'true'); ?>">
    <div
        class="premium-modal-backdrop"
        data-close-email-customer-modal></div>

    <div
        class="premium-modal-dialog email-customer-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="emailCustomerTitle">
        <button
            type="button"
            class="modal-close-button"
            data-close-email-customer-modal
            aria-label="Close email customer dialog">
            ×
        </button>

        <div class="modal-email-icon">
            <i class="fas fa-envelope"></i>
        </div>

        <h2 id="emailCustomerTitle">
            Email customer
        </h2>

        <p class="email-customer-recipient">
            Send a message to
            <strong><?php echo e($customerEmail ?: 'this customer'); ?></strong>
        </p>

        <form
            action="<?php echo e(route('admin.orders.email-customer', $order)); ?>"
            method="POST"
            id="emailCustomerForm">
            <?php echo csrf_field(); ?>

            <div class="email-customer-fields">
                <div class="email-customer-field">
                    <label for="emailCustomerSubject">
                        Subject
                    </label>

                    <input
                        type="text"
                        id="emailCustomerSubject"
                        name="subject"
                        value="<?php echo e(old('subject')); ?>"
                        maxlength="255"
                        required
                        autocomplete="off"
                        placeholder="Enter email subject">
                </div>

                <div class="email-customer-field">
                    <label for="emailCustomerMessage">
                        Message
                    </label>

                    <textarea
                        id="emailCustomerMessage"
                        name="message"
                        rows="7"
                        maxlength="10000"
                        required
                        placeholder="Write your message to the customer..."><?php echo e(old('message')); ?></textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button
                    type="button"
                    class="premium-button premium-button-light"
                    data-close-email-customer-modal>
                    Cancel
                </button>

                <button
                    type="submit"
                    class="premium-button premium-button-primary"
                    id="sendCustomerEmailButton">
                    Send email
                </button>
            </div>
        </form>
    </div>
</div>


<?php if(session('success') || session('error') || session('warning') || $errors->any()): ?>
<div
    class="premium-modal is-open"
    id="orderFeedbackModal"
    aria-hidden="false">
    <div
        class="premium-modal-backdrop"
        data-close-feedback-modal></div>

    <div
        class="premium-modal-dialog order-feedback-dialog"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="orderFeedbackTitle">
        <button
            type="button"
            class="modal-close-button"
            data-close-feedback-modal
            aria-label="Close message">
            ×
        </button>

        <?php if(session('success')): ?>
            <div class="modal-feedback-icon modal-feedback-success">
                <i class="fas fa-check"></i>
            </div>
            <h2 id="orderFeedbackTitle">Success</h2>
            <p><?php echo e(session('success')); ?></p>
        <?php elseif(session('warning')): ?>
            <div class="modal-feedback-icon modal-feedback-warning">!</div>
            <h2 id="orderFeedbackTitle">Notice</h2>
            <p><?php echo e(session('warning')); ?></p>
        <?php else: ?>
            <div class="modal-feedback-icon modal-feedback-danger">!</div>
            <h2 id="orderFeedbackTitle">
                <?php echo e($errors->any() ? 'Please check the form' : 'Error'); ?>

            </h2>

            <?php if($errors->any()): ?>
                <div class="feedback-error-list">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p><?php echo e(session('error')); ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="modal-actions">
            <button
                type="button"
                class="premium-button premium-button-primary"
                data-close-feedback-modal>
                OK
            </button>
        </div>
    </div>
</div>
<?php endif; ?>


<div
    class="premium-modal"
    id="deleteOrderModal"
    aria-hidden="true">
    <div
        class="premium-modal-backdrop"
        data-close-delete-modal></div>

    <div
        class="premium-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteOrderTitle">
        <button
            type="button"
            class="modal-close-button"
            data-close-delete-modal>
            ×
        </button>

        <div class="modal-danger-icon">
            !
        </div>

        <h2 id="deleteOrderTitle">
            Delete <?php echo e($orderNumber); ?>?
        </h2>

        <p>
            This action cannot be undone. The order, items, notes and activity
            records may be permanently removed.
        </p>

        <form
            action="<?php echo e(route('admin.orders.destroy', $order)); ?>"
            method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>

            <div class="modal-actions">
                <button
                    type="button"
                    class="premium-button premium-button-light"
                    data-close-delete-modal>
                    Cancel
                </button>

                <button
                    type="submit"
                    class="premium-button premium-button-danger">
                    Delete order
                </button>
            </div>
        </form>
    </div>
</div>



<div
    class="premium-modal"
    id="deleteNoteModal"
    aria-hidden="true">
    <div
        class="premium-modal-backdrop"
        data-close-delete-note-modal></div>

    <div
        class="premium-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteNoteTitle">
        <button
            type="button"
            class="modal-close-button"
            data-close-delete-note-modal
            aria-label="Close delete note confirmation">
            ×
        </button>

        <div class="modal-danger-icon">
            !
        </div>

        <h2 id="deleteNoteTitle">
            Delete this note?
        </h2>

        <p>
            This note will be permanently removed from the order.
            This action cannot be undone.
        </p>

        <div class="modal-actions">
            <button
                type="button"
                class="premium-button premium-button-light"
                data-close-delete-note-modal>
                Cancel
            </button>

            <button
                type="button"
                class="premium-button premium-button-danger"
                id="confirmDeleteNoteButton">
                Delete note
            </button>
        </div>
    </div>
</div>

<style>
    :root {
        --order-bg: #f5f7fb;
        --order-card: #ffffff;
        --order-border: #e6e9f0;
        --order-text: #182033;
        --order-muted: #737b8c;
        --order-primary: #4f46e5;
        --order-primary-dark: #3730a3;
        --order-success: #15803d;
        --order-success-bg: #ecfdf3;
        --order-warning: #b45309;
        --order-warning-bg: #fff7ed;
        --order-danger: #dc2626;
        --order-danger-bg: #fef2f2;
        --order-info: #0369a1;
        --order-info-bg: #eff6ff;
        --order-shadow:
            0 12px 35px rgba(15, 23, 42, 0.06);
    }

    .premium-order-page {
        max-width: 1550px;
        margin: 0 auto;
        padding: 26px;
        color: var(--order-text);
    }

    .premium-order-page *,
    .premium-order-page *::before,
    .premium-order-page *::after {
        box-sizing: border-box;
    }

    .order-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 18px;
        margin-bottom: 20px;
        border: 1px solid;
        border-radius: 14px;
    }

    .order-alert p {
        margin: 3px 0 0;
    }

    .order-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .order-alert-success {
        color: var(--order-success);
        background: var(--order-success-bg);
        border-color: #bbf7d0;
    }

    .order-alert-danger {
        color: var(--order-danger);
        background: var(--order-danger-bg);
        border-color: #fecaca;
    }

    .order-alert-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 26px;
        width: 26px;
        height: 26px;
        color: #fff;
        background: currentColor;
        border-radius: 50%;
        font-weight: 800;
    }

    .order-alert-icon::first-letter {
        color: #fff;
    }

    .order-hero {
        padding: 26px;
        margin-bottom: 20px;
        background:
            radial-gradient(circle at top right,
                rgba(99, 102, 241, 0.15),
                transparent 31%),
            linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
        border: 1px solid var(--order-border);
        border-radius: 22px;
        box-shadow: var(--order-shadow);
    }

    .order-hero-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 25px;
    }

    .order-back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 13px;
        color: var(--order-muted);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .order-back-link:hover {
        color: var(--order-primary);
    }

    .order-title-line {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .order-title-line h1 {
        margin: 0 7px 0 0;
        color: var(--order-text);
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1.1;
        letter-spacing: -1.3px;
    }

    .order-created-text {
        margin: 12px 0 0;
        color: var(--order-muted);
        font-size: 14px;
    }

    .premium-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 30px;
        padding: 6px 11px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
    }

    .badge-dot {
        width: 7px;
        height: 7px;
        background: currentColor;
        border-radius: 50%;
    }

    .badge-success {
        color: var(--order-success);
        background: var(--order-success-bg);
        border-color: #bbf7d0;
    }

    .badge-warning {
        color: var(--order-warning);
        background: var(--order-warning-bg);
        border-color: #fed7aa;
    }

    .badge-danger {
        color: var(--order-danger);
        background: var(--order-danger-bg);
        border-color: #fecaca;
    }

    .badge-info {
        color: var(--order-info);
        background: var(--order-info-bg);
        border-color: #bfdbfe;
    }

    .badge-neutral {
        color: #596174;
        background: #f4f6f8;
        border-color: #e3e6eb;
    }

    .order-header-actions {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
    }

    .premium-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 15px;
        border: 1px solid transparent;
        border-radius: 11px;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            background-color 0.2s ease;
    }

    .premium-button:hover {
        transform: translateY(-1px);
    }

    .premium-button-light {
        color: var(--order-text);
        background: #fff;
        border-color: var(--order-border);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }

    .premium-button-light:hover {
        background: #f8fafc;
    }

    .premium-button-menu {
        color: #fff;
        background: #1f2937;
    }

    .premium-button-primary {
        color: #fff;
        background:
            linear-gradient(135deg,
                var(--order-primary),
                #6366f1);
        box-shadow:
            0 9px 20px rgba(79, 70, 229, 0.2);
    }

    .premium-button-primary:hover {
        background:
            linear-gradient(135deg,
                var(--order-primary-dark),
                var(--order-primary));
    }

    .premium-button-danger {
        color: #fff;
        background: var(--order-danger);
    }

    .order-actions-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 20;
        display: none;
        min-width: 190px;
        padding: 7px;
        background: #fff;
        border: 1px solid var(--order-border);
        border-radius: 12px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
    }

    .order-actions-menu.is-open {
        display: block;
    }

    .order-actions-menu a,
    .order-actions-menu button {
        display: block;
        width: 100%;
        padding: 10px 11px;
        color: var(--order-text);
        background: transparent;
        border: 0;
        border-radius: 8px;
        font: inherit;
        font-size: 13px;
        font-weight: 700;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }

    .order-actions-menu a:hover,
    .order-actions-menu button:hover {
        background: #f5f7fb;
    }

    .order-actions-menu .danger-menu-action {
        color: var(--order-danger);
    }

    .order-hero-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 25px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid var(--order-border);
        border-radius: 15px;
    }

    .hero-stat {
        min-width: 0;
        padding: 17px 19px;
        border-right: 1px solid var(--order-border);
    }

    .hero-stat:last-child {
        border-right: 0;
    }

    .hero-stat-label,
    .hero-stat small {
        display: block;
        color: var(--order-muted);
        font-size: 12px;
    }

    .hero-stat strong {
        display: block;
        margin: 6px 0 5px;
        overflow: hidden;
        font-size: 15px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .premium-panel {
        padding: 22px;
        background: var(--order-card);
        border: 1px solid var(--order-border);
        border-radius: 18px;
        box-shadow: var(--order-shadow);
    }

    .customer-order-note-panel {
        margin-top: 20px;
    }

    .customer-order-note {
        padding: 16px 18px;
        color: var(--order-text);
        background: #f8fafc;
        border: 1px solid var(--order-border);
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.7;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    .fulfilment-panel {
        margin-bottom: 20px;
    }

    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 20px;
    }

    .compact-heading {
        margin-bottom: 17px;
    }

    .panel-heading h2 {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 3px 0 0;
        color: var(--order-text);
        font-size: 19px;
        line-height: 1.25;
    }

    .panel-eyebrow {
        display: block;
        color: var(--order-muted);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1.1px;
        text-transform: uppercase;
    }

    .panel-heading-meta {
        color: var(--order-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 10px;
        font-weight: 900;
    }

    .heading-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 25px;
        height: 25px;
        padding: 0 7px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 999px;
        font-size: 11px;
    }

    .fulfilment-progress {
        display: flex;
        align-items: flex-start;
        padding: 8px 4px 2px;
        overflow-x: auto;
    }

    .fulfilment-step {
        flex: 0 0 105px;
        text-align: center;
    }

    .step-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        margin: 0 auto 10px;
        border: 2px solid;
        border-radius: 50%;
        font-size: 13px;
        font-weight: 900;
    }

    .fulfilment-step span {
        display: block;
        color: var(--order-muted);
        font-size: 11px;
        font-weight: 800;
        line-height: 1.35;
    }

    .step-complete .step-marker {
        color: #fff;
        background: var(--order-success);
        border-color: var(--order-success);
    }

    .step-complete span {
        color: var(--order-success);
    }

    .step-current .step-marker {
        color: #fff;
        background: var(--order-primary);
        border-color: var(--order-primary);
        box-shadow:
            0 0 0 6px rgba(79, 70, 229, 0.1);
    }

    .step-current span {
        color: var(--order-primary);
    }

    .step-pending .step-marker {
        color: #a5adbb;
        background: #fff;
        border-color: #dfe3ea;
    }

    .step-line {
        flex: 1 0 30px;
        height: 2px;
        margin-top: 18px;
        background: #e6e9ef;
    }

    .step-line.line-complete {
        background: var(--order-success);
    }

    .cancelled-order-state {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px;
        color: var(--order-danger);
        background: var(--order-danger-bg);
        border: 1px solid #fecaca;
        border-radius: 14px;
    }

    .cancelled-order-state p {
        margin: 4px 0 0;
        color: #991b1b;
    }

    .cancelled-state-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        color: #fff;
        background: var(--order-danger);
        border-radius: 50%;
        font-weight: 900;
    }

    .order-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 355px;
        align-items: start;
        gap: 20px;
    }

    .order-main-column {
        display: grid;
        min-width: 0;
        gap: 20px;
    }

    .order-sidebar {
        position: sticky;
        top: 20px;
        display: grid;
        gap: 20px;
    }

    .premium-product-list {
        border: 1px solid var(--order-border);
        border-radius: 15px;
        overflow: hidden;
    }

    .premium-product-card {
        display: flex;
        gap: 17px;
        padding: 18px;
        border-bottom: 1px solid var(--order-border);
    }

    .premium-product-card:last-child {
        border-bottom: 0;
    }

    .premium-product-image {
        position: relative;
        flex: 0 0 86px;
        width: 86px;
        height: 96px;
    }

    .premium-product-image img,
    .product-image-placeholder {
        width: 100%;
        height: 100%;
        border: 1px solid var(--order-border);
        border-radius: 12px;
        object-fit: cover;
    }

    .product-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        background:
            linear-gradient(145deg, #f8fafc, #eef2f7);
        font-size: 24px;
    }

    .product-quantity-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 25px;
        height: 25px;
        padding: 0 7px;
        color: #fff;
        background: #1f2937;
        border: 2px solid #fff;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
    }

    .premium-product-content {
        display: flex;
        flex: 1;
        align-items: center;
        justify-content: space-between;
        min-width: 0;
        gap: 20px;
    }

    .product-primary-info {
        min-width: 0;
    }

    .product-primary-info h3,
    .product-name-link {
        display: block;
        margin: 0;
        color: var(--order-text);
        font-size: 15px;
        font-weight: 850;
        line-height: 1.4;
        text-decoration: none;
    }

    .product-name-link:hover {
        color: var(--order-primary);
    }

    .product-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 7px 15px;
        margin-top: 7px;
        color: var(--order-muted);
        font-size: 11px;
    }

    .premium-product-options {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 12px;
    }

    .product-option-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        color: #4b5563;
        background: #f7f8fb;
        border: 1px solid #e8eaf0;
        border-radius: 8px;
        font-size: 11px;
    }

    .product-option-chip small {
        color: var(--order-muted);
        font-size: inherit;
    }

    .product-option-chip strong {
        color: var(--order-text);
    }

    .product-price-information {
        display: flex;
        align-items: center;
        gap: 14px;
        white-space: nowrap;
    }

    .product-price-information>div {
        display: grid;
        gap: 4px;
    }

    .product-price-information small {
        color: var(--order-muted);
        font-size: 10px;
    }

    .product-price-information span {
        color: #4b5563;
        font-size: 12px;
        font-weight: 700;
    }

    .product-price-information .multiplication-symbol {
        color: #b3b9c5;
    }

    .product-price-information .product-line-total {
        min-width: 100px;
        padding-left: 15px;
        border-left: 1px solid var(--order-border);
        text-align: right;
    }

    .product-line-total strong {
        color: var(--order-text);
        font-size: 15px;
    }

    .order-totals-area {
        display: grid;
        grid-template-columns: 1fr minmax(310px, 420px);
        gap: 30px;
        margin-top: 20px;
    }

    .totals-card {
        padding: 18px;
        background: #fafbfc;
        border: 1px solid var(--order-border);
        border-radius: 14px;
    }

    .total-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 8px 0;
        color: var(--order-muted);
        font-size: 13px;
    }

    .total-row strong {
        color: var(--order-text);
    }

    .coupon-code {
        display: inline-flex;
        padding: 3px 6px;
        margin-left: 5px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 800;
    }

    .discount-value {
        color: var(--order-success) !important;
    }

    .total-divider {
        height: 1px;
        margin: 10px 0;
        background: var(--order-border);
    }

    .grand-total-row {
        align-items: flex-end;
        color: var(--order-text);
        font-size: 15px;
        font-weight: 850;
    }

    .grand-total-row>div {
        text-align: right;
    }

    .grand-total-row small {
        display: block;
        margin-bottom: 2px;
        color: var(--order-muted);
        font-size: 9px;
    }

    .grand-total-row strong {
        font-size: 22px;
    }

    .payment-total-row strong {
        color: var(--order-success);
    }

    .balance-total-row {
        color: var(--order-danger);
    }

    .balance-total-row strong {
        color: var(--order-danger);
    }

    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .info-card {
        min-width: 0;
    }

    .customer-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 17px;
        margin-bottom: 7px;
        border-bottom: 1px solid var(--order-border);
    }

    .customer-avatar,
    .note-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        color: #fff;
        background:
            linear-gradient(135deg,
                var(--order-primary),
                #818cf8);
        border-radius: 12px;
        font-size: 15px;
        font-weight: 900;
    }

    .customer-profile strong,
    .customer-profile span {
        display: block;
    }

    .customer-profile span {
        margin-top: 3px;
        color: var(--order-muted);
        font-size: 11px;
    }

    .information-list {
        display: grid;
    }

    .information-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f1f4;
        font-size: 12px;
    }

    .information-row:last-child {
        border-bottom: 0;
    }

    .information-row>span {
        color: var(--order-muted);
    }

    .information-row a,
    .information-row strong {
        max-width: 65%;
        color: var(--order-text);
        font-weight: 750;
        text-align: right;
        text-decoration: none;
    }

    .information-row a:hover {
        color: var(--order-primary);
    }

    .breakable-value {
        overflow-wrap: anywhere;
    }

    .premium-address {
        display: grid;
        gap: 6px;
        margin: 0;
        color: var(--order-muted);
        font-size: 13px;
        font-style: normal;
        line-height: 1.5;
    }

    .premium-address strong {
        margin-bottom: 3px;
        color: var(--order-text);
        font-size: 14px;
    }

    .premium-address a {
        color: var(--order-primary);
        text-decoration: none;
    }

    .payment-method-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px;
        margin-bottom: 8px;
        background: #f8f9fc;
        border: 1px solid var(--order-border);
        border-radius: 11px;
    }

    .payment-method-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 9px;
    }

    .payment-method-card strong,
    .payment-method-card span {
        display: block;
    }

    .payment-method-card>div>span {
        margin-top: 3px;
        color: var(--order-muted);
        font-size: 10px;
    }

    .note-form {
        margin-bottom: 22px;
    }

    .note-compose {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .note-input-wrapper {
        flex: 1;
        overflow: hidden;
        border: 1px solid var(--order-border);
        border-radius: 13px;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .note-input-wrapper:focus-within {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08);
    }

    .note-input-wrapper textarea {
        display: block;
        width: 100%;
        padding: 14px;
        color: var(--order-text);
        background: #fff;
        border: 0;
        outline: 0;
        font: inherit;
        font-size: 13px;
        resize: vertical;
    }

    .note-form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 10px 12px;
        background: #fafbfc;
        border-top: 1px solid var(--order-border);
    }

    .note-form-footer small {
        color: var(--order-muted);
        font-size: 10px;
    }

    .notes-container {
        display: grid;
        gap: 15px;
    }

    .note-message {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .note-message-content {
        flex: 1;
        padding: 14px;
        background: #f8f9fc;
        border: 1px solid var(--order-border);
        border-radius: 4px 14px 14px 14px;
    }

    .note-message-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 8px;
    }

    .note-message-header strong,
    .note-message-header span {
        display: block;
    }

    .note-message-header span {
        margin-top: 3px;
        color: var(--order-muted);
        font-size: 10px;
    }

    .note-message-content p {
        margin: 0;
        color: #4b5563;
        font-size: 13px;
        line-height: 1.65;
        white-space: pre-line;
    }

    .delete-note-form {
        margin: 0;
    }

    .delete-note-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        color: #9ca3af;
        background: transparent;
        border: 0;
        border-radius: 7px;
        font-size: 19px;
        cursor: pointer;
    }

    .delete-note-button:hover {
        color: var(--order-danger);
        background: var(--order-danger-bg);
    }

    .activity-timeline {
        position: relative;
        display: grid;
        gap: 0;
    }

    .activity-timeline::before {
        position: absolute;
        top: 19px;
        bottom: 19px;
        left: 18px;
        width: 2px;
        background: #e9ebf0;
        content: '';
    }

    .activity-item {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding-bottom: 22px;
    }

    .activity-item:last-child {
        padding-bottom: 0;
    }

    .activity-marker {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        color: var(--order-primary);
        background: #eef2ff;
        border: 4px solid #fff;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 900;
        box-shadow: 0 0 0 1px var(--order-border);
    }

    .activity-order-created .activity-marker {
        color: var(--order-success);
        background: var(--order-success-bg);
    }

    .activity-content {
        flex: 1;
        min-width: 0;
        padding-top: 4px;
    }

    .activity-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
    }

    .activity-title-row strong {
        font-size: 13px;
    }

    .activity-title-row>span {
        flex: 0 0 auto;
        color: var(--order-muted);
        font-size: 10px;
    }

    .activity-content>small {
        display: block;
        margin-top: 5px;
        color: var(--order-muted);
        font-size: 10px;
    }

    .activity-change {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
        margin-top: 8px;
    }

    .activity-change span {
        padding: 4px 7px;
        color: #5f6776;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
    }

    .activity-change .new-activity-value {
        color: var(--order-primary);
        background: #eef2ff;
    }

    .premium-empty-state {
        padding: 35px 20px;
        color: var(--order-muted);
        text-align: center;
    }

    .small-empty-state {
        padding: 25px 20px;
        background: #fafbfc;
        border: 1px dashed #d9dde5;
        border-radius: 13px;
    }

    .empty-state-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 14px;
        font-size: 20px;
    }

    .premium-empty-state h3 {
        margin: 0 0 6px;
        color: var(--order-text);
        font-size: 15px;
    }

    .premium-empty-state p {
        margin: 0;
        font-size: 12px;
    }

    .order-management-panel {
        overflow: hidden;
    }

    .premium-form-group {
        display: grid;
        gap: 7px;
        margin-bottom: 15px;
    }

    .premium-form-group label {
        color: #4b5563;
        font-size: 11px;
        font-weight: 800;
    }

    .premium-input,
    .premium-select,
    .premium-textarea {
        display: block;
        width: 100%;
        min-height: 43px;
        padding: 10px 12px;
        color: var(--order-text);
        background: #fff;
        border: 1px solid #dfe3ea;
        border-radius: 10px;
        outline: 0;
        font: inherit;
        font-size: 12px;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .premium-textarea {
        min-height: 92px;
        resize: vertical;
    }

    .premium-input:focus,
    .premium-select:focus,
    .premium-textarea:focus {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08);
    }

    .form-divider {
        height: 1px;
        margin: 20px 0;
        background: var(--order-border);
    }

    .input-with-action {
        position: relative;
    }

    .input-with-action .premium-input {
        padding-right: 65px;
    }

    .input-with-action button {
        position: absolute;
        top: 50%;
        right: 6px;
        padding: 7px 9px;
        color: var(--order-primary);
        background: #eef2ff;
        border: 0;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 850;
        transform: translateY(-50%);
        cursor: pointer;
    }

    .premium-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 18px 0;
        color: #4b5563;
        font-size: 11px;
        font-weight: 650;
        line-height: 1.45;
        cursor: pointer;
    }

    .premium-checkbox input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .custom-checkbox {
        position: relative;
        flex: 0 0 18px;
        width: 18px;
        height: 18px;
        background: #fff;
        border: 1px solid #cfd4dd;
        border-radius: 5px;
    }

    .premium-checkbox input:checked+.custom-checkbox {
        background: var(--order-primary);
        border-color: var(--order-primary);
    }

    .premium-checkbox input:checked+.custom-checkbox::after {
        position: absolute;
        top: 2px;
        left: 5px;
        width: 5px;
        height: 9px;
        border-right: 2px solid #fff;
        border-bottom: 2px solid #fff;
        content: '';
        transform: rotate(45deg);
    }

    .full-width-button {
        width: 100%;
    }

    .management-help-text {
        display: block;
        margin-top: 10px;
        color: var(--order-muted);
        font-size: 9px;
        text-align: center;
    }

    .sidebar-summary-panel {
        padding: 19px;
    }

    .tracking-card {
        padding: 14px;
        background: #f8f9fc;
        border: 1px solid var(--order-border);
        border-radius: 11px;
    }

    .tracking-card>span {
        display: block;
        color: var(--order-muted);
        font-size: 10px;
    }

    .tracking-card>strong {
        display: block;
        margin: 6px 0 12px;
        overflow-wrap: anywhere;
        font-size: 13px;
    }

    .tracking-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--order-border);
    }

    .tracking-card-footer span {
        color: var(--order-muted);
        font-size: 10px;
    }

    .tracking-card-footer button {
        padding: 5px 8px;
        color: var(--order-primary);
        background: #eef2ff;
        border: 0;
        border-radius: 6px;
        font-size: 9px;
        font-weight: 850;
        cursor: pointer;
    }

    .sidebar-empty-message {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px;
        color: var(--order-muted);
        background: #fafbfc;
        border: 1px dashed #d9dde5;
        border-radius: 11px;
    }

    .sidebar-empty-message span {
        color: var(--order-primary);
        font-size: 18px;
    }

    .sidebar-empty-message p {
        margin: 0;
        font-size: 11px;
        line-height: 1.5;
    }

    .sidebar-payment-summary {
        display: grid;
        gap: 10px;
    }

    .sidebar-payment-summary>div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        color: var(--order-muted);
        font-size: 11px;
    }

    .sidebar-payment-summary strong {
        color: var(--order-text);
    }

    .sidebar-balance-row {
        padding-top: 11px;
        border-top: 1px solid var(--order-border);
    }

    .sidebar-balance-row strong {
        color: var(--order-danger);
        font-size: 14px;
    }

    .danger-zone-panel {
        border-color: #fecaca;
    }

    .danger-zone-panel h2 {
        margin: 5px 0 8px;
        font-size: 17px;
    }

    .danger-zone-panel p {
        margin: 0 0 17px;
        color: var(--order-muted);
        font-size: 11px;
        line-height: 1.55;
    }

    .danger-eyebrow {
        color: var(--order-danger);
    }

    .premium-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .premium-modal.is-open {
        display: flex;
    }

    .premium-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.62);
        backdrop-filter: blur(3px);
    }

    .premium-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(100%, 440px);
        padding: 27px;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
        text-align: center;
    }

    .modal-close-button {
        position: absolute;
        top: 12px;
        right: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 31px;
        height: 31px;
        color: #687182;
        background: #f4f5f7;
        border: 0;
        border-radius: 8px;
        font-size: 20px;
        cursor: pointer;
    }

    .modal-danger-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 53px;
        height: 53px;
        margin: 0 auto 15px;
        color: #fff;
        background: var(--order-danger);
        border-radius: 50%;
        font-size: 22px;
        font-weight: 900;
    }

    .premium-modal-dialog h2 {
        margin: 0 0 9px;
        font-size: 21px;
    }

    .premium-modal-dialog p {
        margin: 0;
        color: var(--order-muted);
        font-size: 12px;
        line-height: 1.65;
    }

    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 23px;
    }

    .email-customer-dialog {
        width: min(100%, 560px);
    }

    .modal-email-icon,
    .modal-feedback-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 53px;
        height: 53px;
        margin: 0 auto 15px;
        color: #fff;
        border-radius: 50%;
        font-size: 20px;
        font-weight: 900;
    }

    .modal-email-icon {
        background: var(--order-primary);
    }

    .modal-feedback-success {
        background: var(--order-success);
    }

    .modal-feedback-warning {
        background: var(--order-warning);
    }

    .modal-feedback-danger {
        background: var(--order-danger);
    }

    .email-customer-recipient {
        overflow-wrap: anywhere;
    }

    .email-customer-fields {
        display: grid;
        gap: 15px;
        margin-top: 22px;
        text-align: left;
    }

    .email-customer-field {
        display: grid;
        gap: 7px;
    }

    .email-customer-field label {
        color: var(--order-text);
        font-size: 12px;
        font-weight: 800;
    }

    .email-customer-field input,
    .email-customer-field textarea {
        width: 100%;
        padding: 11px 12px;
        color: var(--order-text);
        background: #fff;
        border: 1px solid var(--order-border);
        border-radius: 10px;
        outline: none;
        font: inherit;
        font-size: 13px;
        line-height: 1.55;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .email-customer-field textarea {
        min-height: 150px;
        resize: vertical;
    }

    .email-customer-field input:focus,
    .email-customer-field textarea:focus {
        border-color: var(--order-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .feedback-error-list {
        display: grid;
        gap: 5px;
    }

    .feedback-error-list p {
        margin: 0;
    }

    @media (max-width: 575px) {
        .premium-modal {
            padding: 12px;
        }

        .premium-modal-dialog {
            padding: 24px 18px 20px;
        }

        .email-customer-dialog .modal-actions {
            flex-direction: column-reverse;
        }

        .email-customer-dialog .premium-button {
            width: 100%;
        }
    }

    @media (max-width: 1180px) {
        .order-layout {
            grid-template-columns: minmax(0, 1fr) 320px;
        }

        .order-hero-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hero-stat:nth-child(2) {
            border-right: 0;
        }

        .hero-stat:nth-child(-n + 2) {
            border-bottom: 1px solid var(--order-border);
        }

        .premium-product-content {
            align-items: flex-start;
            flex-direction: column;
        }

        .product-price-information {
            width: 100%;
            justify-content: flex-end;
        }
    }

    @media (max-width: 920px) {
        .premium-order-page {
            padding: 18px;
        }

        .order-layout {
            grid-template-columns: 1fr;
        }

        .order-sidebar {
            position: static;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .order-management-panel,
        .danger-zone-panel {
            grid-column: 1 / -1;
        }

        .order-hero-top {
            flex-direction: column;
        }

        .order-header-actions {
            width: 100%;
        }

        .order-info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 680px) {
        .premium-order-page {
            padding: 12px;
        }

        .order-hero,
        .premium-panel {
            padding: 17px;
            border-radius: 15px;
        }

        .order-title-line {
            align-items: flex-start;
        }

        .order-title-line h1 {
            width: 100%;
        }

        .order-header-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .order-header-actions .premium-button {
            width: 100%;
        }

        .order-hero-stats {
            grid-template-columns: 1fr;
        }

        .hero-stat,
        .hero-stat:nth-child(2) {
            border-right: 0;
            border-bottom: 1px solid var(--order-border);
        }

        .hero-stat:last-child {
            border-bottom: 0;
        }

        .fulfilment-step {
            flex-basis: 85px;
        }

        .premium-product-card {
            align-items: flex-start;
        }

        .premium-product-image {
            flex-basis: 68px;
            width: 68px;
            height: 78px;
        }

        .product-price-information {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 9px;
        }

        .product-price-information .multiplication-symbol {
            display: none;
        }

        .product-price-information .product-line-total {
            min-width: 0;
            padding-left: 0;
            border-left: 0;
        }

        .order-totals-area {
            grid-template-columns: 1fr;
        }

        .totals-spacer {
            display: none;
        }

        .order-sidebar {
            grid-template-columns: 1fr;
        }

        .order-management-panel,
        .danger-zone-panel {
            grid-column: auto;
        }

        .note-compose {
            flex-direction: column;
        }

        .note-input-wrapper {
            width: 100%;
        }

        .note-form-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .note-form-footer .premium-button {
            width: 100%;
        }

        .activity-title-row {
            flex-direction: column;
            gap: 4px;
        }
    }

    @media print {
        body {
            background: #fff !important;
        }

        .premium-order-page {
            max-width: none;
            padding: 0;
        }

        .order-header-actions,
        .order-sidebar,
        .note-form,
        .delete-note-button,
        .danger-zone-panel,
        .order-back-link {
            display: none !important;
        }

        .order-layout {
            display: block;
        }

        .order-hero,
        .premium-panel {
            break-inside: avoid;
            box-shadow: none;
        }

        .order-main-column {
            gap: 12px;
        }
    }

    .premium-input-readonly {
        cursor: default;
        color: #596174;
        background: #f4f6f8;
    }

    .field-help-text {
        display: block;
        margin-top: 7px;
        color: var(--order-muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .courier-shipment-summary {
        display: grid;
        gap: 10px;
        margin-top: 12px;
        padding: 14px;
        border: 1px solid var(--order-border);
        border-radius: 12px;
        background: #f8fafc;
    }

    .courier-summary-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .courier-summary-row span {
        color: var(--order-muted);
        font-size: 12px;
    }

    .courier-summary-row strong {
        max-width: 60%;
        color: var(--order-text);
        font-size: 12px;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .courier-shipment-empty {
        margin-top: 12px;
        padding: 12px 14px;
        color: var(--order-muted);
        background: #f8fafc;
        border: 1px dashed var(--order-border);
        border-radius: 12px;
        font-size: 12px;
        line-height: 1.5;
    }

    .shipment-events-block {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--order-border);
    }

    .shipment-events-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .shipment-events-heading > div {
        display: grid;
        gap: 2px;
    }

    .shipment-events-heading span {
        color: var(--order-muted);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .shipment-events-heading strong {
        color: var(--order-text);
        font-size: 13px;
    }

    .shipment-events-heading small {
        padding: 4px 8px;
        border: 1px solid var(--order-border);
        border-radius: 999px;
        color: var(--order-muted);
        background: #f8fafc;
        font-size: 10px;
        white-space: nowrap;
    }

    .shipment-event-item {
        position: relative;
        display: grid;
        grid-template-columns: 10px minmax(0, 1fr);
        gap: 10px;
        padding: 11px 0;
        border-bottom: 1px solid var(--order-border);
    }

    .shipment-event-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .shipment-event-marker {
        width: 8px;
        height: 8px;
        margin-top: 5px;
        border-radius: 50%;
        background: #635bff;
        box-shadow: 0 0 0 3px rgba(99, 91, 255, .10);
    }

    .shipment-event-content {
        min-width: 0;
    }

    .shipment-event-topline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .shipment-event-topline > strong {
        color: var(--order-text);
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .shipment-event-topline > span {
        flex: 0 0 auto;
        color: var(--order-muted);
        font-size: 10px;
        white-space: nowrap;
    }

    .shipment-event-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 10px;
        margin-top: 4px;
        color: var(--order-muted);
        font-size: 10px;
        line-height: 1.45;
    }

    .shipment-event-meta strong {
        color: var(--order-text);
        font-weight: 600;
    }

    .shipment-event-content p {
        margin: 6px 0 0;
        color: var(--order-muted);
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .shipment-events-empty {
        margin-top: 0;
    }

    @media (max-width: 575px) {
        .shipment-event-topline {
            display: grid;
        }

        .shipment-event-topline > span {
            white-space: normal;
        }
    }

</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const moreButton =
            document.getElementById('orderMoreButton');

        const actionsMenu =
            document.getElementById('orderActionsMenu');

        if (moreButton && actionsMenu) {
            moreButton.addEventListener('click', function(event) {
                event.stopPropagation();

                actionsMenu.classList.toggle('is-open');
            });

            document.addEventListener('click', function() {
                actionsMenu.classList.remove('is-open');
            });

            actionsMenu.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Tracking copy buttons
        |--------------------------------------------------------------------------
        */

        const trackingInput =
            document.getElementById('tracking_number_display');

        const copyTrackingButton =
            document.getElementById('copyTrackingButton');

        const copyTrackingCardButton =
            document.getElementById('copyTrackingCardButton');

        const trackingDisplay =
            document.getElementById('trackingDisplay');

        const trackingCopiedModal =
            document.getElementById('trackingCopiedModal');

        const closeTrackingCopiedButtons =
            document.querySelectorAll(
                '[data-close-tracking-copied-modal]'
            );

        function openTrackingCopiedModal() {
            if (!trackingCopiedModal) {
                return;
            }

            trackingCopiedModal.classList.add('is-open');
            trackingCopiedModal.setAttribute('aria-hidden', 'false');
        }

        function closeTrackingCopiedModal() {
            if (!trackingCopiedModal) {
                return;
            }

            trackingCopiedModal.classList.remove('is-open');
            trackingCopiedModal.setAttribute('aria-hidden', 'true');
        }

        closeTrackingCopiedButtons.forEach(function(button) {
            button.addEventListener(
                'click',
                closeTrackingCopiedModal
            );
        });

        async function copyTrackingNumber(button, value) {
            if (!value) {
                return;
            }

            try {
                await navigator.clipboard.writeText(value);
                openTrackingCopiedModal();
            } catch (error) {
                const temporaryInput =
                    document.createElement('textarea');

                temporaryInput.value = value;
                temporaryInput.style.position = 'fixed';
                temporaryInput.style.opacity = '0';

                document.body.appendChild(temporaryInput);

                temporaryInput.select();

                const copied =
                    document.execCommand('copy');

                temporaryInput.remove();

                if (copied) {
                    openTrackingCopiedModal();
                }
            }
        }

        if (copyTrackingButton && trackingInput) {
            copyTrackingButton.addEventListener(
                'click',
                function() {
                    copyTrackingNumber(
                        copyTrackingButton,
                        trackingInput.value.trim()
                    );
                }
            );
        }

        if (
            copyTrackingCardButton &&
            trackingDisplay
        ) {
            copyTrackingCardButton.addEventListener(
                'click',
                function() {
                    copyTrackingNumber(
                        copyTrackingCardButton,
                        trackingDisplay.textContent.trim()
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Email customer modal
        |--------------------------------------------------------------------------
        */

        const emailCustomerModal =
            document.getElementById('emailCustomerModal');

        const openEmailCustomerButtons =
            document.querySelectorAll(
                '[data-open-email-customer-modal]'
            );

        const closeEmailCustomerButtons =
            document.querySelectorAll(
                '[data-close-email-customer-modal]'
            );

        const emailCustomerSubject =
            document.getElementById('emailCustomerSubject');

        function openEmailCustomerModal() {
            if (!emailCustomerModal) {
                return;
            }

            emailCustomerModal.classList.add('is-open');
            emailCustomerModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            if (emailCustomerSubject) {
                setTimeout(function() {
                    emailCustomerSubject.focus();
                }, 50);
            }
        }

        function closeEmailCustomerModal() {
            if (!emailCustomerModal) {
                return;
            }

            emailCustomerModal.classList.remove('is-open');
            emailCustomerModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        openEmailCustomerButtons.forEach(function(button) {
            button.addEventListener(
                'click',
                function() {
                    if (actionsMenu) {
                        actionsMenu.classList.remove('is-open');
                    }

                    openEmailCustomerModal();
                }
            );
        });

        closeEmailCustomerButtons.forEach(function(button) {
            button.addEventListener(
                'click',
                closeEmailCustomerModal
            );
        });

        const feedbackModal =
            document.getElementById('orderFeedbackModal');

        const closeFeedbackButtons =
            document.querySelectorAll(
                '[data-close-feedback-modal]'
            );

        function closeFeedbackModal() {
            if (!feedbackModal) {
                return;
            }

            feedbackModal.classList.remove('is-open');
            feedbackModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        closeFeedbackButtons.forEach(function(button) {
            button.addEventListener(
                'click',
                closeFeedbackModal
            );
        });

        <?php if($errors->any()): ?>
        openEmailCustomerModal();
        <?php endif; ?>

        /*
        |--------------------------------------------------------------------------
        | Delete modal
        |--------------------------------------------------------------------------
        */

        const deleteModal =
            document.getElementById('deleteOrderModal');

        const openDeleteButtons =
            document.querySelectorAll(
                '[data-open-delete-modal]'
            );

        const closeDeleteButtons =
            document.querySelectorAll(
                '[data-close-delete-modal]'
            );

        function openDeleteModal() {
            if (!deleteModal) {
                return;
            }

            deleteModal.classList.add('is-open');
            deleteModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            if (!deleteModal) {
                return;
            }

            deleteModal.classList.remove('is-open');
            deleteModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        openDeleteButtons.forEach(function(button) {
            button.addEventListener(
                'click',
                openDeleteModal
            );
        });

        closeDeleteButtons.forEach(function(button) {
            button.addEventListener(
                'click',
                closeDeleteModal
            );
        });

        document.addEventListener(
            'keydown',
            function(event) {
                if (event.key === 'Escape') {
                    closeDeleteModal();
                    closeEmailCustomerModal();
                    closeFeedbackModal();
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate submissions
        |--------------------------------------------------------------------------
        */

        const managementForm =
            document.getElementById('orderManagementForm');

        const saveOrderButton =
            document.getElementById('saveOrderButton');

        if (managementForm && saveOrderButton) {
            managementForm.addEventListener(
                'submit',
                function() {
                    saveOrderButton.disabled = true;
                    saveOrderButton.textContent =
                        'Saving changes...';
                }
            );
        }

        const emailCustomerForm =
            document.getElementById('emailCustomerForm');

        const sendCustomerEmailButton =
            document.getElementById('sendCustomerEmailButton');

        if (emailCustomerForm && sendCustomerEmailButton) {
            emailCustomerForm.addEventListener(
                'submit',
                function() {
                    sendCustomerEmailButton.disabled = true;
                    sendCustomerEmailButton.textContent =
                        'Sending...';
                }
            );
        }

        const noteForm =
            document.getElementById('orderNoteForm');

        const addNoteButton =
            document.getElementById('addNoteButton');

        if (noteForm && addNoteButton) {
            noteForm.addEventListener(
                'submit',
                function() {
                    addNoteButton.disabled = true;
                    addNoteButton.textContent =
                        'Adding note...';
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Note deletion confirmation
        |--------------------------------------------------------------------------
        |
        | Use the same centered custom modal system as the rest of the order
        | page. Never fall back to the browser's native confirm dialog.
        |
        */

        const deleteNoteModal =
            document.getElementById('deleteNoteModal');

        const confirmDeleteNoteButton =
            document.getElementById('confirmDeleteNoteButton');

        let pendingDeleteNoteForm = null;

        function openDeleteNoteModal(form) {
            if (!deleteNoteModal) {
                return;
            }

            pendingDeleteNoteForm = form;

            deleteNoteModal.classList.add('is-open');
            deleteNoteModal.setAttribute('aria-hidden', 'false');

            document.body.classList.add('modal-open');

            window.setTimeout(function() {
                confirmDeleteNoteButton?.focus();
            }, 50);
        }

        function closeDeleteNoteModal() {
            if (!deleteNoteModal) {
                return;
            }

            deleteNoteModal.classList.remove('is-open');
            deleteNoteModal.setAttribute('aria-hidden', 'true');

            pendingDeleteNoteForm = null;

            if (
                !document.querySelector(
                    '.premium-modal.is-open'
                )
            ) {
                document.body.classList.remove('modal-open');
            }
        }

        document
            .querySelectorAll('.delete-note-form')
            .forEach(function(form) {
                form.addEventListener(
                    'submit',
                    function(event) {
                        event.preventDefault();
                        openDeleteNoteModal(form);
                    }
                );
            });

        document
            .querySelectorAll('[data-close-delete-note-modal]')
            .forEach(function(button) {
                button.addEventListener(
                    'click',
                    closeDeleteNoteModal
                );
            });

        confirmDeleteNoteButton?.addEventListener(
            'click',
            function() {
                if (!pendingDeleteNoteForm) {
                    closeDeleteNoteModal();
                    return;
                }

                const formToSubmit = pendingDeleteNoteForm;

                confirmDeleteNoteButton.disabled = true;
                confirmDeleteNoteButton.textContent = 'Deleting...';

                pendingDeleteNoteForm = null;

                formToSubmit.submit();
            }
        );

        document.addEventListener(
            'keydown',
            function(event) {
                if (
                    event.key === 'Escape'
                    && deleteNoteModal?.classList.contains('is-open')
                ) {
                    closeDeleteNoteModal();
                }
            }
        );
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\orders\show.blade.php ENDPATH**/ ?>