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

$qrPayload = implode("\n", [
'Invoice',
'Order: '.$orderNumber,
'Customer: '.($order->billing_name ?: $order->user?->name ?: 'Guest'),
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

$invoiceNumber = 'INV-' . str_pad(
(string) $order->id,
6,
'0',
STR_PAD_LEFT
);

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

$paymentMethod = $order->payment_method
? ucwords(
str_replace(
['_', '-'],
' ',
$order->payment_method
)
)
: 'Not specified';

$paymentStatus = ucwords(
str_replace(
['_', '-'],
' ',
$order->payment_status ?: 'pending'
)
);

$orderStatus = ucwords(
str_replace(
['_', '-'],
' ',
$order->order_status ?: 'pending'
)
);

$paymentReference = $order->payment_reference
?? $order->transaction_id
?? null;

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
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Invoice {{ $invoiceNumber }}
    </title>

    <style>
        @page {
            margin: 25px 30px 45px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            margin-bottom: 22px;
            border-bottom: 2px solid #111827;
        }

        .header-table td {
            padding-bottom: 18px;
            vertical-align: top;
        }

        .brand-box {
            display: inline-block;
            padding: 12px 18px;
            color: #ffffff;
            background: #111827;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
        }

        .company-name {
            margin: 10px 0 3px;
            color: #111827;
            font-size: 15px;
            font-weight: bold;
        }

        .company-details {
            color: #6b7280;
            font-size: 8.5px;
            line-height: 1.6;
        }

        .invoice-heading-cell {
            text-align: right;
        }

        .invoice-heading {
            margin: 0;
            color: #111827;
            font-size: 31px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .invoice-number {
            margin-top: 7px;
            color: #4f46e5;
            font-size: 12px;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            margin-top: 9px;
            margin-left: 4px;
            padding: 4px 7px;
            border-radius: 10px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .badge-order {
            color: #374151;
            background: #f3f4f6;
        }

        .badge-payment {
            color: #166534;
            background: #dcfce7;
        }

        .meta-table {
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }

        .meta-table td {
            width: 25%;
            padding: 10px;
            border-right: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .meta-table td:last-child {
            border-right: 0;
        }

        .meta-label {
            display: block;
            margin-bottom: 4px;
            color: #6b7280;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .meta-value {
            color: #111827;
            font-size: 9px;
            font-weight: bold;
        }

        .address-table {
            margin-bottom: 22px;
        }

        .address-table td {
            width: 50%;
            vertical-align: top;
        }

        .address-left {
            padding-right: 8px;
        }

        .address-right {
            padding-left: 8px;
        }

        .address-card {
            min-height: 115px;
            padding: 13px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
        }

        .address-title {
            margin-bottom: 8px;
            color: #4f46e5;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .address-name {
            margin-bottom: 5px;
            color: #111827;
            font-size: 10px;
            font-weight: bold;
        }

        .address-line {
            color: #6b7280;
            font-size: 8.5px;
            line-height: 1.55;
        }

        .section-heading-table {
            margin-bottom: 8px;
        }

        .section-heading {
            color: #111827;
            font-size: 12px;
            font-weight: bold;
        }

        .section-subtext {
            color: #6b7280;
            font-size: 8px;
            text-align: right;
        }

        .items-table {
            margin-bottom: 20px;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .items-table th {
            padding: 8px 7px;
            color: #ffffff;
            background: #111827;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 10px 7px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .items-table .right {
            text-align: right;
            white-space: nowrap;
        }

        .product-table {
            width: auto;
        }

        .product-table td {
            padding: 0;
            border: 0;
        }

        .product-image-cell {
            width: 48px;
            padding-right: 9px !important;
        }

        .product-image {
            width: 42px;
            height: 46px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .product-placeholder {
            width: 42px;
            height: 46px;
            color: #9ca3af;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 7px;
            line-height: 46px;
            text-align: center;
        }

        .product-name {
            margin-bottom: 3px;
            color: #111827;
            font-size: 9px;
            font-weight: bold;
        }

        .product-option {
            display: inline-block;
            margin: 2px 3px 0 0;
            padding: 2px 4px;
            color: #4b5563;
            background: #f3f4f6;
            border-radius: 3px;
            font-size: 6.5px;
        }

        .sku {
            color: #6b7280;
            font-size: 7.5px;
        }

        .summary-table {
            page-break-inside: avoid;
            margin-top: 8px;
        }

        .summary-table>tbody>tr>td {
            vertical-align: top;
        }

        .payment-cell {
            width: 56%;
            padding-right: 14px;
        }

        .totals-cell {
            width: 44%;
            padding-left: 14px;
        }

        .summary-card {
            padding: 13px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
        }

        .summary-title {
            margin-bottom: 8px;
            color: #111827;
            font-size: 10px;
            font-weight: bold;
        }

        .detail-table td {
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8px;
        }

        .detail-table tr:last-child td {
            border-bottom: 0;
        }

        .detail-label {
            color: #6b7280;
        }

        .detail-value {
            color: #111827;
            font-weight: bold;
            text-align: right;
        }

        .totals-table td {
            padding: 5px 0;
            font-size: 8.5px;
        }

        .totals-label {
            color: #6b7280;
        }

        .totals-value {
            color: #111827;
            font-weight: bold;
            text-align: right;
        }

        .discount-value {
            color: #15803d;
        }

        .grand-total-row td {
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            color: #111827;
            font-size: 12px;
            font-weight: bold;
        }

        .currency-code {
            color: #6b7280;
            font-size: 7px;
            font-weight: normal;
        }

        .notes-box {
            page-break-inside: avoid;
            margin-top: 18px;
            padding: 11px;
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 5px;
        }

        .notes-title {
            margin-bottom: 4px;
            font-weight: bold;
        }

        .notes-text {
            font-size: 8px;
            white-space: pre-line;
        }

        .footer-table {
            page-break-inside: avoid;
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .footer-table td {
            vertical-align: bottom;
        }

        .thank-you-title {
            margin-bottom: 4px;
            color: #111827;
            font-size: 12px;
            font-weight: bold;
        }

        .thank-you-text {
            width: 78%;
            color: #6b7280;
            font-size: 8px;
            line-height: 1.55;
        }

        .reference-box {
            padding: 9px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            text-align: center;
        }


        .reference-number {
            color: #111827;
            font-size: 8px;
            font-weight: bold;
        }

        .reference-label {
            color: #6b7280;
            font-size: 6.5px;
        }

        .generated-text {
            margin-top: 18px;
            color: #9ca3af;
            font-size: 6.5px;
            text-align: center;
        }

        .empty-row {
            padding: 25px !important;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td width="58%">
                <div class="brand-box">
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
                class="invoice-heading-cell">
                <div class="invoice-heading">
                    Invoice
                </div>

                <div class="invoice-number">
                    {{ $invoiceNumber }}
                </div>

                <div>
                    <span class="badge badge-order">
                        {{ $orderStatus }}
                    </span>

                    <span class="badge badge-payment">
                        Payment: {{ $paymentStatus }}
                    </span>
                </div>
            </td>
        </tr>
    </table>
    <table width="100%" style="margin-top:15px;">
        <tr>

            <td width="70%">

                <img
                    src="data:image/png;base64,{{ $barcode }}"
                    style="width:100%;height:60px;">

                <div
                    style="
font-size:11px;
text-align:center;
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
                    Invoice date
                </span>

                <span class="meta-value">
                    {{ $order->created_at?->format('d M Y') }}
                </span>
            </td>

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
                    Payment method
                </span>

                <span class="meta-value">
                    {{ $paymentMethod }}
                </span>
            </td>

            <td>
                <span class="meta-label">
                    Currency
                </span>

                <span class="meta-value">
                    {{ $currencyCode }}
                </span>
            </td>
        </tr>
    </table>

    <table class="address-table">
        <tr>
            <td class="address-left">
                <div class="address-card">
                    <div class="address-title">
                        Bill to
                    </div>

                    <div class="address-name">
                        {{ $order->billing_name ?: $customerName }}
                    </div>

                    @forelse ($billingAddress as $line)
                    <div class="address-line">
                        {{ $line }}
                    </div>
                    @empty
                    <div class="address-line">
                        No billing address provided.
                    </div>
                    @endforelse

                    @if ($customerEmail)
                    <div class="address-line">
                        {{ $customerEmail }}
                    </div>
                    @endif

                    @if ($customerPhone)
                    <div class="address-line">
                        {{ $customerPhone }}
                    </div>
                    @endif
                </div>
            </td>

            <td class="address-right">
                <div class="address-card">
                    <div class="address-title">
                        Ship to
                    </div>

                    <div class="address-name">
                        {{ $order->shipping_name ?: $customerName }}
                    </div>

                    @forelse ($shippingAddress as $line)
                    <div class="address-line">
                        {{ $line }}
                    </div>
                    @empty
                    <div class="address-line">
                        No shipping address provided.
                    </div>
                    @endforelse

                    @if ($order->shipping_email)
                    <div class="address-line">
                        {{ $order->shipping_email }}
                    </div>
                    @endif

                    @if ($order->shipping_phone)
                    <div class="address-line">
                        {{ $order->shipping_phone }}
                    </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="section-heading-table">
        <tr>
            <td class="section-heading">
                Order items
            </td>

            <td class="section-subtext">
                {{ $order->items->sum('quantity') }} unit(s)
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="47%">Product</th>
                <th width="13%">SKU</th>
                <th width="14%" class="right">Price</th>
                <th width="10%" class="right">Qty</th>
                <th width="16%" class="right">Total</th>
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

            $lineTotal = $item->subtotal !== null
            ? (float) $item->subtotal
            : (float) $item->price
            * (int) $item->quantity;

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
            @endphp

            <tr>
                <td>
                    <table class="product-table">
                        <tr>
                            <td class="product-image-cell">
                                @if ($productImage)
                                <img
                                    src="{{ $productImage }}"
                                    alt="{{ $productName }}"
                                    class="product-image">
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

                                @foreach ($displayOptions as $option)
                                <span class="product-option">
                                    {{ $option['name'] ?? 'Option' }}:
                                    {{ $option['value'] ?? '' }}
                                </span>
                                @endforeach
                            </td>
                        </tr>
                    </table>
                </td>

                <td>
                    <span class="sku">
                        {{ $item->sku
                                ?: $item->product?->sku
                                ?: 'N/A' }}
                    </span>
                </td>

                <td class="right">
                    {{ $money($item->price) }}
                </td>

                <td class="right">
                    {{ $item->quantity }}
                </td>

                <td class="right">
                    <strong>
                        {{ $money($lineTotal) }}
                    </strong>
                </td>
            </tr>
            @empty
            <tr>
                <td
                    colspan="5"
                    class="empty-row">
                    No order items were found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="payment-cell">
                <div class="summary-card">
                    <div class="summary-title">
                        Payment and delivery details
                    </div>

                    <table class="detail-table">
                        <tr>
                            <td class="detail-label">
                                Payment method
                            </td>

                            <td class="detail-value">
                                {{ $paymentMethod }}
                            </td>
                        </tr>

                        <tr>
                            <td class="detail-label">
                                Payment status
                            </td>

                            <td class="detail-value">
                                {{ $paymentStatus }}
                            </td>
                        </tr>

                        <tr>
                            <td class="detail-label">
                                Payment reference
                            </td>

                            <td class="detail-value">
                                {{ $paymentReference ?: 'N/A' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="detail-label">
                                Tracking number
                            </td>

                            <td class="detail-value">
                                {{ $order->tracking_number
                                    ?: 'Not available' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <td class="totals-cell">
                <div class="summary-card">
                    <table class="totals-table">
                        <tr>
                            <td class="totals-label">
                                Subtotal
                            </td>

                            <td class="totals-value">
                                {{ $money($subtotal) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="totals-label">
                                Discount
                            </td>

                            <td class="totals-value discount-value">
                                @if ($discount > 0)
                                -
                                @endif

                                {{ $money($discount) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="totals-label">
                                Shipping
                            </td>

                            <td class="totals-value">
                                {{ $money($shipping) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="totals-label">
                                Tax
                            </td>

                            <td class="totals-value">
                                {{ $money($tax) }}
                            </td>
                        </tr>

                        <tr class="grand-total-row">
                            <td>
                                Total
                            </td>

                            <td class="totals-value">
                                <span class="currency-code">
                                    {{ $currencyCode }}
                                </span>

                                {{ $money($total) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @if (!empty($order->admin_notes))
    <div class="notes-box">
        <div class="notes-title">
            Order notes
        </div>

        <div class="notes-text">
            {{ $order->admin_notes }}
        </div>
    </div>
    @endif

    <table class="footer-table">
        <tr>
            <td width="73%">
                <div class="thank-you-title">
                    Thank you for your order.
                </div>

                <div class="thank-you-text">
                    Please keep this invoice for your records. For questions
                    about this order, contact our customer support team and
                    mention order number {{ $orderNumber }}.
                </div>
            </td>

            <td width="27%">
                <div class="reference-box">
                    <img
                        src="data:image/png;base64,{{ $barcode }}"
                        style="width:100%;height:40px;">

                    <div class="reference-number">
                        {{ $orderNumber }}
                    </div>

                    <div class="reference-label">
                        Order reference
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="generated-text">
        Invoice generated on
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