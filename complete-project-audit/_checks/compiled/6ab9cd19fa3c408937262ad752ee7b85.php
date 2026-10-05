<?php

$orderNumber = $order->order_number

?: 'ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);



$invoiceNumber = 'INV-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);



$currencyCode = strtoupper($order->currency ?: 'PKR');



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



$currencySymbol = $currencySymbols[$currencyCode]

?? $currencyCode . ' ';



$money = function ($amount) use ($currencySymbol) {

return $currencySymbol . number_format((float) $amount, 2);

};



$customerName = $order->billing_name

?: $order->shipping_name

?: $order->user?->name

?: 'Guest customer';



$customerEmail = $order->billing_email

?: $order->shipping_email

?: $order->user?->email

?: null;



$customerPhone = $order->billing_phone

?: $order->shipping_phone

?: null;



$billingAddress = array_filter([

$order->billing_address

?? $order->billing_address_line_1

?? $order->billing_address1

?? null,



$order->billing_address_line_2

?? $order->billing_address2

?? null,



trim(

($order->billing_city ?? '')

. (!empty($order->billing_state)

? ', ' . $order->billing_state

: '')

),



trim(

($order->billing_postcode

?? $order->billing_zip

?? '')

. (!empty($order->billing_country)

? ', ' . $order->billing_country

: '')

),

]);



$shippingAddress = array_filter([

$order->shipping_address

?? $order->shipping_address_line_1

?? $order->shipping_address1

?? null,



$order->shipping_address_line_2

?? $order->shipping_address2

?? null,



trim(

($order->shipping_city ?? '')

. (!empty($order->shipping_state)

? ', ' . $order->shipping_state

: '')

),



trim(

($order->shipping_postcode

?? $order->shipping_zip

?? '')

. (!empty($order->shipping_country)

? ', ' . $order->shipping_country

: '')

),

]);



$subtotal = (float) ($order->subtotal ?? 0);

$discount = (float) ($order->discount ?? 0);

$shipping = (float) ($order->shipping ?? 0);

$tax = (float) ($order->tax ?? 0);

$total = (float) ($order->total ?? 0);



$orderStatus = strtolower($order->order_status ?: 'pending');

$paymentStatus = strtolower($order->payment_status ?: 'pending');



$paymentMethod = $order->payment_method

? ucwords(str_replace(['_', '-'], ' ', $order->payment_method))

: 'Not specified';



$paymentReference = $order->payment_reference

?? $order->transaction_id

?? null;



$trackingNumber = $order->tracking_number ?: null;



$statusClass = match ($orderStatus) {

'completed',

'delivered' => 'status-success',



'processing',

'shipped' => 'status-warning',



'cancelled',

'refunded' => 'status-danger',



default => 'status-neutral',

};



$paymentClass = match ($paymentStatus) {

'paid',

'completed',

'succeeded' => 'status-success',



'failed',

'declined',

'cancelled',

'refunded' => 'status-danger',



default => 'status-warning',

};

$isCustomerInvoice = request()->routeIs('customer.orders.invoice');



$invoiceBackUrl = $isCustomerInvoice

    ? route('customer.orders.show', $order->id)

    : route('admin.orders.show', $order);



$invoiceDownloadUrl = $isCustomerInvoice

    ? route('customer.orders.invoice.download', $order->id)

    : route('admin.orders.invoice.download', $order);



?>



