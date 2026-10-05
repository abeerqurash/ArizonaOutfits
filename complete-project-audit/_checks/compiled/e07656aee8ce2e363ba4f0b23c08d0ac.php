<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Order confirmation
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background: #f5f5f5;
        font-family: Arial, Helvetica, sans-serif;
        color: #222222;
    ">
    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0">
        <tr>
            <td
                align="center"
                style="padding: 30px 15px;">
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="
                    max-width: 680px;
                    background: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                ">
                    <tr>
                        <td
                            style="
                            padding: 30px;
                            background: #111111;
                            color: #ffffff;
                        ">
                            <h1
                                style="
                                margin: 0;
                                font-size: 26px;
                            ">
                                Thank you for your order
                            </h1>

                            <p
                                style="
                                margin: 10px 0 0;
                                line-height: 1.6;
                            ">
                                Your order has been received successfully.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px;">
                            <p
                                style="
                                margin-top: 0;
                                line-height: 1.7;
                            ">
                                Hello
                                <?php echo e($order->customer_name
                                ?? $order->billing_name
                                ?? $order->name
                                ?? 'Customer'); ?>,
                            </p>

                            <p style="line-height: 1.7;">
                                Your order number is
                                <strong>
                                    <?php echo e($order->order_number); ?>

                                </strong>.
                            </p>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="8"
                                border="0"
                                style="
                                margin: 25px 0;
                                border-collapse: collapse;
                            ">
                                <thead>
                                    <tr
                                        style="
                                        background: #f1f1f1;
                                        text-align: left;
                                    ">
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th align="right">Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td
                                            style="
                                                border-bottom:
                                                    1px solid #eeeeee;
                                            ">
                                            <strong>
                                                <?php echo e($item->productName()); ?>

                                            </strong>

                                            <?php if($item->variantLabel()): ?>
                                            <br>

                                            <small>
                                                <?php echo e($item->variantLabel()); ?>

                                            </small>
                                            <?php endif; ?>
                                        </td>

                                        <td
                                            style="
                                                border-bottom:
                                                    1px solid #eeeeee;
                                            ">
                                            <?php echo e($item->quantity); ?>

                                        </td>

                                        <td
                                            align="right"
                                            style="
                                                border-bottom:
                                                    1px solid #eeeeee;
                                            ">
                                            <?php echo e(strtoupper(
                                                config(
                                                    'payments.currency',
                                                    'USD'
                                                )
                                            )); ?>


                                            <?php echo e(number_format(
                                                (float) $item->subtotal(),
                                                2
                                            )); ?>

                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="5"
                                border="0">
                                <?php if(
                                isset($order->subtotal)
                                && $order->subtotal !== null
                                ): ?>
                                <tr>
                                    <td>Subtotal</td>

                                    <td align="right">
                                        <?php echo e(strtoupper(
                                            config(
                                                'payments.currency',
                                                'USD'
                                            )
                                        )); ?>


                                        <?php echo e(number_format(
                                            (float) $order->subtotal,
                                            2
                                        )); ?>

                                    </td>
                                </tr>
                                <?php endif; ?>

                                <?php if(
                                isset($order->shipping_total)
                                && $order->shipping_total !== null
                                ): ?>
                                <tr>
                                    <td>Shipping</td>

                                    <td align="right">
                                        <?php echo e(strtoupper(
                                            config(
                                                'payments.currency',
                                                'USD'
                                            )
                                        )); ?>


                                        <?php echo e(number_format(
                                            (float) $order->shipping_total,
                                            2
                                        )); ?>

                                    </td>
                                </tr>
                                <?php endif; ?>

                                <?php if(
                                isset($order->discount_total)
                                && (float) $order->discount_total > 0
                                ): ?>
                                <tr>
                                    <td>Discount</td>

                                    <td align="right">
                                        -
                                        <?php echo e(strtoupper(
                                            config(
                                                'payments.currency',
                                                'USD'
                                            )
                                        )); ?>


                                        <?php echo e(number_format(
                                            (float) $order->discount_total,
                                            2
                                        )); ?>

                                    </td>
                                </tr>
                                <?php endif; ?>

                                <tr>
                                    <td
                                        style="
                                        padding-top: 12px;
                                        font-size: 18px;
                                        font-weight: bold;
                                    ">
                                        Order total
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                        padding-top: 12px;
                                        font-size: 18px;
                                        font-weight: bold;
                                    ">
                                        <?php echo e(strtoupper(
                                        $order->currency
                                            ?? config(
                                                'payments.currency',
                                                'USD'
                                            )
                                    )); ?>


                                        <?php echo e(number_format(
                                        (float) (
                                            $order->total
                                            ?? $order->grand_total
                                            ?? 0
                                        ),
                                        2
                                    )); ?>

                                    </td>
                                </tr>
                            </table>

                            <?php if(
                            ($order->payment_provider ?? null)
                            === 'bank_transfer'
                            || ($order->payment_method ?? null)
                            === 'bank_transfer'
                            ): ?>
                            <div
                                style="
                                    margin-top: 30px;
                                    padding: 20px;
                                    background: #f8f8f8;
                                    border-radius: 8px;
                                ">
                                <h2
                                    style="
                                        margin-top: 0;
                                        font-size: 20px;
                                    ">
                                    Bank transfer instructions
                                </h2>

                                <p style="line-height: 1.7;">
                                    Please use your order number
                                    <strong>
                                        <?php echo e($order->order_number); ?>

                                    </strong>
                                    as the payment reference.
                                </p>

                                <p style="line-height: 1.8;">
                                    <strong>Bank:</strong>
                                    <?php echo e(config(
                                        'payments.bank_transfer.bank_name'
                                    ) ?: 'Not provided'); ?>

                                    <br>

                                    <strong>Account name:</strong>
                                    <?php echo e(config(
                                        'payments.bank_transfer.account_name'
                                    ) ?: 'Not provided'); ?>

                                    <br>

                                    <strong>Account number:</strong>
                                    <?php echo e(config(
                                        'payments.bank_transfer.account_number'
                                    ) ?: 'Not provided'); ?>

                                    <br>

                                    <strong>IBAN:</strong>
                                    <?php echo e(config(
                                        'payments.bank_transfer.iban'
                                    ) ?: 'Not provided'); ?>

                                    <br>

                                    <strong>SWIFT code:</strong>
                                    <?php echo e(config(
                                        'payments.bank_transfer.swift_code'
                                    ) ?: 'Not provided'); ?>

                                </p>
                            </div>
                            <?php endif; ?>

                            <p
                                style="
                                margin-bottom: 0;
                                margin-top: 30px;
                                line-height: 1.7;
                            ">
                                We will contact you when the order status
                                changes.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?php if(filled(app(\App\Services\StoreSettingsService::class)->settings()->order_email_message)): ?><p style="text-align:center;padding:16px;font-family:Arial"><?php echo e(app(\App\Services\StoreSettingsService::class)->settings()->order_email_message); ?></p><?php endif; ?>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\emails\orders\customer-confirmation.blade.php ENDPATH**/ ?>