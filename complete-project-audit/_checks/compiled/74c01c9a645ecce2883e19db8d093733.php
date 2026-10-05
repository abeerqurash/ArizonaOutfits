<?php $__env->startSection('title', 'Order '.($order->order_number ?: '#'.$order->id)); ?>

<?php $__env->startSection('page-heading', 'Order Details'); ?>



<?php $__env->startSection('content'); ?>



<?php

    $paymentMetadata = $order->payment_metadata ?? [];



    if (is_string($paymentMetadata)) {

        $decodedPaymentMetadata = json_decode($paymentMetadata, true);



        $paymentMetadata =

            json_last_error() === JSON_ERROR_NONE

            && is_array($decodedPaymentMetadata)

                ? $decodedPaymentMetadata

                : [];

    }



    if (!is_array($paymentMetadata)) {

        $paymentMetadata = [];

    }



    $isBankTransfer =

        ($order->payment_method ?? '') === 'bank_transfer';



    $bankName =

        $paymentMetadata['bank_name'] ?? null;



    $bankAccountName =

        $paymentMetadata['account_name'] ?? null;



    $bankAccountNumber =

        $paymentMetadata['account_number'] ?? null;



    $bankIban =

        $paymentMetadata['iban'] ?? null;



    $bankSwiftCode =

        $paymentMetadata['swift_code'] ?? null;



    $bankBranchName =

        $paymentMetadata['branch_name'] ?? null;



    $bankInstructions =

        $paymentMetadata['instructions'] ?? null;



    $paymentReference =

        $order->payment_reference

        ?: $order->order_number;

?>



<header class="customer-page-heading">

    <div>

        <span>Order details</span>



        <h2>

            <?php echo e($order->order_number ?: 'Order #'.$order->id); ?>


        </h2>



        <p>

            Placed

            <?php echo e($order->created_at?->format('d F Y \a\t g:i A')); ?>.

        </p>

    </div>



    <div class="customer-heading-actions">

        <a

            class="secondary"

            href="<?php echo e(route('customer.orders.invoice', $order->id)); ?>"

            target="_blank"

        >

            <i class="fa-regular fa-eye"></i>

            View invoice

        </a>



        <a

            href="<?php echo e(route('customer.orders.invoice.download', $order->id)); ?>"

        >

            <i class="fa-solid fa-download"></i>

            Download PDF

        </a>

    </div>

</header>



<?php if($isBankTransfer): ?>

<section class="customer-panel customer-bank-transfer-panel">

    <header class="customer-panel-heading">

        <div>

            <span>Payment instructions</span>

            <h3>Bank transfer details</h3>

        </div>



        <span class="customer-bank-icon">

            <i class="fa-solid fa-building-columns"></i>

        </span>

    </header>



    <div class="customer-bank-transfer-body">

        <div class="customer-bank-notice">

            <span>

                <i class="fa-solid fa-circle-info"></i>

            </span>



            <div>

                <strong>

                    Use your Order ID as the payment reference

                </strong>



                <p>

                    Transfer the exact order total using the bank details

                    below. Your order will remain pending until the payment

                    has been verified.

                </p>

            </div>

        </div>



        <div class="customer-bank-details-grid">



            <?php if($bankName): ?>

            <div class="customer-bank-detail">

                <small>Bank name</small>

                <strong><?php echo e($bankName); ?></strong>

            </div>

            <?php endif; ?>



            <?php if($bankAccountName): ?>

            <div class="customer-bank-detail">

                <small>Account name / title</small>

                <strong><?php echo e($bankAccountName); ?></strong>

            </div>

            <?php endif; ?>



            <?php if($bankAccountNumber): ?>

            <div class="customer-bank-detail">

                <small>Account number</small>

                <strong><?php echo e($bankAccountNumber); ?></strong>

            </div>

            <?php endif; ?>



            <?php if($bankIban): ?>

            <div class="customer-bank-detail">

                <small>IBAN</small>

                <strong><?php echo e($bankIban); ?></strong>

            </div>

            <?php endif; ?>



            <?php if($bankSwiftCode): ?>

            <div class="customer-bank-detail">

                <small>SWIFT / BIC</small>

                <strong><?php echo e($bankSwiftCode); ?></strong>

            </div>

            <?php endif; ?>



            <?php if($bankBranchName): ?>

            <div class="customer-bank-detail">

                <small>Branch</small>

                <strong><?php echo e($bankBranchName); ?></strong>

            </div>

            <?php endif; ?>



            <div class="customer-bank-detail customer-bank-reference">

                <small>Payment reference</small>

                <strong><?php echo e($paymentReference); ?></strong>

            </div>



            <div class="customer-bank-detail">

                <small>Amount to transfer</small>

                <strong>

                    <?php echo e(strtoupper($order->currency ?: 'USD')); ?>


                    <?php echo e(number_format((float) $order->total, 2)); ?>


                </strong>

            </div>



        </div>



        <?php if($bankInstructions): ?>

        <div class="customer-bank-instructions">

            <span>

                <i class="fa-regular fa-note-sticky"></i>

            </span>



            <div>

                <strong>Payment instructions</strong>

                <p><?php echo nl2br(e($bankInstructions)); ?></p>

            </div>

        </div>

        <?php endif; ?>

    </div>