<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="UTF-8">



    <meta

        name="viewport"

        content="width=device-width, initial-scale=1.0">



    <title>

        Invoice <?php echo e($invoiceNumber); ?>


    </title>



    <style>

        :root {

            --invoice-primary: #111827;

            --invoice-accent: #4f46e5;

            --invoice-text: #1f2937;

            --invoice-muted: #6b7280;

            --invoice-border: #e5e7eb;

            --invoice-background: #f3f4f6;

            --invoice-card: #ffffff;

            --invoice-success: #15803d;

            --invoice-success-bg: #dcfce7;

            --invoice-warning: #b45309;

            --invoice-warning-bg: #ffedd5;

            --invoice-danger: #b91c1c;

            --invoice-danger-bg: #fee2e2;

        }



        * {

            box-sizing: border-box;

        }



        html {

            background: var(--invoice-background);

        }



        body {

            margin: 0;

            color: var(--invoice-text);

            background: var(--invoice-background);

            font-family:

                Arial,

                Helvetica,

                sans-serif;

            font-size: 14px;

            line-height: 1.5;

        }



        button,

        a {

            font: inherit;

        }



        .invoice-toolbar {

            position: sticky;

            top: 0;

            z-index: 100;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 14px 24px;

            background: rgba(255, 255, 255, 0.96);

            border-bottom: 1px solid var(--invoice-border);

            backdrop-filter: blur(10px);

        }



        .invoice-toolbar-left {

            display: flex;

            align-items: center;

            gap: 12px;

        }



        .invoice-toolbar-title {

            display: grid;

            gap: 2px;

        }



        .invoice-toolbar-title strong {

            color: var(--invoice-primary);

            font-size: 15px;

        }



        .invoice-toolbar-title span {

            color: var(--invoice-muted);

            font-size: 11px;

        }



        .invoice-toolbar-actions {

            display: flex;

            align-items: center;

            gap: 9px;

        }



        .toolbar-button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 40px;

            padding: 9px 15px;

            color: var(--invoice-primary);

            background: #ffffff;

            border: 1px solid var(--invoice-border);

            border-radius: 9px;

            font-size: 12px;

            font-weight: 700;

            text-decoration: none;

            cursor: pointer;

        }



        .toolbar-button:hover {

            background: #f9fafb;

        }



        .toolbar-button-primary {

            color: #ffffff;

            background: var(--invoice-accent);

            border-color: var(--invoice-accent);

        }



        .toolbar-button-primary:hover {

            background: #4338ca;

        }



        .invoice-page-wrapper {

            padding: 35px 20px 70px;

        }



        .invoice-page {

            width: 100%;

            max-width: 950px;

            min-height: 1120px;

            margin: 0 auto;

            padding: 55px;

            background: var(--invoice-card);

            box-shadow:

                0 20px 50px rgba(15, 23, 42, 0.1);

        }



        .invoice-header {

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 40px;

            padding-bottom: 32px;

            border-bottom: 2px solid var(--invoice-primary);

        }



        .company-brand {

            max-width: 430px;

        }



        .company-logo {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 190px;

            min-height: 58px;

            margin-bottom: 16px;

            color: #ffffff;

            background: var(--invoice-primary);

            border-radius: 10px;

            font-size: 22px;

            font-weight: 900;

            letter-spacing: 0.5px;

        }



        .company-brand h1 {

            margin: 0 0 6px;

            color: var(--invoice-primary);

            font-size: 25px;

            line-height: 1.2;

        }



        .company-brand p {

            margin: 0;

            color: var(--invoice-muted);

            font-size: 12px;

            line-height: 1.65;

        }



        .invoice-heading {

            min-width: 260px;

            text-align: right;

        }



        .invoice-heading h2 {

            margin: 0;

            color: var(--invoice-primary);

            font-size: 42px;

            font-weight: 900;

            letter-spacing: 3px;

            line-height: 1;

            text-transform: uppercase;

        }



        .invoice-number {

            display: block;

            margin-top: 12px;

            color: var(--invoice-accent);

            font-size: 15px;

            font-weight: 800;

        }



        .invoice-status-row {

            display: flex;

            flex-wrap: wrap;

            justify-content: flex-end;

            gap: 7px;

            margin-top: 17px;

        }



        .status-badge {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 6px 10px;

            border-radius: 999px;

            font-size: 10px;

            font-weight: 800;

            text-transform: capitalize;

        }



        .status-badge::before {

            width: 6px;

            height: 6px;

            background: currentColor;

            border-radius: 50%;

            content: '';

        }



        .status-success {

            color: var(--invoice-success);

            background: var(--invoice-success-bg);

        }



        .status-warning {

            color: var(--invoice-warning);

            background: var(--invoice-warning-bg);

        }



        .status-danger {

            color: var(--invoice-danger);

            background: var(--invoice-danger-bg);

        }



        .status-neutral {

            color: #4b5563;

            background: #f3f4f6;

        }



        .invoice-meta-grid {

            display: grid;

            grid-template-columns: repeat(4, minmax(0, 1fr));

            margin: 30px 0;

            overflow: hidden;

            border: 1px solid var(--invoice-border);

            border-radius: 12px;

        }



        .invoice-meta-item {

            min-width: 0;

            padding: 15px;

            border-right: 1px solid var(--invoice-border);

        }



        .invoice-meta-item:last-child {

            border-right: 0;

        }



        .invoice-meta-item span {

            display: block;

            margin-bottom: 5px;

            color: var(--invoice-muted);

            font-size: 9px;

            font-weight: 800;

            letter-spacing: 0.7px;

            text-transform: uppercase;

        }



        .invoice-meta-item strong {

            display: block;

            overflow-wrap: anywhere;

            color: var(--invoice-primary);

            font-size: 12px;

        }



        .address-grid {

            display: grid;

            grid-template-columns: repeat(2, minmax(0, 1fr));

            gap: 25px;

            margin-bottom: 32px;

        }



        .address-card {

            padding: 20px;

            background: #f9fafb;

            border: 1px solid var(--invoice-border);

            border-radius: 12px;

        }



        .address-card-title {

            display: block;

            margin-bottom: 12px;

            color: var(--invoice-accent);

            font-size: 9px;

            font-weight: 900;

            letter-spacing: 1px;

            text-transform: uppercase;

        }



        .address-card strong {

            display: block;

            margin-bottom: 6px;

            color: var(--invoice-primary);

            font-size: 14px;

        }



        .address-card address {

            display: grid;

            gap: 3px;

            margin: 0;

            color: var(--invoice-muted);

            font-size: 11px;

            font-style: normal;

            line-height: 1.55;

        }



        .address-card a {

            color: var(--invoice-text);

            text-decoration: none;

        }



        .invoice-section-title {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin: 0 0 13px;

        }



        .invoice-section-title h3 {

            margin: 0;

            color: var(--invoice-primary);

            font-size: 16px;

        }



        .invoice-section-title span {

            color: var(--invoice-muted);

            font-size: 10px;

        }



        .invoice-products {

            width: 100%;

            border-collapse: collapse;

        }



        .invoice-products thead {

            background: var(--invoice-primary);

        }



        .invoice-products th {

            padding: 12px 10px;

            color: #ffffff;

            font-size: 9px;

            font-weight: 800;

            letter-spacing: 0.6px;

            text-align: left;

            text-transform: uppercase;

        }



        .invoice-products th:nth-child(3),

        .invoice-products th:nth-child(4),

        .invoice-products th:nth-child(5) {

            text-align: right;

        }



        .invoice-products td {

            padding: 16px 10px;

            border-bottom: 1px solid var(--invoice-border);

            vertical-align: top;

        }



        .invoice-products td:nth-child(3),

        .invoice-products td:nth-child(4),

        .invoice-products td:nth-child(5) {

            text-align: right;

            white-space: nowrap;

        }



        .invoice-product {

            display: flex;

            align-items: flex-start;

            gap: 12px;

        }



        .invoice-product-image {

            flex: 0 0 55px;

            width: 55px;

            height: 60px;

            overflow: hidden;

            background: #f3f4f6;

            border: 1px solid var(--invoice-border);

            border-radius: 8px;

        }



        .invoice-product-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;

        }



        .invoice-image-placeholder {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 100%;

            height: 100%;

            color: #9ca3af;

            font-size: 18px;

        }



        .invoice-product-info {

            min-width: 0;

        }



        .invoice-product-info strong {

            display: block;

            margin-bottom: 4px;

            color: var(--invoice-primary);

            font-size: 12px;

            line-height: 1.4;

        }



        .invoice-product-info small {

            display: block;

            color: var(--invoice-muted);

            font-size: 9px;

        }



        .variant-list {

            display: flex;

            flex-wrap: wrap;

            gap: 5px;

            margin-top: 8px;

        }



        .variant-item {

            display: inline-flex;

            gap: 4px;

            padding: 4px 6px;

            color: #4b5563;

            background: #f3f4f6;

            border-radius: 5px;

            font-size: 8px;

        }



        .variant-item b {

            color: var(--invoice-primary);

        }



        .product-sku {

            color: var(--invoice-muted);

            font-size: 9px;

        }



        .invoice-summary-area {

            display: grid;

            grid-template-columns: minmax(0, 1fr) 330px;

            align-items: start;

            gap: 35px;

            margin-top: 28px;

        }



        .payment-details {

            padding: 18px;

            background: #f9fafb;

            border: 1px solid var(--invoice-border);

            border-radius: 11px;

        }



        .payment-details h3 {

            margin: 0 0 13px;

            color: var(--invoice-primary);

            font-size: 14px;

        }



        .payment-detail-row {

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 15px;

            padding: 7px 0;

            border-bottom: 1px solid var(--invoice-border);

            font-size: 10px;

        }



        .payment-detail-row:last-child {

            border-bottom: 0;

        }



        .payment-detail-row span {

            color: var(--invoice-muted);

        }



        .payment-detail-row strong {

            max-width: 62%;

            color: var(--invoice-primary);

            text-align: right;

            overflow-wrap: anywhere;

        }



        .invoice-totals {

            padding: 19px;

            background: #f9fafb;

            border: 1px solid var(--invoice-border);

            border-radius: 11px;

        }



        .invoice-total-row {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 7px 0;

            color: var(--invoice-muted);

            font-size: 11px;

        }



        .invoice-total-row strong {

            color: var(--invoice-primary);

        }



        .discount-amount {

            color: var(--invoice-success) !important;

        }



        .invoice-total-divider {

            height: 1px;

            margin: 10px 0;

            background: var(--invoice-border);

        }



        .invoice-grand-total {

            color: var(--invoice-primary);

            font-size: 15px;

            font-weight: 800;

        }



        .invoice-grand-total strong {

            font-size: 21px;

        }



        .currency-code {

            display: block;

            color: var(--invoice-muted);

            font-size: 8px;

            font-weight: 700;

            text-align: right;

        }



        .invoice-notes {

            margin-top: 35px;

            padding: 18px;

            background: #fffbeb;

            border: 1px solid #fde68a;

            border-radius: 11px;

        }



        .invoice-notes strong {

            display: block;

            margin-bottom: 6px;

            color: #92400e;

            font-size: 11px;

        }



        .invoice-notes p {

            margin: 0;

            color: #a16207;

            font-size: 10px;

            line-height: 1.6;

            white-space: pre-line;

        }



        .invoice-footer {

            display: grid;

            grid-template-columns: 1fr auto;

            align-items: end;

            gap: 30px;

            padding-top: 30px;

            margin-top: 45px;

            border-top: 1px solid var(--invoice-border);

        }



        .invoice-footer-message h3 {

            margin: 0 0 6px;

            color: var(--invoice-primary);

            font-size: 16px;

        }



        .invoice-footer-message p {

            max-width: 520px;

            margin: 0;

            color: var(--invoice-muted);

            font-size: 10px;

            line-height: 1.6;

        }



        .invoice-reference-box {

            min-width: 160px;

            padding: 13px;

            text-align: center;

            border: 1px solid var(--invoice-border);

            border-radius: 9px;

        }



        .invoice-reference-code {

            display: flex;

            align-items: center;

            justify-content: center;

            height: 55px;

            margin-bottom: 7px;

            color: var(--invoice-primary);

            background:

                repeating-linear-gradient(90deg,

                    #111827 0,

                    #111827 2px,

                    transparent 2px,

                    transparent 5px);

            font-size: 0;

        }



        .invoice-reference-box strong {

            display: block;

            color: var(--invoice-primary);

            font-size: 10px;

        }



        .invoice-reference-box span {

            display: block;

            margin-top: 2px;

            color: var(--invoice-muted);

            font-size: 8px;

        }



        .invoice-generated {

            margin-top: 25px;

            color: #9ca3af;

            font-size: 8px;

            text-align: center;

        }



        .empty-products {

            padding: 30px 15px !important;

            color: var(--invoice-muted);

            text-align: center !important;

        }



        @media (max-width: 800px) {

            .invoice-toolbar {

                align-items: flex-start;

                flex-direction: column;

            }



            .invoice-toolbar-actions {

                width: 100%;

            }



            .toolbar-button {

                flex: 1;

            }



            .invoice-page-wrapper {

                padding: 20px 10px 45px;

            }



            .invoice-page {

                min-height: auto;

                padding: 28px 20px;

            }



            .invoice-header {

                flex-direction: column;

            }



            .invoice-heading {

                min-width: 0;

                text-align: left;

            }



            .invoice-status-row {

                justify-content: flex-start;

            }



            .invoice-meta-grid {

                grid-template-columns: repeat(2, minmax(0, 1fr));

            }



            .invoice-meta-item:nth-child(2) {

                border-right: 0;

            }



            .invoice-meta-item:nth-child(-n + 2) {

                border-bottom: 1px solid var(--invoice-border);

            }



            .address-grid,

            .invoice-summary-area {

                grid-template-columns: 1fr;

            }



            .invoice-products {

                min-width: 700px;

            }



            .invoice-products-wrapper {

                overflow-x: auto;

            }



            .invoice-footer {

                grid-template-columns: 1fr;

            }



            .invoice-reference-box {

                max-width: 200px;

            }

        }



        @page {

            size: A4;

            margin: 10mm;

        }



        @media print {



            html,

            body {

                background: #ffffff !important;

            }



            body {

                font-size: 11px;

                print-color-adjust: exact;

                -webkit-print-color-adjust: exact;

            }



            .invoice-toolbar {

                display: none !important;

            }



            .invoice-page-wrapper {

                padding: 0;

            }



            .invoice-page {

                width: 100%;

                max-width: none;

                min-height: auto;

                padding: 10mm;

                box-shadow: none;

            }



            .invoice-header {

                padding-bottom: 20px;

            }



            .invoice-heading h2 {

                font-size: 32px;

            }



            .invoice-meta-grid {

                margin: 20px 0;

            }



            .address-grid {

                margin-bottom: 22px;

            }



            .invoice-products th {

                padding: 9px 8px;

            }



            .invoice-products td {

                padding: 11px 8px;

            }



            .invoice-product-image {

                width: 42px;

                height: 46px;

                flex-basis: 42px;

            }



            .invoice-summary-area {

                margin-top: 20px;

            }



            .invoice-notes,

            .invoice-footer,

            .address-card,

            .payment-details,

            .invoice-totals {

                break-inside: avoid;

            }



            .invoice-products tr {

                break-inside: avoid;

            }



            a {

                color: inherit !important;

                text-decoration: none !important;

            }

        }

    .invoice-coupon-code{display:inline-block;margin-left:5px;padding:2px 6px;color:var(--invoice-accent);background:#eef2ff;border:1px solid #d7dbff;border-radius:999px;font-size:8px;font-weight:800;line-height:1.3;vertical-align:middle}
</style>

</head>



<body>



    <div class="invoice-toolbar">

        <div class="invoice-toolbar-left">

            <a

                href="<?php echo e($invoiceBackUrl); ?>"

                class="toolbar-button">

                ← Back to order

            </a>



            <div class="invoice-toolbar-title">

                <strong><?php echo e($invoiceNumber); ?></strong>

                <span><?php echo e($orderNumber); ?></span>

            </div>

        </div>



        <div class="invoice-toolbar-actions">

            <button

                type="button"

                class="toolbar-button"

                onclick="window\.print()">

                Print invoice

            </button>



            <a

                href="<?php echo e($invoiceDownloadUrl); ?>"

                class="toolbar-button toolbar-button-primary">

                Download PDF

            </a>

        </div>

    </div>



    <main class="invoice-page-wrapper">

        <article class="invoice-page">



            <header class="invoice-header">

                <div class="company-brand">

                    <div class="company-logo">

                        Arizona Outfits

                    </div>



                    <h1>Arizona Outfits</h1>



                    <p>

                        Premium clothing and lifestyle products.<br>

                        Email: support@arizonaoutfits.com<br>

                        Website: arizonaoutfits.com

                    </p>

                </div>



                <div class="invoice-heading">

                    <h2>Invoice</h2>



                    <span class="invoice-number">

                        <?php echo e($invoiceNumber); ?>


                    </span>



                    <div class="invoice-status-row">

                        <span class="status-badge <?php echo e($statusClass); ?>">

                            <?php echo e(str_replace('_', ' ', $orderStatus)); ?>


                        </span>



                        <span class="status-badge <?php echo e($paymentClass); ?>">

                            Payment:

                            <?php echo e(str_replace('_', ' ', $paymentStatus)); ?>


                        </span>

                    </div>

                </div>

            </header>



            <section class="invoice-meta-grid">

                <div class="invoice-meta-item">

                    <span>Invoice date</span>



                    <strong>

                        <?php echo e(optional($order->created_at)->format('d M Y')); ?>


                    </strong>

                </div>



                <div class="invoice-meta-item">

                    <span>Order number</span>



                    <strong><?php echo e($orderNumber); ?></strong>

                </div>



                <div class="invoice-meta-item">

                    <span>Payment method</span>



                    <strong><?php echo e($paymentMethod); ?></strong>

                </div>



                <div class="invoice-meta-item">

                    <span>Currency</span>



                    <strong><?php echo e($currencyCode); ?></strong>

                </div>

            </section>



            <section class="address-grid">

                <div class="address-card">

                    <span class="address-card-title">

                        Bill to

                    </span>



                    <strong>

                        <?php echo e($order->billing_name ?: $customerName); ?>


                    </strong>



                    <address>

                        <?php $__empty_1 = true; $__currentLoopData = $billingAddress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <span><?php echo e($line); ?></span>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <span>No billing address provided.</span>

                        <?php endif; ?>



                        <?php if($customerEmail): ?>

                        <a href="mailto:<?php echo e($customerEmail); ?>">

                            <?php echo e($customerEmail); ?>


                        </a>

                        <?php endif; ?>



                        <?php if($customerPhone): ?>

                        <a href="tel:<?php echo e($customerPhone); ?>">

                            <?php echo e($customerPhone); ?>


                        </a>

                        <?php endif; ?>

                    </address>

                </div>



                <div class="address-card">

                    <span class="address-card-title">

                        Ship to

                    </span>



                    <strong>

                        <?php echo e($order->shipping_name ?: $customerName); ?>


                    </strong>



                    <address>

                        <?php $__empty_1 = true; $__currentLoopData = $shippingAddress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <span><?php echo e($line); ?></span>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <span>No shipping address provided.</span>

                        <?php endif; ?>



                        <?php if($order->shipping_email): ?>

                        <a href="mailto:<?php echo e($order->shipping_email); ?>">

                            <?php echo e($order->shipping_email); ?>


                        </a>

                        <?php endif; ?>



                        <?php if($order->shipping_phone): ?>

                        <a href="tel:<?php echo e($order->shipping_phone); ?>">

                            <?php echo e($order->shipping_phone); ?>


                        </a>

                        <?php endif; ?>

                    </address>

                </div>

            </section>



            <section>

                <div class="invoice-section-title">

                    <h3>Order items</h3>



                    <span>

                        <?php echo e($order->items->sum('quantity')); ?>


                        unit(s)

                    </span>

                </div>



                <div class="invoice-products-wrapper">

                    <table class="invoice-products">

                        <thead>

                            <tr>

                                <th>Product</th>

                                <th>SKU</th>

                                <th>Price</th>

                                <th>Quantity</th>

                                <th>Total</th>

                            </tr>

                        </thead>



                        <tbody>

                            <?php $__empty_1 = true; $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <?php

                            $productName = $item->product_name

                            ?: $item->product?->title

                            ?: $item->product_title

                            ?: 'Deleted product';



                            $productImage = $item->product?->featured_image_url;



                            if (!$productImage && $item->product?->images?->isNotEmpty()) {

                            $firstImage = $item->product->images->first();



                            $productImage = $firstImage->image_url

                            ?? $firstImage->url

                            ?? $firstImage->path

                            ?? null;



                            if (

                            $productImage

                            && !str_starts_with($productImage, 'http')

                            && !str_starts_with($productImage, '/')

                            ) {

                            $productImage = asset('storage/' . $productImage);

                            }

                            }



                            $lineTotal = $item->subtotal !== null

                            ? (float) $item->subtotal

                            : (float) $item->price * (int) $item->quantity;



                            $displayOptions = $item->display_options ?? [];



                            if (

                            empty($displayOptions)

                            && is_array($item->options ?? null)

                            ) {

                            $displayOptions = collect($item->options)

                            ->map(function ($value, $name) {

                            return [

                            'name' => ucwords(

                            str_replace(

                            ['_', '-'],

                            ' ',

                            (string) $name

                            )

                            ),



                            'value' => is_array($value)

                            ? implode(', ', $value)

                            : (string) $value,

                            ];

                            })

                            ->values()

                            ->all();

                            }

                            ?>



                            <tr>

                                <td>

                                    <div class="invoice-product">

                                        <div class="invoice-product-image">

                                            <?php if($productImage): ?>

                                            <img

                                                src="<?php echo e($productImage); ?>"

                                                alt="<?php echo e($productName); ?>">

                                            <?php else: ?>

                                            <div class="invoice-image-placeholder">

                                                □

                                            </div>

                                            <?php endif; ?>

                                        </div>



                                        <div class="invoice-product-info">

                                            <strong>

                                                <?php echo e($productName); ?>


                                            </strong>



                                            <?php if(!empty($displayOptions)): ?>

                                            <div class="variant-list">

                                                <?php $__currentLoopData = $displayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                <span class="variant-item">

                                                    <?php echo e($option['name'] ?? 'Option'); ?>:



                                                    <b>

                                                        <?php echo e($option['value'] ?? ''); ?>


                                                    </b>

                                                </span>

                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            </div>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                </td>



                                <td>

                                    <span class="product-sku">

                                        <?php echo e($item->sku

                                                ?: $item->product?->sku

                                                ?: 'N/A'); ?>


                                    </span>

                                </td>



                                <td>

                                    <?php echo e($money($item->price)); ?>


                                </td>



                                <td>

                                    <?php echo e($item->quantity); ?>


                                </td>



                                <td>

                                    <strong>

                                        <?php echo e($money($lineTotal)); ?>


                                    </strong>

                                </td>

                            </tr>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td

                                    colspan="5"

                                    class="empty-products">

                                    No order items were found.

                                </td>

                            </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>



            <section class="invoice-summary-area">

                <div class="payment-details">

                    <h3>Payment and delivery details</h3>



                    <div class="payment-detail-row">

                        <span>Payment method</span>



                        <strong><?php echo e($paymentMethod); ?></strong>

                    </div>



                    <div class="payment-detail-row">

                        <span>Payment status</span>



                        <strong>

                            <?php echo e(ucwords(

                                str_replace(

                                    '_',

                                    ' ',

                                    $paymentStatus

                                )

                            )); ?>


                        </strong>

                    </div>



                    <div class="payment-detail-row">

                        <span>Payment reference</span>



                        <strong>

                            <?php echo e($paymentReference ?: 'N/A'); ?>


                        </strong>

                    </div>



                    <div class="payment-detail-row">

                        <span>Tracking number</span>



                        <strong>

                            <?php echo e($trackingNumber ?: 'Not available'); ?>


                        </strong>

                    </div>

                </div>



                <div class="invoice-totals">

                    <div class="invoice-total-row">

                        <span>Subtotal</span>



                        <strong><?php echo e($money($subtotal)); ?></strong>

                    </div>



                    <div class="invoice-total-row">

                        <span>Discount
                            <?php if(!empty($order->coupon_code)): ?>
                                <small class="invoice-coupon-code"><?php echo e($order->coupon_code); ?></small>
                            <?php endif; ?></span>



                        <strong class="<?php echo e($discount > 0 ? 'discount-amount' : ''); ?>">

                            <?php echo e($discount > 0 ? '-' : ''); ?>


                            <?php echo e($money($discount)); ?>


                        </strong>

                    </div>



                    <div class="invoice-total-row">

                        <span>Shipping</span>



                        <strong><?php echo e($money($shipping)); ?></strong>

                    </div>



                    <div class="invoice-total-row">

                        <span>Tax</span>



                        <strong><?php echo e($money($tax)); ?></strong>

                    </div>



                    <div class="invoice-total-divider"></div>



                    <div class="invoice-total-row invoice-grand-total">

                        <span>Total</span>



                        <div>

                            <span class="currency-code">

                                <?php echo e($currencyCode); ?>


                            </span>



                            <strong><?php echo e($money($total)); ?></strong>

                        </div>

                    </div>

                </div>

            </section>



            <footer class="invoice-footer">

                <div class="invoice-footer-message">

                    <h3>Thank you for your order.</h3>



                    <p>

                        Please keep this invoice for your records. For questions

                        about this order, contact our customer support team and

                        mention order number <?php echo e($orderNumber); ?>.

                    </p>

                </div>



                <div class="invoice-reference-box">

                    <div class="invoice-reference-code">

                        <?php echo e($orderNumber); ?>


                    </div>



                    <strong><?php echo e($orderNumber); ?></strong>



                    <span>Order reference</span>

                </div>

            </footer>



            <p class="invoice-generated">

                Invoice generated on

                <?php echo e(now()->format('d M Y \a\t h:i A')); ?>


            </p>

        </article>

    </main>



    <script>

        document.addEventListener('keydown', function(event) {

            if (

                (event.ctrlKey || event.metaKey) &&

                event.key.toLowerCase() === 'p'

            ) {

                event.preventDefault();

                window\.print();

            }

        });

    </script>

</body>



</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/orders/invoice.blade.php ENDPATH**/ ?>