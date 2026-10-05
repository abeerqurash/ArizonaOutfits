<?php
    $orderNumber = $order->order_number
        ?: 'ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

    $trackingNumber = $order->tracking_number ?: $orderNumber;

    $customerName = $order->shipping_name
        ?: $order->billing_name
        ?: $order->user?->name
        ?: 'Guest Customer';

    $customerPhone = $order->shipping_phone ?: $order->billing_phone ?: null;
    $customerEmail = $order->shipping_email ?: $order->billing_email ?: $order->user?->email ?: null;

    $address1 = $order->shipping_address
        ?? $order->shipping_address_line_1
        ?? $order->shipping_address1
        ?? $order->billing_address
        ?? $order->billing_address_line_1
        ?? $order->billing_address1
        ?? null;

    $address2 = $order->shipping_address_line_2
        ?? $order->shipping_address2
        ?? $order->billing_address_line_2
        ?? $order->billing_address2
        ?? null;

    $city = $order->shipping_city ?: $order->billing_city ?: null;
    $state = $order->shipping_state ?: $order->billing_state ?: null;
    $postcode = $order->shipping_postcode
        ?? $order->shipping_zip
        ?? $order->billing_postcode
        ?? $order->billing_zip
        ?? null;
    $country = $order->shipping_country ?: $order->billing_country ?: null;

    $courierProvider = $order->courier_provider
        ?? $order->shipping_carrier
        ?? $order->carrier
        ?? null;

    $courierTracking = $order->courier ?: null;

    $shippingMethod = $order->shipping_method_name
        ?? $order->shipping_method
        ?? 'Standard Shipping';

    $orderStatus = ucwords(str_replace(['_', '-'], ' ', $order->order_status ?? $order->status ?? 'pending'));

    $totalQuantity = (int) $order->items->sum('quantity');
    $packageWeight = $order->package_weight ?? $order->weight ?? null;
    $weightUnit = $order->weight_unit ?? 'kg';

    $senderName = 'Arizona Outfits';
    $senderLine1 = 'Main fulfilment warehouse';
    $senderEmail = 'support@arizonaoutfits.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label <?php echo e($orderNumber); ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }
        .toolbar {
            width: min(100% - 32px, 760px);
            margin: 24px auto 18px;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            background: #fff;
            color: #111827;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }
        .btn-primary { background: #111827; border-color: #111827; color: #fff; }
        .sheet-wrap { padding: 0 16px 32px; overflow-x: auto; }
        .label {
            width: 4in;
            min-height: 6in;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #111;
            box-shadow: 0 14px 40px rgba(15,23,42,.12);
            overflow: hidden;
        }
        .row { display: flex; width: 100%; }
        .header {
            min-height: .78in;
            padding: 14px 15px;
            border-bottom: 2px solid #111;
            align-items: center;
            justify-content: space-between;
        }
        .brand { font-size: 20px; line-height: .92; font-weight: 900; letter-spacing: .5px; }
        .brand small { display: block; margin-top: 7px; font-size: 7px; line-height: 1.2; font-weight: 600; letter-spacing: 0; }
        .service {
            width: 96px;
            padding: 9px 6px;
            border: 2px solid #111;
            text-align: center;
            font-size: 7px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .service strong { display: block; margin-top: 4px; font-size: 11px; }
        .section { padding: 12px 14px; border-bottom: 2px solid #111; }
        .eyebrow { margin-bottom: 7px; font-size: 7px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; }
        .from { min-height: .72in; }
        .from strong { font-size: 11px; }
        .muted { margin-top: 4px; font-size: 8px; line-height: 1.45; }
        .ship { min-height: 1.45in; }
        .recipient { font-size: 18px; line-height: 1.1; font-weight: 900; text-transform: uppercase; }
        .address { margin-top: 9px; font-size: 13px; line-height: 1.38; font-weight: 800; text-transform: uppercase; }
        .contact { margin-top: 9px; font-size: 8px; line-height: 1.4; }
        .tracking { min-height: 1.12in; text-align: center; }
        .tracking-number { margin: 4px 0 8px; font-size: 14px; font-weight: 900; letter-spacing: 1px; }
        .barcode { display: block; width: 100%; height: 52px; object-fit: fill; }
        .barcode-text { margin-top: 5px; font: 700 8px monospace; letter-spacing: 1.2px; }
        .details { min-height: 1.15in; padding: 0; display: table; width: 100%; table-layout: fixed; border-bottom: 2px solid #111; }
        .detail-list { display: table-cell; width: 68%; vertical-align: middle; padding: 10px 12px; border-right: 2px solid #111; }
        .detail { display: table; width: 100%; margin: 0 0 5px; table-layout: fixed; font-size: 8px; }
        .detail:last-child { margin-bottom: 0; }
        .detail b, .detail span { display: table-cell; vertical-align: top; }
        .detail b { width: 88px; text-transform: uppercase; }
        .detail span { font-weight: 700; overflow-wrap: anywhere; }
        .qr-cell { display: table-cell; width: 32%; vertical-align: middle; text-align: center; padding: 8px; }
        .qr { display: block; width: 84px; height: 84px; margin: 0 auto; }
        .scan { margin-top: 4px; font-size: 7px; font-weight: 900; text-transform: uppercase; }
        .footer { min-height: .48in; padding: 10px 13px; display: flex; align-items: center; justify-content: space-between; font-size: 9px; font-weight: 900; }
        .footer span:last-child { text-align: right; }
        .hint { margin: 14px auto 0; width: min(100% - 32px, 520px); text-align: center; color: #6b7280; font-size: 11px; }
        @media print {
            @page { size: 4in 6in; margin: 0; }
            body { background: #fff; }
            .toolbar, .hint { display: none !important; }
            .sheet-wrap { padding: 0; overflow: visible; }
            .label { margin: 0; box-shadow: none; }
        }
        @media (max-width: 460px) {
            .sheet-wrap { padding-left: 8px; padding-right: 8px; }
            .label { transform-origin: top center; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a class="btn" href="<?php echo e(route('admin.orders.show', $order)); ?>">← Back to order</a>
        <button class="btn" type="button" onclick="window.print()">Print 4 × 6 label</button>
        <a class="btn btn-primary" href="<?php echo e(route('admin.orders.shipping-label.download', $order)); ?>">Download PDF</a>
    </div>

    <div class="sheet-wrap">
        <article class="label">
            <header class="row header">
                <div class="brand">ARIZONA<br>OUTFITS<small>Premium clothing & lifestyle products</small></div>
                <div class="service">Service<strong><?php echo e($shippingMethod); ?></strong></div>
            </header>

            <section class="section from">
                <div class="eyebrow">From</div>
                <strong><?php echo e($senderName); ?></strong>
                <div class="muted"><?php echo e($senderLine1); ?><br><?php echo e($senderEmail); ?></div>
            </section>

            <section class="section ship">
                <div class="eyebrow">Ship to</div>
                <div class="recipient"><?php echo e($customerName); ?></div>
                <div class="address">
                    <?php if($address1): ?><?php echo e($address1); ?><br><?php endif; ?>
                    <?php if($address2): ?><?php echo e($address2); ?><br><?php endif; ?>
                    <?php if($city || $state): ?><?php echo e($city); ?><?php echo e($city && $state ? ', ' : ''); ?><?php echo e($state); ?><br><?php endif; ?>
                    <?php if($postcode || $country): ?><?php echo e($postcode); ?><?php echo e($postcode && $country ? ' · ' : ''); ?><?php echo e($country); ?><?php endif; ?>
                </div>
                <?php if($customerPhone || $customerEmail): ?>
                <div class="contact">
                    <?php if($customerPhone): ?>Phone: <?php echo e($customerPhone); ?><?php endif; ?>
                    <?php if($customerPhone && $customerEmail): ?> &nbsp;·&nbsp; <?php endif; ?>
                    <?php if($customerEmail): ?>Email: <?php echo e($customerEmail); ?><?php endif; ?>
                </div>
                <?php endif; ?>
            </section>

            <section class="section tracking">
                <div class="eyebrow">Arizona tracking</div>
                <div class="tracking-number"><?php echo e($trackingNumber); ?></div>
                <img class="barcode" src="data:image/png;base64,<?php echo e($barcodeBase64); ?>" alt="Arizona tracking barcode">
                <div class="barcode-text"><?php echo e($trackingNumber); ?></div>
            </section>

            <section class="details">
                <div class="detail-list">
                    <div class="detail"><b>Courier provider</b><span><?php echo e($courierProvider ?: 'Not assigned'); ?></span></div>
                    <div class="detail"><b>Courier tracking</b><span><?php echo e($courierTracking ?: 'Not assigned'); ?></span></div>
                    <div class="detail"><b>Order</b><span><?php echo e($orderNumber); ?></span></div>
                    <div class="detail"><b>Status</b><span><?php echo e($orderStatus); ?></span></div>
                    <div class="detail"><b>Weight</b><span><?php echo e($packageWeight !== null ? number_format((float)$packageWeight, 2).' '.$weightUnit : 'Not specified'); ?></span></div>
                    <div class="detail"><b>Contents</b><span><?php echo e($totalQuantity); ?> unit(s)</span></div>
                </div>
                <div class="qr-cell">
                    <img class="qr" src="data:image/png;base64,<?php echo e($qrBase64); ?>" alt="Order QR code">
                    <div class="scan">Scan order</div>
                </div>
            </section>

            <footer class="footer">
                <span><?php echo e($orderNumber); ?></span>
                <span>Package 1 of 1</span>
            </footer>
        </article>
    </div>
    <div class="hint">4 × 6 thermal-label preview. Barcode and QR use the same generated data as the PDF.</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\orders\shipping-label.blade.php ENDPATH**/ ?>