</section>

<?php endif; ?>



<div class="customer-order-grid">



    <section class="customer-panel">

        <header class="customer-panel-heading">

            <div>

                <span>Purchased products</span>

                <h3>Order items</h3>

            </div>



            <span class="customer-status <?php echo e($order->order_status); ?>">

                <?php echo e(str($order->order_status ?: 'pending')->headline()); ?>


            </span>

        </header>



        <div class="customer-table-wrap">

            <table class="customer-table">

                <thead>

                    <tr>

                        <th>Product</th>

                        <th>Quantity</th>

                        <th>Price</th>

                        <th>Total</th>

                    </tr>

                </thead>



                <tbody>

                    <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <?php

                            $itemName =

                                $item->product_name

                                ?? $item->name

                                ?? $item->product?->name

                                ?? 'Product';



                            $itemPrice =

                                (float) (

                                    $item->unit_price

                                    ?? $item->price

                                    ?? 0

                                );



                            $itemTotal =

                                (float) (

                                    $item->total

                                    ?? $item->subtotal

                                    ?? (

                                        $itemPrice

                                        * (int) $item->quantity

                                    )

                                );

                        ?>



                        <tr>

                            <td>

                                <strong><?php echo e($itemName); ?></strong>



                                <?php if($item->variant): ?>

                                    <small>

                                        <?php echo e($item->variant->name

                                            ?? $item->variant->sku

                                            ?? 'Variant'); ?>


                                    </small>

                                <?php endif; ?>

                            </td>



                            <td>

                                <?php echo e(number_format((int) $item->quantity)); ?>


                            </td>



                            <td>

                                <?php echo e(strtoupper($order->currency ?: 'USD')); ?>


                                <?php echo e(number_format($itemPrice, 2)); ?>


                            </td>



                            <td>

                                <strong>

                                    <?php echo e(strtoupper($order->currency ?: 'USD')); ?>


                                    <?php echo e(number_format($itemTotal, 2)); ?>


                                </strong>

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </tbody>

            </table>

        </div>



        <div class="customer-totals">
            <div>
                <small>Subtotal</small>
                <strong>
                    <?php echo e(strtoupper($order->currency ?: 'USD')); ?>

                    <?php echo e(number_format((float) $order->subtotal, 2)); ?>

                </strong>
            </div>

            <div>
                <small>
                    Discount
                    <?php if(filled($order->coupon_code)): ?>
                        <span class="customer-coupon-code">
                            <?php echo e($order->coupon_code); ?>

                        </span>
                    <?php endif; ?>
                </small>

                <strong class="<?php echo e((float) $order->discount > 0 ? 'customer-discount-value' : ''); ?>">
                    <?php echo e((float) $order->discount > 0 ? '-' : ''); ?>

                    <?php echo e(strtoupper($order->currency ?: 'USD')); ?>

                    <?php echo e(number_format((float) $order->discount, 2)); ?>

                </strong>
            </div>

            <div>
                <small>Shipping</small>
                <strong>
                    <?php echo e(strtoupper($order->currency ?: 'USD')); ?>

                    <?php echo e(number_format(
                        (float) (
                            $order->shipping_price
                            ?? $order->shipping
                            ?? 0
                        ),
                        2
                    )); ?>

                </strong>
            </div>

            <div>
                <small>Tax</small>
                <strong>
                    <?php echo e(strtoupper($order->currency ?: 'USD')); ?>

                    <?php echo e(number_format((float) ($order->tax ?? 0), 2)); ?>

                </strong>
            </div>

            <div class="grand-total">
                <small>Order total</small>
                <strong>
                    <?php echo e(strtoupper($order->currency ?: 'USD')); ?>

                    <?php echo e(number_format((float) $order->total, 2)); ?>

                </strong>
            </div>
        </div>

    </section>



    <aside class="customer-panel customer-delivery-card">

        <header class="customer-panel-heading">

            <div>

                <span>Shipment</span>

                <h3>Delivery details</h3>

            </div>

        </header>



        <div class="customer-address">

            <span class="customer-address-icon">

                <i class="fa-solid fa-location-dot"></i>

            </span>



            <p>

                <strong>

                    <?php echo e($order->shipping_name ?: $order->billing_name); ?>


                </strong>

                <br>



                <?php echo e($order->shipping_address ?: $order->billing_address); ?>


                <br>



                <?php echo e($order->shipping_city ?: $order->billing_city); ?>


                <?php echo e($order->shipping_state ?: $order->billing_state); ?>


                <?php echo e($order->shipping_zip ?: $order->billing_zip); ?>


                <br>



                <?php echo e($order->shipping_country ?: $order->billing_country); ?>


            </p>

        </div>



        <dl class="customer-detail-list">

            <div>

                <dt>Payment</dt>

                <dd>

                    <?php echo e(str(

                        $order->payment_status ?: 'pending'

                    )->headline()); ?>


                </dd>

            </div>



            <div>

                <dt>Method</dt>

                <dd>

                    <?php echo e(str(

                        $order->payment_method ?: 'Not available'

                    )->headline()); ?>


                </dd>

            </div>



            <?php if($isBankTransfer): ?>

            <div>

                <dt>Payment reference</dt>

                <dd><?php echo e($paymentReference); ?></dd>

            </div>

            <?php endif; ?>



            <div>

                <dt>Tracking</dt>

                <dd>

                    <?php echo e($order->tracking_number ?: 'Pending'); ?>


                </dd>

            </div>



            <div>

                <dt>Estimated delivery</dt>

                <dd>

                    <?php echo e($order->estimated_delivery ?: 'To be confirmed'); ?>


                </dd>

            </div>

        </dl>



        <?php if($order->notes->isNotEmpty()): ?>

        <div class="customer-order-updates">

            <h4>Order updates</h4>



            <?php $__currentLoopData = $order->notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <p><?php echo e($note->note); ?></p>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>

        <?php endif; ?>

    </aside>



