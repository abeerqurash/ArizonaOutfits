@php
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
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Shipping Label {{ $orderNumber }}</title>
<style>
    @page { size: 4in 6in; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; color: #000; font-family: DejaVu Sans, sans-serif; font-size: 8px; }
    .label { width: 4in; height: 6in; border: 2px solid #000; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .header td { height: 56px; padding: 9px 11px; border-bottom: 2px solid #000; vertical-align: middle; }
    .brand { font-size: 18px; line-height: 15px; font-weight: bold; }
    .brand-sub { margin-top: 5px; font-size: 6px; font-weight: normal; }
    .service { width: 100px; border: 2px solid #000; padding: 6px; text-align: center; font-size: 6px; font-weight: bold; text-transform: uppercase; }
    .service strong { display: block; margin-top: 3px; font-size: 9px; }
    .section { padding: 9px 11px; border-bottom: 2px solid #000; }
    .eyebrow { margin-bottom: 5px; font-size: 6px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
    .from { height: 55px; }
    .from-name { font-size: 9px; font-weight: bold; }
    .from-sub { margin-top: 3px; font-size: 6px; line-height: 9px; }
    .ship { height: 117px; }
    .recipient { font-size: 16px; line-height: 17px; font-weight: bold; text-transform: uppercase; }
    .address { margin-top: 6px; font-size: 10px; line-height: 13px; font-weight: bold; text-transform: uppercase; }
    .contact { margin-top: 6px; font-size: 6px; }
    .tracking { height: 91px; text-align: center; }
    .tracking-number { margin: 2px 0 5px; font-size: 11px; font-weight: bold; letter-spacing: 1px; }
    .barcode { width: 100%; height: 38px; }
    .barcode-text { margin-top: 3px; font-family: DejaVu Sans Mono, monospace; font-size: 6px; font-weight: bold; letter-spacing: 1px; }
    .details { height: 113px; border-bottom: 2px solid #000; }
    .details td { vertical-align: middle; }
    .info { width: 68%; padding: 8px 10px; border-right: 2px solid #000; }
    .qr-cell { width: 32%; text-align: center; padding: 7px; }
    .detail { margin-bottom: 4px; font-size: 6.5px; line-height: 8px; }
    .detail b { display: inline-block; width: 76px; text-transform: uppercase; }
    .qr { width: 72px; height: 72px; }
    .scan { margin-top: 3px; font-size: 6px; font-weight: bold; text-transform: uppercase; }
    .footer td { height: 35px; padding: 8px 10px; vertical-align: middle; font-size: 7px; font-weight: bold; }
    .right { text-align: right; }
</style>
</head>
<body>
<div class="label">
    <table class="header">
        <tr>
            <td>
                <div class="brand">ARIZONA<br>OUTFITS</div>
                <div class="brand-sub">Premium clothing & lifestyle products</div>
            </td>
            <td style="width:112px;text-align:right;">
                <div class="service">Service<strong>{{ $shippingMethod }}</strong></div>
            </td>
        </tr>
    </table>

    <div class="section from">
        <div class="eyebrow">From</div>
        <div class="from-name">{{ $senderName }}</div>
        <div class="from-sub">{{ $senderLine1 }}<br>{{ $senderEmail }}</div>
    </div>

    <div class="section ship">
        <div class="eyebrow">Ship to</div>
        <div class="recipient">{{ $customerName }}</div>
        <div class="address">
            @if($address1){{ $address1 }}<br>@endif
            @if($address2){{ $address2 }}<br>@endif
            @if($city || $state){{ $city }}{{ $city && $state ? ', ' : '' }}{{ $state }}<br>@endif
            @if($postcode || $country){{ $postcode }}{{ $postcode && $country ? ' · ' : '' }}{{ $country }}@endif
        </div>
        @if($customerPhone || $customerEmail)
        <div class="contact">
            @if($customerPhone)Phone: {{ $customerPhone }}@endif
            @if($customerPhone && $customerEmail) &nbsp; | &nbsp; @endif
            @if($customerEmail)Email: {{ $customerEmail }}@endif
        </div>
        @endif
    </div>

    <div class="section tracking">
        <div class="eyebrow">Arizona tracking</div>
        <div class="tracking-number">{{ $trackingNumber }}</div>
        <img class="barcode" src="data:image/png;base64,{{ $barcodeBase64 }}">
        <div class="barcode-text">{{ $trackingNumber }}</div>
    </div>

    <table class="details">
        <tr>
            <td class="info">
                <div class="detail"><b>Courier provider</b>{{ $courierProvider ?: 'Not assigned' }}</div>
                <div class="detail"><b>Courier tracking</b>{{ $courierTracking ?: 'Not assigned' }}</div>
                <div class="detail"><b>Order</b>{{ $orderNumber }}</div>
                <div class="detail"><b>Status</b>{{ $orderStatus }}</div>
                <div class="detail"><b>Weight</b>{{ $packageWeight !== null ? number_format((float)$packageWeight, 2).' '.$weightUnit : 'Not specified' }}</div>
                <div class="detail"><b>Contents</b>{{ $totalQuantity }} unit(s)</div>
            </td>
            <td class="qr-cell">
                <img class="qr" src="data:image/png;base64,{{ $qrBase64 }}">
                <div class="scan">Scan order</div>
            </td>
        </tr>
    </table>

    <table class="footer">
        <tr>
            <td>{{ $orderNumber }}</td>
            <td class="right">Package 1 of 1</td>
        </tr>
    </table>
</div>
</body>
</html>
