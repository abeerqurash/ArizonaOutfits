@php
use Picqer\Barcode\BarcodeGeneratorPNG;

$orderNumber = $order->order_number
    ?: 'ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

$generator = new BarcodeGeneratorPNG();

$barcode = base64_encode(
    $generator->getBarcode(
        $orderNumber,
        $generator::TYPE_CODE_128,
        2,
        60
    )
);

$customerName = $order->shipping_name
    ?: $order->billing_name
    ?: $order->user?->name
    ?: 'Guest';

$qrPayload = implode("\n", [
    'Packing Slip',
    'Order: '.$orderNumber,
    'Customer: '.$customerName,
]);

$qrResult = new \Endroid\QrCode\Builder\Builder(
    writer: new \Endroid\QrCode\Writer\PngWriter(),
    writerOptions: [],
    validateResult: false,
    data: $qrPayload,
    encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
    errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Medium,
    size: 180,
    margin: 5
);

$qr = base64_encode(
    $qrResult->build()->getString()
);
@endphp
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

    $resolveImagePath = function ($item) {
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

        $imagePath = ltrim($imagePath, '/');

        $possiblePaths = [
            public_path($imagePath),
            public_path('storage/' . $imagePath),
        ];

        if (str_starts_with($imagePath, 'storage/')) {
            array_unshift(
                $possiblePaths,
                public_path($imagePath)
            );
        }

        foreach ($possiblePaths as $possiblePath) {
            if (is_file($possiblePath)) {
                return $possiblePath;
            }
        }

        return null;
    };

    $getOptions = function ($item) {
        $options = [];

        if (
            !empty($item->display_options)
            && is_iterable($item->display_options)
        ) {
            foreach ($item->display_options as $option) {
                $options[] = [
                    'name' => $option['name'] ?? 'Option',
                    'value' => $option['value'] ?? '',
                ];
            }
        }

        if (
            empty($options)
            && is_array($item->options ?? null)
        ) {
            foreach ($item->options as $name => $value) {
                $options[] = [
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

    <title>
        Packing Slip {{ $orderNumber }}
    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 24px 30px 45px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.45;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            margin-bottom: 18px;
            border-bottom: 2px solid #111827;
        }

        .header-table td {
            padding-bottom: 16px;
            vertical-align: top;
        }

        .brand-mark {
            display: inline-block;
            padding: 11px 17px;
            color: #ffffff;
            background: #111827;
            border-radius: 5px;
            font-size: 17px;
            font-weight: bold;
        }

        .company-name {
            margin-top: 8px;
            color: #111827;
            font-size: 13px;
            font-weight: bold;
        }

        .company-details {
            margin-top: 3px;
            color: #6b7280;
            font-size: 7.5px;
            line-height: 1.55;
        }

        .title-cell {
            text-align: right;
        }

        .document-title {
            color: #111827;
            font-size: 27px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .document-number {
            margin-top: 6px;
            color: #4f46e5;
            font-size: 11px;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 7px;
            color: #166534;
            background: #dcfce7;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
        }

        .meta-table {
            margin-bottom: 17px;
            border: 1px solid #e5e7eb;
        }

        .meta-table td {
            width: 25%;
            padding: 9px;
            border-right: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .meta-table td:last-child {
            border-right: 0;
        }

        .meta-label {
            display: block;
            margin-bottom: 3px;
            color: #6b7280;
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .meta-value {
            color: #111827;
            font-size: 8.5px;
            font-weight: bold;
        }

        .details-table {
            margin-bottom: 18px;
        }

        .details-table td {
            vertical-align: top;
        }

        .shipping-cell {
            width: 60%;
            padding-right: 8px;
        }

        .warehouse-cell {
            width: 40%;
            padding-left: 8px;
        }

        .detail-card {
            min-height: 132px;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
        }

        .card-title {
            margin-bottom: 7px;
            color: #4f46e5;
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .customer-name {
            margin-bottom: 4px;
            color: #111827;
            font-size: 10px;
            font-weight: bold;
        }

        .address-line,
        .contact-line {
            color: #6b7280;
            font-size: 8px;
            line-height: 1.5;
        }

        .contact-group {
            margin-top: 5px;
        }

        .warehouse-row {
            margin-bottom: 16px;
        }

        .warehouse-row:last-child {
            margin-bottom: 0;
        }

        .warehouse-label {
            display: inline-block;
            width: 90px;
            color: #6b7280;
            font-size: 7px;
            font-weight: bold;
        }

        .warehouse-line {
            display: inline-block;
            width: 145px;
            height: 14px;
            border-bottom: 1px solid #9ca3af;
        }

        .section-heading-table {
            margin-bottom: 7px;
        }

        .section-heading {
            color: #111827;
            font-size: 11px;
            font-weight: bold;
        }

        .section-summary {
            color: #6b7280;
            font-size: 7px;
            text-align: right;
        }

        .items-table {
            margin-bottom: 18px;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .items-table th {
            padding: 7px 6px;
            color: #ffffff;
            background: #111827;
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .packed-column {
            width: 8%;
            text-align: center !important;
        }

        .product-column {
            width: 40%;
        }

        .sku-column {
            width: 15%;
        }

        .variant-column {
            width: 25%;
        }

        .quantity-column {
            width: 12%;
            text-align: center !important;
        }

        .checkbox {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1.5px solid #9ca3af;
            border-radius: 2px;
        }

        .product-table {
            width: auto;
        }

        .product-table td {
            padding: 0;
            border: 0;
        }

        .image-cell {
            width: 48px;
            padding-right: 8px !important;
        }

        .product-image,
        .product-placeholder {
            width: 42px;
            height: 48px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .product-image {
            object-fit: cover;
        }

        .product-placeholder {
            color: #9ca3af;
            background: #f3f4f6;
            font-size: 6px;
            line-height: 48px;
            text-align: center;
        }

        .product-name {
            margin-bottom: 3px;
            color: #111827;
            font-size: 8.5px;
            font-weight: bold;
        }

        .product-id,
        .sku-text {
            color: #6b7280;
            font-size: 7px;
        }

        .sku-text {
            color: #111827;
            font-weight: bold;
        }

        .option-badge {
            display: inline-block;
            margin: 1px 2px 1px 0;
            padding: 2px 4px;
            color: #4b5563;
            background: #f3f4f6;
            border-radius: 3px;
            font-size: 6px;
        }

        .quantity-value {
            display: inline-block;
            min-width: 26px;
            padding: 4px 6px;
            color: #111827;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }

        .empty-row {
            padding: 20px !important;
            color: #6b7280;
            text-align: center;
        }

        .bottom-table {
            page-break-inside: avoid;
            margin-top: 10px;
        }

        .bottom-table td {
            vertical-align: top;
        }

        .notes-cell {
            width: 60%;
            padding-right: 8px;
        }

        .verification-cell {
            width: 40%;
            padding-left: 8px;
        }

        .bottom-card {
            min-height: 130px;
            padding: 11px;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
        }

        .bottom-title {
            margin-bottom: 10px;
            color: #111827;
            font-size: 9px;
            font-weight: bold;
        }

        .notes-area {
            min-height: 95px;
            color: #4b5563;
            font-size: 7.5px;
            line-height: 1.7;
            white-space: pre-line;
            background:
                repeating-linear-gradient(
                    to bottom,
                    transparent,
                    transparent 22px,
                    #d1d5db 23px
                );
        }

        .signature-field {
            margin-bottom: 17px;
        }

        .signature-field:last-child {
            margin-bottom: 0;
        }

        .signature-label {
            display: block;
            margin-bottom: 11px;
            color: #6b7280;
            font-size: 6.5px;
            font-weight: bold;
        }

        .signature-line {
            height: 10px;
            border-bottom: 1px solid #9ca3af;
        }

        .footer-table {
            page-break-inside: avoid;
            margin-top: 20px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
        }

        .footer-table td {
            vertical-align: bottom;
        }

        .footer-title {
            margin-bottom: 4px;
            color: #111827;
            font-size: 10px;
            font-weight: bold;
        }

        .footer-text {
            width: 82%;
            color: #6b7280;
            font-size: 7px;
            line-height: 1.5;
        }

        .barcode-box {
            padding: 9px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            text-align: center;
        }

        

        .barcode-number {
            color: #111827;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.8px;
        }

        .barcode-label {
            margin-top: 2px;
            color: #6b7280;
            font-size: 5.5px;
        }

        .generated-text {
            margin-top: 14px;
            color: #9ca3af;
            font-size: 5.8px;
            text-align: center;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td width="58%">
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
            </td>

            <td
                width="42%"
                class="title-cell"
            >
                <div class="document-title">
                    Packing Slip
                </div>

                <div class="document-number">
                    {{ $orderNumber }}
                </div>

                <div class="status-badge">
                    {{ $orderStatus }}
                </div>
            </td>
        </tr>
    </table>
<table width="100%" style="margin-top:15px;margin-bottom:20px;">
<tr>

<td width="70%">

<img
src="data:image/png;base64,{{ $barcode }}"
style="width:100%;height:60px;">

<div
style="
text-align:center;
font-size:11px;
font-weight:bold;
margin-top:5px;
">
{{ $orderNumber }}
</div>

</td>

<td width="30%" align="right">

<img
src="data:image/png;base64,{{ $qr }}"
style="width:90px;height:90px;">

</td>

</tr>
</table>
    <table class="meta-table">
        <tr>
            <td>
                <span class="meta-label">
                    Order number
                </span>

                <span class="meta-value">
                    {{ $orderNumber }}
                </span>
            </td>

            <td>
                <span class="meta-label">
                    Order date
                </span>

                <span class="meta-value">
                    {{ $order->created_at?->format('d M Y') ?? 'N/A' }}
                </span>
            </td>

            <td>
                <span class="meta-label">
                    Shipping method
                </span>

                <span class="meta-value">
                    {{ $shippingMethod }}
                </span>
            </td>

            <td>
                <span class="meta-label">
                    Tracking number
                </span>

                <span class="meta-value">
                    {{ $trackingNumber ?: 'Not assigned' }}
                </span>
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td class="shipping-cell">
                <div class="detail-card">
                    <div class="card-title">
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

                    <div class="contact-group">
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
            </td>

            <td class="warehouse-cell">
                <div class="detail-card">
                    <div class="card-title">
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
            </td>
        </tr>
    </table>

    <table class="section-heading-table">
        <tr>
            <td class="section-heading">
                Items to pack
            </td>

            <td class="section-summary">
                {{ $order->items->count() }} product line(s) ·
                {{ $totalQuantity }} unit(s)
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="packed-column">
                    Packed
                </th>

                <th class="product-column">
                    Product
                </th>

                <th class="sku-column">
                    SKU
                </th>

                <th class="variant-column">
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

                    $productImage = $resolveImagePath($item);
                    $options = $getOptions($item);

                    $sku = $item->sku
                        ?: $item->variant?->sku
                        ?: $item->product?->sku
                        ?: 'N/A';
                @endphp

                <tr>
                    <td class="packed-column">
                        <span class="checkbox"></span>
                    </td>

                    <td>
                        <table class="product-table">
                            <tr>
                                <td class="image-cell">
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
                                </td>

                                <td>
                                    <div class="product-name">
                                        {{ $productName }}
                                    </div>

                                    <div class="product-id">
                                        Product ID:
                                        {{ $item->product_id ?: 'N/A' }}
                                    </div>
                                </td>
                            </tr>
                        </table>
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
                        class="empty-row"
                    >
                        No items were found for this order.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="bottom-table">
        <tr>
            <td class="notes-cell">
                <div class="bottom-card">
                    <div class="bottom-title">
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
            </td>

            <td class="verification-cell">
                <div class="bottom-card">
                    <div class="bottom-title">
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
            </td>
        </tr>
    </table>

    <table class="footer-table">
        <tr>
            <td width="72%">
                <div class="footer-title">
                    Packing verification
                </div>

                <div class="footer-text">
                    Confirm that every item, quantity, size, colour and
                    product variant matches this packing slip before the
                    package is sealed and dispatched.
                </div>
            </td>

            <td width="28%">
                <div class="barcode-box">
                    <img
src="data:image/png;base64,{{ $barcode }}"
style="width:100%;height:40px;">

                    <div class="barcode-number">
                        {{ $orderNumber }}
                    </div>

                    <div class="barcode-label">
                        Order reference
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="generated-text">
        Packing slip generated on
        {{ now()->format('d M Y \a\t h:i A') }}
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font(
                "DejaVu Sans",
                "normal"
            );

            $pdf->page_text(
                500,
                815,
                "Page {PAGE_NUM} of {PAGE_COUNT}",
                $font,
                7,
                [0.42, 0.45, 0.50]
            );
        }
    </script>
</body>
</html>