</div>





<?php if(filled($order->order_notes)): ?>

<section class="customer-panel customer-checkout-note-panel">

    <header class="customer-panel-heading">

        <div>

            <span>Your message</span>

            <h3>Checkout order note</h3>

        </div>



        <span class="customer-checkout-note-icon" aria-hidden="true">

            <i class="fa-regular fa-note-sticky"></i>

        </span>

    </header>



    <div class="customer-checkout-note">

        <?php echo nl2br(e($order->order_notes)); ?>


    </div>



    <p class="customer-checkout-note-help">

        This is the note you submitted when placing this order.

    </p>

</section>

<?php endif; ?>



<style>

.customer-checkout-note-panel {

    margin-top: 20px;

    overflow: hidden;

}



.customer-checkout-note-icon {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 38px;

    height: 38px;

    flex: 0 0 38px;

    border-radius: 10px;

    background: #eef0ff;

    color: #635bff;

    font-size: 14px;

}



.customer-checkout-note {

    margin: 0 18px;

    padding: 16px 18px;

    border: 1px solid #e5eaf1;

    border-radius: 12px;

    background: #f8fafc;

    color: #172033;

    font-size: 14px;

    line-height: 1.7;

    overflow-wrap: anywhere;

}



.customer-checkout-note-help {

    margin: 10px 18px 18px;

    color: #7b8495;

    font-size: 12px;

    line-height: 1.5;

}



