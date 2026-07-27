@php
    $orderNumber = $order->order_number
        ?: 'ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

    $customerName = $order->shipping_name
        ?: $order->billing_name
        ?: $order->user?->name
        ?: 'Guest customer';

    $customerEmail = $order->shipping_email
        ?: $order->billing_email
        ?: $order->user?->email
        ?: null;

    $customerPhone = $order->shipping_phone
        ?: $order->billing_phone
        ?: null;

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

    $orderStatus = ucwords(
        str_replace(
            ['_', '-'],
            ' ',
            $order->order_status
                ?? $order->status
                ?? 'pending'
        )
    );

    $shippingMethod = ucwords(
        str_replace(
            ['_', '-'],
            ' ',
            $order->shipping_method
                ?? $order->delivery_method
                ?? 'Standard delivery'
        )
    );

    $trackingNumber = $order->tracking_number
        ?? $order->shipment_tracking_number
        ?? null;

    $totalQuantity = $order->items->sum(function ($item) {
        return (int) ($item->quantity ?? 0);
    });

    $resolveImage = function ($item) {
        $product = $item->product;

        if (!$product) {
            return null;
        }

        $imagePath = $product->featured_image
            ?? $product->image
            ?? $product->image_path
            ?? null;

        if (!$imagePath && $product->images?->isNotEmpty()) {
            $firstImage = $product->images->first();

            $imagePath = $firstImage->path
                ?? $firstImage->image
                ?? $firstImage->image_path
                ?? $firstImage->url
                ?? null;
        }

        if (!$imagePath) {
            return null;
        }

        if (
            str_starts_with($imagePath, 'http://')
            || str_starts_with($imagePath, 'https://')
        ) {
            return $imagePath;
        }

        return asset(
            str_starts_with($imagePath, 'storage/')
                ? $imagePath
                : 'storage/' . ltrim($imagePath, '/')
        );
    };

    $getOptions = function ($item) {
        $options = [];

        if (!empty($item->display_options) && is_iterable($item->display_options)) {
            foreach ($item->display_options as $option) {
                $options[] = [
                    'name' => $option['name'] ?? 'Option',
                    'value' => $option['value'] ?? '',
                ];
            }
        }

        if (empty($options) && is_array($item->options ?? null)) {
            foreach ($item->options as $name => $value) {
                $options[] = [
                    'name' => ucwords(
                        str_replace(['_', '-'], ' ', (string) $name)
                    ),
                    'value' => is_array($value)
                        ? implode(', ', $value)
                        : (string) $value,
                ];
            }
        }

        if (empty($options) && $item->variant) {
            foreach (['size', 'color', 'material'] as $field) {
                if (!empty($item->variant->{$field})) {
                    $options[] = [
                        'name' => ucfirst($field),
                        'value' => $item->variant->{$field},
                    ];
                }
            }

            if (
                empty($options)
                && !empty($item->variant->name)
            ) {
                $options[] = [
                    'name' => 'Variant',
                    'value' => $item->variant->name,
                ];
            }
        }

        return $options;
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Packing Slip {{ $orderNumber }}
    </title>

    <style>
        :root {
            --primary: #111827;
            --primary-soft: #f3f4f6;
            --accent: #4f46e5;
            --success: #15803d;
            --border: #e5e7eb;
            --muted: #6b7280;
            --background: #f4f5f7;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--primary);
            background: var(--background);
            font-family:
                Inter,
                Arial,
                Helvetica,
                sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        a {
            font: inherit;
        }

        .packing-page {
            width: 100%;
            padding: 28px 20px 50px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto 18px;
        }

        .toolbar-left,
        .toolbar-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .toolbar-button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 42px;
            padding: 10px 16px;
            color: var(--primary);
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease;
        }

        .toolbar-button:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        .toolbar-button-primary {
            color: var(--white);
            background: var(--primary);
            border-color: var(--primary);
        }

        .toolbar-button-primary:hover {
            color: var(--white);
            background: #1f2937;
            border-color: #1f2937;
        }

        .document {
            width: 100%;
            max-width: 1100px;
            min-height: 1120px;
            margin: 0 auto;
            padding: 44px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(17, 24, 39, 0.08);
        }

        .document-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 30px;
            padding-bottom: 26px;
            border-bottom: 2px solid var(--primary);
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 190px;
            min-height: 58px;
            padding: 12px 20px;
            color: var(--white);
            background: var(--primary);
            border-radius: 8px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        .company-name {
            margin: 12px 0 3px;
            font-size: 18px;
            font-weight: 800;
        }

        .company-details {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.65;
        }

        .document-title-box {
            text-align: right;
        }

        .document-title {
            margin: 0;
            font-size: 36px;
            font-weight: 900;
            letter-spacing: 2px;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .document-number {
            margin-top: 10px;
            color: var(--accent);
            font-size: 16px;
            font-weight: 800;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            margin-top: 12px;
            padding: 6px 10px;
            color: var(--success);
            background: #dcfce7;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .order-meta {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-top: 24px;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .meta-item {
            min-height: 82px;
            padding: 16px;
            border-right: 1px solid var(--border);
        }

        .meta-item:last-child {
            border-right: 0;
        }

        .meta-label {
            display: block;
            margin-bottom: 5px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .meta-value {
            display: block;
            color: var(--primary);
            font-size: 14px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .details-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.6fr);
            gap: 18px;
            margin-top: 24px;
        }

        .detail-card {
            min-height: 190px;
            padding: 20px;
            background: #f9fafb;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .card-label {
            margin-bottom: 12px;
            color: var(--accent);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .customer-name {
            margin-bottom: 7px;
            font-size: 17px;
            font-weight: 900;
        }

        .address-line,
        .contact-line {
            color: var(--muted);
            line-height: 1.7;
        }

        .contact-lines {
            margin-top: 9px;
        }

        .warehouse-row {
            display: grid;
            grid-template-columns: 130px minmax(0, 1fr);
            align-items: end;
            gap: 12px;
            margin-bottom: 22px;
        }

        .warehouse-row:last-child {
            margin-bottom: 0;
        }

        .warehouse-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }

        .warehouse-line {
            min-height: 24px;
            border-bottom: 1px solid #9ca3af;
        }

        .section-heading-row {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 20px;
            margin: 30px 0 12px;
        }

        .section-heading {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
        }

        .section-summary {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .items-wrapper {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .items-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }

        .items-table th {
            padding: 13px 12px;
            color: var(--white);
            background: var(--primary);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .check-column {
            width: 45px;
            text-align: center !important;
        }

        .quantity-column {
            width: 95px;
            text-align: center !important;
        }

        .warehouse-checkbox {
            display: inline-block;
            width: 20px;
            height: 20px;
            background: var(--white);
            border: 2px solid #9ca3af;
            border-radius: 4px;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .product-image,
        .product-placeholder {
            flex: 0 0 64px;
            width: 64px;
            height: 72px;
            border: 1px solid var(--border);
            border-radius: 7px;
        }

        .product-image {
            object-fit: cover;
        }

        .product-placeholder {
            display: flex;
            justify-content: center;
            align-items: center;
            color: #9ca3af;
            background: var(--primary-soft);
            font-size: 10px;
            font-weight: 700;
            text-align: center;
        }

        .product-name {
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 900;
        }

        .product-id {
            color: var(--muted);
            font-size: 12px;
        }

        .sku-text {
            color: var(--primary);
            font-size: 13px;
            font-weight: 800;
        }

        .option-badge {
            display: inline-block;
            margin: 2px 4px 2px 0;
            padding: 4px 7px;
            color: #4b5563;
            background: var(--primary-soft);
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
        }

        .quantity-value {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-width: 42px;
            min-height: 38px;
            padding: 5px 10px;
            color: var(--primary);
            background: var(--primary-soft);
            border-radius: 6px;
            font-size: 16px;
            font-weight: 900;
        }

        .empty-items {
            padding: 35px !important;
            color: var(--muted);
            text-align: center;
        }

        .notes-signature-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.65fr);
            gap: 18px;
            margin-top: 26px;
        }

        .notes-card,
        .signature-card {
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .notes-title,
        .signature-title {
            margin-bottom: 15px;
            font-size: 15px;
            font-weight: 900;
        }

        .notes-area {
            min-height: 150px;
            padding: 8px 0;
            background:
                repeating-linear-gradient(
                    to bottom,
                    transparent,
                    transparent 32px,
                    #d1d5db 33px
                );
        }

        .signature-field {
            margin-bottom: 25px;
        }

        .signature-field:last-child {
            margin-bottom: 0;
        }

        .signature-label {
            display: block;
            margin-bottom: 18px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }

        .signature-line {
            min-height: 20px;
            border-bottom: 1px solid #9ca3af;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 230px;
            align-items: end;
            gap: 35px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .footer-title {
            margin-bottom: 5px;
            font-size: 17px;
            font-weight: 900;
        }

        .footer-text {
            max-width: 620px;
            color: var(--muted);
            font-size: 12px;
        }

        .barcode-box {
            padding: 13px;
            border: 1px solid var(--border);
            border-radius: 7px;
            text-align: center;
        }

        .barcode {
            height: 52px;
            margin-bottom: 8px;
            background:
                repeating-linear-gradient(
                    90deg,
                    #111827 0,
                    #111827 2px,
                    transparent 2px,
                    transparent 4px,
                    #111827 4px,
                    #111827 5px,
                    transparent 5px,
                    transparent 8px
                );
        }

        .barcode-number {
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .barcode-label {
            margin-top: 3px;
            color: var(--muted);
            font-size: 10px;
        }

        .print-note {
            margin-top: 22px;
            color: #9ca3af;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 850px) {
            .document {
                padding: 25px;
            }

            .document-header,
            .toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .document-title-box {
                text-align: left;
            }

            .order-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .meta-item:nth-child(2) {
                border-right: 0;
            }

            .meta-item:nth-child(-n + 2) {
                border-bottom: 1px solid var(--border);
            }

            .details-grid,
            .notes-signature-grid,
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {
            .packing-page {
                padding: 15px 10px 35px;
            }

            .document {
                padding: 20px 15px;
                border-radius: 8px;
            }

            .toolbar-right {
                width: 100%;
            }

            .toolbar-button {
                flex: 1;
            }

            .order-meta {
                grid-template-columns: 1fr;
            }

            .meta-item {
                border-right: 0;
                border-bottom: 1px solid var(--border);
            }

            .meta-item:last-child {
                border-bottom: 0;
            }

            .document-title {
                font-size: 28px;
            }

            .brand-mark {
                min-width: 0;
                width: 100%;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm;
            }

            body {
                background: var(--white);
                font-size: 11px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .packing-page {
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .document {
                width: 100%;
                max-width: none;
                min-height: auto;
                margin: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .document-header {
                padding-bottom: 15px;
            }

            .brand-mark {
                min-width: 160px;
                min-height: 46px;
                padding: 8px 13px;
                font-size: 17px;
            }

            .document-title {
                font-size: 27px;
            }

            .order-meta {
                margin-top: 15px;
            }

            .meta-item {
                min-height: 62px;
                padding: 10px;
            }

            .details-grid {
                grid-template-columns: 1.4fr 0.6fr;
                gap: 10px;
                margin-top: 15px;
            }

            .detail-card {
                min-height: 145px;
                padding: 13px;
            }

            .section-heading-row {
                margin: 18px 0 8px;
            }

            .items-wrapper {
                overflow: visible;
            }

            .items-table {
                min-width: 0;
            }

            .items-table thead {
                display: table-header-group;
            }

            .items-table tr,
            .notes-signature-grid,
            .footer-grid {
                page-break-inside: avoid;
            }

            .items-table th {
                padding: 8px;
            }

            .items-table td {
                padding: 8px;
            }

            .product-image,
            .product-placeholder {
                flex-basis: 45px;
                width: 45px;
                height: 52px;
            }

            .notes-signature-grid {
                grid-template-columns: 1.35fr 0.65fr;
                gap: 10px;
                margin-top: 16px;
            }

            .notes-card,
            .signature-card {
                padding: 12px;
            }

            .notes-area {
                min-height: 100px;
            }

            .footer-grid {
                grid-template-columns: 1fr 180px;
                margin-top: 18px;
                padding-top: 14px;
            }

            .print-note {
                display: none;
            }
        }
    </style>
</head>

<body>
    <main class="packing-page">
        <div class="toolbar">
            <div class="toolbar-left">
                <a
                    href="{{ route('admin.orders.show', $order) }}"
                    class="toolbar-button"
                >
                    ← Back to order
                </a>
            </div>

            <div class="toolbar-right">
                <button
                    type="button"
                    class="toolbar-button"
                    onclick="window.print()"
                >
                    Print packing slip
                </button>

                <a
                    href="{{ route('admin.orders.packing-slip.download', $order) }}"
                    class="toolbar-button toolbar-button-primary"
                >
                    Download PDF
                </a>
            </div>
        </div>

        <section class="document">
            <header class="document-header">
                <div>
                    <div class="brand-mark">
                        Arizona Outfits
                    </div>

                    <div class="company-name">
                        Arizona Outfits
                    </div>

                    <div class="company-details">
                        Premium clothing and lifestyle products.<br>
                        Email: support@arizonaoutfits.com<br>
                        Website: arizonaoutfits.com
                    </div>
                </div>

                <div class="document-title-box">
                    <h1 class="document-title">
                        Packing Slip
                    </h1>

                    <div class="document-number">
                        {{ $orderNumber }}
                    </div>

                    <div class="status-badge">
                        {{ $orderStatus }}
                    </div>
                </div>
            </header>

            <section class="order-meta">
                <div class="meta-item">
                    <span class="meta-label">
                        Order number
                    </span>

                    <span class="meta-value">
                        {{ $orderNumber }}
                    </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">
                        Order date
                    </span>

                    <span class="meta-value">
                        {{ $order->created_at?->format('d M Y') ?? 'N/A' }}
                    </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">
                        Shipping method
                    </span>

                    <span class="meta-value">
                        {{ $shippingMethod }}
                    </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">
                        Tracking number
                    </span>

                    <span class="meta-value">
                        {{ $trackingNumber ?: 'Not assigned' }}
                    </span>
                </div>
            </section>

            <section class="details-grid">
                <div class="detail-card">
                    <div class="card-label">
                        Ship to
                    </div>

                    <div class="customer-name">
                        {{ $customerName }}
                    </div>

                    @forelse ($shippingAddress as $line)
                        <div class="address-line">
                            {{ $line }}
                        </div>
                    @empty
                        <div class="address-line">
                            No shipping address was provided.
                        </div>
                    @endforelse

                    <div class="contact-lines">
                        @if ($customerEmail)
                            <div class="contact-line">
                                {{ $customerEmail }}
                            </div>
                        @endif

                        @if ($customerPhone)
                            <div class="contact-line">
                                {{ $customerPhone }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="detail-card">
                    <div class="card-label">
                        Warehouse processing
                    </div>

                    <div class="warehouse-row">
                        <span class="warehouse-label">
                            Picker
                        </span>

                        <span class="warehouse-line"></span>
                    </div>

                    <div class="warehouse-row">
                        <span class="warehouse-label">
                            Packer
                        </span>

                        <span class="warehouse-line"></span>
                    </div>

                    <div class="warehouse-row">
                        <span class="warehouse-label">
                            Dispatch date
                        </span>

                        <span class="warehouse-line"></span>
                    </div>
                </div>
            </section>

            <div class="section-heading-row">
                <h2 class="section-heading">
                    Items to pack
                </h2>

                <div class="section-summary">
                    {{ $order->items->count() }} product line(s) ·
                    {{ $totalQuantity }} unit(s)
                </div>
            </div>

            <div class="items-wrapper">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="check-column">
                                Packed
                            </th>

                            <th>
                                Product
                            </th>

                            <th>
                                SKU
                            </th>

                            <th>
                                Variant
                            </th>

                            <th class="quantity-column">
                                Quantity
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($order->items as $item)
                            @php
                                $productName = $item->product_name
                                    ?: $item->product?->title
                                    ?: $item->product_title
                                    ?: 'Deleted product';

                                $productImage = $resolveImage($item);
                                $options = $getOptions($item);

                                $sku = $item->sku
                                    ?: $item->variant?->sku
                                    ?: $item->product?->sku
                                    ?: 'N/A';
                            @endphp

                            <tr>
                                <td class="check-column">
                                    <span class="warehouse-checkbox"></span>
                                </td>

                                <td>
                                    <div class="product-cell">
                                        @if ($productImage)
                                            <img
                                                src="{{ $productImage }}"
                                                alt="{{ $productName }}"
                                                class="product-image"
                                            >
                                        @else
                                            <div class="product-placeholder">
                                                No image
                                            </div>
                                        @endif

                                        <div>
                                            <div class="product-name">
                                                {{ $productName }}
                                            </div>

                                            <div class="product-id">
                                                Product ID:
                                                {{ $item->product_id ?: 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="sku-text">
                                        {{ $sku }}
                                    </span>
                                </td>

                                <td>
                                    @forelse ($options as $option)
                                        <span class="option-badge">
                                            {{ $option['name'] }}:
                                            {{ $option['value'] }}
                                        </span>
                                    @empty
                                        <span class="product-id">
                                            Standard
                                        </span>
                                    @endforelse
                                </td>

                                <td class="quantity-column">
                                    <span class="quantity-value">
                                        {{ (int) $item->quantity }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="empty-items"
                                >
                                    No items were found for this order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <section class="notes-signature-grid">
                <div class="notes-card">
                    <div class="notes-title">
                        Warehouse notes
                    </div>

                    <div class="notes-area">
                        @if (!empty($order->admin_notes))
                            {{ $order->admin_notes }}
                        @elseif (!empty($order->notes))
                            {{ $order->notes }}
                        @endif
                    </div>
                </div>

                <div class="signature-card">
                    <div class="signature-title">
                        Final verification
                    </div>

                    <div class="signature-field">
                        <span class="signature-label">
                            Checked by
                        </span>

                        <div class="signature-line"></div>
                    </div>

                    <div class="signature-field">
                        <span class="signature-label">
                            Signature
                        </span>

                        <div class="signature-line"></div>
                    </div>

                    <div class="signature-field">
                        <span class="signature-label">
                            Date and time
                        </span>

                        <div class="signature-line"></div>
                    </div>
                </div>
            </section>

            <footer class="footer-grid">
                <div>
                    <div class="footer-title">
                        Packing verification
                    </div>

                    <div class="footer-text">
                        Confirm that every item, quantity, size, colour and
                        product variant matches this packing slip before the
                        package is sealed and dispatched.
                    </div>
                </div>

                <div class="barcode-box">
                    <div class="barcode"></div>

                    <div class="barcode-number">
                        {{ $orderNumber }}
                    </div>

                    <div class="barcode-label">
                        Order reference
                    </div>
                </div>
            </footer>

            <div class="print-note">
                Packing slip generated on
                {{ now()->format('d M Y \a\t h:i A') }}
            </div>
        </section>
    </main>
</body>
</html>