@media (max-width: 650px) {

    .customer-checkout-note-panel {

        margin-top: 16px;

    }



    .customer-checkout-note {

        margin: 0 14px;

        padding: 14px;

    }



    .customer-checkout-note-help {

        margin: 9px 14px 14px;

    }

}



.customer-bank-transfer-panel {

    margin-bottom: 20px;

    overflow: hidden;

}



.customer-bank-icon {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 38px;

    height: 38px;

    flex: 0 0 38px;

    border-radius: 10px;

    background: #eef0ff;

    color: #635bff;

    font-size: 14px;

}



.customer-bank-transfer-body {

    padding: 18px;

}



.customer-bank-notice {

    display: flex;

    align-items: flex-start;

    gap: 12px;

    margin-bottom: 16px;

    padding: 13px 14px;

    border: 1px solid #e1e4ff;

    border-radius: 10px;

    background: #f7f7ff;

}



.customer-bank-notice > span {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 30px;

    height: 30px;

    flex: 0 0 30px;

    border-radius: 8px;

    background: #eef0ff;

    color: #635bff;

}



.customer-bank-notice strong,

.customer-bank-instructions strong {

    display: block;

    color: #172033;

    font-size: 12px;

}



.customer-bank-notice p,

.customer-bank-instructions p {

    margin: 4px 0 0;

    color: #727b8c;

    font-size: 11px;

    line-height: 1.6;

}



.customer-bank-details-grid {

    display: grid;

    grid-template-columns: repeat(2, minmax(0, 1fr));

    gap: 10px;

}



.customer-bank-detail {

    min-width: 0;

    padding: 12px 13px;

    border: 1px solid #e8ebf0;

    border-radius: 9px;

    background: #fff;

}



.customer-bank-detail small {

    display: block;

    margin-bottom: 5px;

    color: #8992a2;

    font-size: 9px;

    font-weight: 700;

    letter-spacing: .04em;

    text-transform: uppercase;

}



.customer-bank-detail strong {

    display: block;

    overflow-wrap: anywhere;

    color: #172033;

    font-size: 11px;

}



.customer-bank-reference {

    border-color: #dcdfff;

    background: #fafaff;

}



.customer-bank-reference strong {

    color: #5149dc;

}



.customer-bank-instructions {

    display: flex;

    align-items: flex-start;

    gap: 11px;

    margin-top: 14px;

    padding: 13px 14px;

    border: 1px solid #e8ebf0;

    border-radius: 9px;

    background: #fafbfc;

}



.customer-bank-instructions > span {

    color: #635bff;

    font-size: 14px;

}



@media (max-width: 650px) {

    .customer-bank-details-grid {

        grid-template-columns: 1fr;

    }



    .customer-bank-transfer-body {

        padding: 14px;

    }

}


.customer-coupon-code {
    display: inline-flex;
    align-items: center;
    margin-left: 5px;
    padding: 2px 6px;
    border: 1px solid #d7dbff;
    border-radius: 999px;
    background: #f7f7ff;
    color: #5149dc;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .03em;
    line-height: 1.3;
    vertical-align: middle;
}

.customer-discount-value {
    color: #0f766e;
}

</style>





<?php

    $customerStatus = strtolower((string) ($order->order_status ?? 'pending'));

    $customerTrackingSteps = [

        'pending' => 1,

        'confirmed' => 2,

        'processing' => 2,

        'packed' => 3,

        'shipped' => 4,

        'out_for_delivery' => 5,

        'completed' => 6,

        'delivered' => 6,

    ];

    $customerCurrentStep = $customerTrackingSteps[$customerStatus] ?? 1;

    $customerOrderStopped = in_array($customerStatus, ['cancelled', 'refunded'], true);

?>



<section class="customer-panel customer-order-tracking-panel">

    <div class="customer-order-tracking-heading">

        <div>

            <span>Order tracking</span>

            <h2>Delivery progress</h2>

            <p>Follow the latest status and delivery updates for this order.</p>

        </div>

        <div class="customer-order-tracking-reference">

            <span>Tracking number</span>

            <strong><?php echo e($order->tracking_number ?: 'Tracking pending'); ?></strong>

        </div>

    </div>



    <?php if($customerOrderStopped): ?>

        <div class="customer-order-tracking-stopped">

            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>

            <span>This order is currently <?php echo e(str($customerStatus)->headline()); ?>.</span>

        </div>

    <?php else: ?>

        <div class="customer-order-progress-wrap">

            <div class="customer-order-progress" aria-label="Order delivery progress">

                <?php $__currentLoopData = [

                    1 => ['Pending', 'fa-receipt'],

                    2 => ['Processing', 'fa-box-open'],

                    3 => ['Packed', 'fa-box'],

                    4 => ['Shipped', 'fa-truck-fast'],

                    5 => ['Out for delivery', 'fa-route'],

                    6 => ['Delivered', 'fa-circle-check'],

                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step => [$label, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <div class="customer-order-progress-step <?php echo e($customerCurrentStep >= $step ? 'is-complete' : ''); ?> <?php echo e($customerCurrentStep === $step ? 'is-current' : ''); ?>">

                        <div class="customer-order-progress-icon">

                            <i class="fa-solid <?php echo e($icon); ?>" aria-hidden="true"></i>

                        </div>

                        <span><?php echo e($label); ?></span>

                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>

        </div>

    <?php endif; ?>



    <div class="customer-order-tracking-meta">

        <div><span>Current status</span><strong><?php echo e(str($customerStatus)->headline()); ?></strong></div>

        <div><span>Shipping method</span><strong><?php echo e($order->shipping_method ?: 'To be confirmed'); ?></strong></div>

        <div><span>Estimated delivery</span><strong><?php echo e($order->estimated_delivery ?: 'To be confirmed'); ?></strong></div>

    </div>



    <div class="customer-order-history">

        <div class="customer-order-history-heading">

            <span>Timeline</span>

            <h3>Tracking history</h3>

        </div>



        <?php if($order->activities->isNotEmpty()): ?>

            <div class="customer-order-history-list">

                <?php $__currentLoopData = $order->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <article class="customer-order-history-item">

                        <div class="customer-order-history-dot" aria-hidden="true"></div>

                        <div>

                            <strong><?php echo e($activity->title); ?></strong>

                            <?php if(filled($activity->description)): ?>

                                <p><?php echo e($activity->description); ?></p>

                            <?php endif; ?>

                            <time datetime="<?php echo e($activity->created_at?->toIso8601String()); ?>">

                                <?php echo e($activity->created_at?->format('d M Y, h:i A')); ?>


                            </time>

                        </div>

                    </article>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>

        <?php else: ?>

            <div class="customer-order-history-empty">

                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>

                <p>No additional tracking updates have been recorded yet.</p>

            </div>

        <?php endif; ?>

    </div>

</section>



<style>

.customer-order-tracking-panel{margin-top:14px;padding:18px 20px!important}

.customer-order-tracking-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px}

.customer-order-tracking-heading>div:first-child>span,.customer-order-history-heading>span{display:block;margin-bottom:3px;color:#0f766e;font-size:9px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}

.customer-order-tracking-heading h2,.customer-order-history-heading h3{margin:0;color:#172033}

.customer-order-tracking-heading h2{font-size:17px}

.customer-order-tracking-heading p{margin:4px 0 0;color:#667085;font-size:11px;line-height:1.45}

.customer-order-tracking-reference{flex:0 0 auto;max-width:220px;text-align:right}

.customer-order-tracking-reference span,.customer-order-tracking-meta span{display:block;margin-bottom:2px;color:#7b8798;font-size:9px}

.customer-order-tracking-reference strong,.customer-order-tracking-meta strong{display:block;color:#172033;font-size:11px;overflow-wrap:anywhere}



.customer-order-progress-wrap{width:100%;padding:2px 4px 0;overflow:visible}

.customer-order-progress{display:flex;width:100%;margin:0 0 18px}

.customer-order-progress-step{position:relative;display:flex;flex:1;min-width:0;flex-direction:column;align-items:center;text-align:center}

.customer-order-progress-step::after{content:"";position:absolute;z-index:0;top:13px;left:calc(50% + 13px);right:calc(-50% + 13px);height:2px;background:#e2e8f0}

.customer-order-progress-step:last-child::after{display:none}

.customer-order-progress-step.is-complete::after{background:#0f766e}

.customer-order-progress-icon{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;width:28px;height:28px;margin:0 0 6px;color:#7b8798;background:#f1f5f9;border:2px solid #e2e8f0;border-radius:50%;font-size:9px}

.customer-order-progress-step.is-complete .customer-order-progress-icon{color:#fff;background:#0f766e;border-color:#0f766e}

.customer-order-progress-step.is-current .customer-order-progress-icon{box-shadow:0 0 0 4px rgba(15,118,110,.10)}

.customer-order-progress-step>span{display:block;max-width:100%;padding:0 3px;color:#7b8798;font-size:9px;font-weight:700;line-height:1.25}

.customer-order-progress-step.is-complete>span{color:#172033}



.customer-order-tracking-stopped{display:flex;align-items:center;justify-content:center;gap:7px;padding:10px 12px;margin-bottom:16px;color:#9a3412;background:#fff7ed;border:1px solid #fed7aa;border-radius:9px;text-align:center;font-size:11px;font-weight:700}

.customer-order-tracking-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-top:0}

.customer-order-tracking-meta>div{min-width:0;padding:9px 11px;background:#f8fafc;border:1px solid #e5eaf1;border-radius:9px}



.customer-order-history{margin-top:16px;padding-top:15px;border-top:1px solid #e5eaf1}

.customer-order-history-heading{margin-bottom:10px}

.customer-order-history-heading h3{font-size:13px}

.customer-order-history-list{display:grid}

.customer-order-history-item{position:relative;display:grid;grid-template-columns:12px minmax(0,1fr);gap:8px;padding-bottom:12px}

.customer-order-history-item:not(:last-child)::before{content:"";position:absolute;top:10px;bottom:0;left:4px;width:1px;background:#dfe5ec}

.customer-order-history-dot{position:relative;z-index:1;width:9px;height:9px;margin-top:3px;background:#0f766e;border:2px solid #dff5f1;border-radius:50%;box-sizing:border-box}

.customer-order-history-item strong{display:block;margin-bottom:2px;color:#172033;font-size:11px}

.customer-order-history-item p{margin:0 0 3px;color:#667085;font-size:10px;line-height:1.45}

.customer-order-history-item time{color:#98a2b3;font-size:9px}

.customer-order-history-empty{display:flex;align-items:center;gap:7px;color:#667085;font-size:10px}

.customer-order-history-empty p{margin:0}



@media(max-width:900px){

    .customer-order-tracking-panel{padding:16px!important}

    .customer-order-tracking-heading{align-items:flex-start;flex-direction:column;gap:8px}

    .customer-order-tracking-reference{max-width:none;text-align:left}

    .customer-order-progress-wrap{overflow-x:auto;padding:2px 0 8px;-webkit-overflow-scrolling:touch;scrollbar-width:thin}

    .customer-order-progress{width:600px;margin-bottom:10px}

    .customer-order-tracking-meta{grid-template-columns:1fr}

}



@media(max-width:520px){

    .customer-order-tracking-panel{margin-top:12px;padding:14px!important}

    .customer-order-tracking-heading h2{font-size:16px}

    .customer-order-tracking-heading p{font-size:10px}

    .customer-order-progress{width:540px}

    .customer-order-progress-step>span{font-size:8px}

    .customer-order-tracking-meta>div{padding:9px 10px}

}

</style>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('customer.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/customer/orders/show.blade.php ENDPATH**/ ?>