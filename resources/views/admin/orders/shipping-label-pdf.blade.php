@php
$orderNumber = $order->order_number
?: 'ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

$recipientName = $order->shipping_name
?: $order->billing_name
?: $order->user?->name
?: 'Guest Customer';

$recipientEmail = $order->shipping_email
?: $order->billing_email
?: $order->user?->email
?: null;

$recipientPhone = $order->shipping_phone
?: $order->billing_phone
?: null;

$addressLineOne = $order->shipping_address
?? $order->shipping_address_line_1
?? $order->shipping_address1
?? null;

$addressLineTwo = $order->shipping_address_line_2
?? $order->shipping_address2
?? null;

$shippingCity = $order->shipping_city ?? null;
$shippingState = $order->shipping_state ?? null;

$shippingPostcode = $order->shipping_postcode
?? $order->shipping_zip
?? null;

$shippingCountry = $order->shipping_country ?? null;

$cityStateLine = collect([
$shippingCity,
$shippingState,
])->filter()->implode(', ');

$postcodeCountryLine = collect([
$shippingPostcode,
$shippingCountry,
])->filter()->implode(' ');

$trackingNumber = $order->tracking_number
?? $order->shipment_tracking_number
?? null;

$barcodeValue = strtoupper(
preg_replace(
'/[^A-Za-z0-9\-]/',
'',
$trackingNumber ?: $orderNumber
)
);

$courier = $order->courier
?? $order->shipping_carrier
?? $order->carrier
?? 'Not Assigned';

$service = $order->shipping_service
?? $order->delivery_method
?? $order->shipping_method
?? 'Standard Delivery';

$packageWeight = $order->package_weight
?? $order->shipping_weight
?? $order->weight
?? null;

$weightUnit = $order->weight_unit ?? 'kg';

$packageNumber = (int) ($order->package_number ?? 1);
$totalPackages = (int) ($order->total_packages ?? 1);

$totalQuantity = $order->items->sum(function ($item) {
return (int) ($item->quantity ?? 0);
});

$orderStatus = ucwords(
str_replace(
['_', '-'],
' ',
$order->order_status
?? $order->status
?? 'pending'
)
);

/*
|--------------------------------------------------------------------------
| Sender information
|--------------------------------------------------------------------------
|
| Replace these details with your real warehouse information.
|
*/

$senderCompany = config('app.name', 'Arizona Outfits');
$senderAddressOne = 'Main Fulfilment Warehouse';
$senderAddressTwo = 'Warehouse Address';
$senderCity = 'Karachi';
$senderCountry = 'Pakistan';
$senderEmail = 'support@arizonaoutfits.com';
$senderPhone = null;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Shipping Label {{ $orderNumber }}
    </title>

    <style>
        @page {
            size: 4in 6in;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 4in;
            height: 6in;
            margin: 0;
            padding: 0;
        }

        body {
            color: #000000;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.25;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .label {
            position: relative;
            width: 4in;
            height: 6in;
            overflow: hidden;
            border: 2px solid #000000;
        }

        .section {
            width: 100%;
            border-bottom: 2px solid #000000;
        }

        .header-section {
            height: 0.70in;
            padding: 8px 10px;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-cell {
            width: 65%;
        }

        .service-cell {
            width: 35%;
            text-align: right;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .company-subtitle {
            margin-top: 3px;
            font-size: 6.5px;
            font-weight: bold;
        }

        .service-box {
            display: inline-block;
            width: 105px;
            padding: 5px 4px;
            border: 1.5px solid #000000;
            text-align: center;
        }

        .service-label {
            display: block;
            margin-bottom: 2px;
            font-size: 5.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .service-value {
            display: block;
            font-size: 8px;
            font-weight: bold;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .sender-section {
            height: 0.78in;
            padding: 7px 10px;
        }

        .section-label {
            margin-bottom: 4px;
            font-size: 6px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .sender-name {
            margin-bottom: 2px;
            font-size: 8.5px;
            font-weight: bold;
        }

        .sender-address {
            font-size: 6.8px;
            line-height: 1.35;
        }

        .recipient-section {
            height: 1.48in;
            padding: 9px 12px;
        }

        .recipient-name {
            margin-bottom: 4px;
            font-size: 15px;
            font-weight: bold;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .recipient-address {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.3;
            text-transform: uppercase;
        }

        .recipient-contact {
            margin-top: 5px;
            font-size: 6.5px;
            font-weight: bold;
        }

        .tracking-section {
            height: 1.42in;
            padding: 7px 10px;
        }

        .tracking-title {
            margin-bottom: 4px;
            font-size: 6px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-align: center;
            text-transform: uppercase;
        }

        .tracking-number {
            margin-bottom: 6px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: center;
            word-wrap: break-word;
        }

        /*
        |--------------------------------------------------------------------------
        | Visual barcode placeholder
        |--------------------------------------------------------------------------
        |
        | This is printable but not a true encoded barcode. A real Code 128
        | barcode can be added in the next integration step.
        |
        */

        .barcode-wrapper {
            padding: 0 5px;
        }



        .barcode-caption {
            margin-top: 4px;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1px;
            text-align: center;
        }

        .shipment-section {
            height: 1.10in;
        }

        .shipment-table {
            height: 100%;
        }

        .shipment-info-cell {
            width: 69%;
            padding: 7px 9px;
            border-right: 2px solid #000000;
            vertical-align: top;
        }

        .qr-cell {
            width: 31%;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .shipment-row {
            margin-bottom: 3px;
        }

        .shipment-row:last-child {
            margin-bottom: 0;
        }

        .shipment-label {
            display: inline-block;
            width: 65px;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .shipment-value {
            display: inline-block;
            width: 120px;
            font-size: 6.5px;
            font-weight: bold;
            vertical-align: top;
            word-wrap: break-word;
        }

        /*
        |--------------------------------------------------------------------------
        | Visual QR placeholder
        |--------------------------------------------------------------------------
        |
        | This gives the label a QR-style area. It is not yet scannable.
        |
        */

        .qr-code {
            width: 62px;
            height: 62px;
            margin: 0 auto;
            padding: 3px;
            border: 1.5px solid #000000;
        }

        .qr-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        .qr-table td {
            width: 12.5%;
            height: 12.5%;
            padding: 0;
        }

        .qr-black {
            background: #000000;
        }

        .qr-white {
            background: #ffffff;
        }

        .qr-caption {
            margin-top: 3px;
            font-size: 5px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .footer-section {
            height: 0.52in;
            padding: 7px 10px;
            border-bottom: 0;
        }

        .footer-table td {
            vertical-align: middle;
        }

        .order-reference {
            width: 62%;
            font-size: 8px;
            font-weight: bold;
        }

        .package-reference {
            width: 38%;
            font-size: 7px;
            font-weight: bold;
            text-align: right;
        }

        .missing-address {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .real-barcode {
            display: block;
            width: 100%;
            height: 45px;
        }

        .real-qr-code {
            display: block;
            width: 60px;
            height: 60px;
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="label">
        <section class="section header-section">
            <table class="header-table">
                <tr>
                    <td class="company-cell">
                        <div class="company-name">
                            {{ $senderCompany }}
                        </div>

                        <div class="company-subtitle">
                            Premium clothing and lifestyle products
                        </div>
                    </td>

                    <td class="service-cell">
                        <div class="service-box">
                            <span class="service-label">
                                Service
                            </span>

                            <span class="service-value">
                                {{ $service }}
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </section>

        <section class="section sender-section">
            <div class="section-label">
                From
            </div>

            <div class="sender-name">
                {{ $senderCompany }}
            </div>

            <div class="sender-address">
                {{ $senderAddressOne }}<br>

                @if ($senderAddressTwo)
                {{ $senderAddressTwo }}<br>
                @endif

                {{ $senderCity }}, {{ $senderCountry }}<br>

                {{ $senderEmail }}

                @if ($senderPhone)
                · {{ $senderPhone }}
                @endif
            </div>
        </section>

        <section class="section recipient-section">
            <div class="section-label">
                Ship to
            </div>

            <div class="recipient-name">
                {{ $recipientName }}
            </div>

            <div class="recipient-address">
                @if ($addressLineOne)
                <div>{{ $addressLineOne }}</div>
                @endif

                @if ($addressLineTwo)
                <div>{{ $addressLineTwo }}</div>
                @endif

                @if ($cityStateLine)
                <div>{{ $cityStateLine }}</div>
                @endif

                @if ($postcodeCountryLine)
                <div>{{ $postcodeCountryLine }}</div>
                @endif

                @if (
                !$addressLineOne
                && !$addressLineTwo
                && !$cityStateLine
                && !$postcodeCountryLine
                )
                <div class="missing-address">
                    Shipping address not provided
                </div>
                @endif
            </div>

            @if ($recipientPhone || $recipientEmail)
            <div class="recipient-contact">
                @if ($recipientPhone)
                Phone: {{ $recipientPhone }}
                @endif

                @if ($recipientPhone && $recipientEmail)
                &nbsp; | &nbsp;
                @endif

                @if ($recipientEmail)
                Email: {{ $recipientEmail }}
                @endif
            </div>
            @endif
        </section>

        <section class="section tracking-section">
            <div class="tracking-title">
                Tracking number
            </div>

            <div class="tracking-number">
                {{ $trackingNumber ?: 'NOT ASSIGNED' }}
            </div>

            <div class="barcode-wrapper">
                <img
                    src="data:image/png;base64,{{ $barcodeBase64 }}"
                    alt="Tracking barcode"
                    class="real-barcode">
            </div>

            <div class="barcode-caption">
                {{ $barcodeValue }}
            </div>
        </section>

        <section class="section shipment-section">
            <table class="shipment-table">
                <tr>
                    <td class="shipment-info-cell">
                        <div class="shipment-row">
                            <span class="shipment-label">
                                Courier
                            </span>

                            <span class="shipment-value">
                                {{ $courier }}
                            </span>
                        </div>

                        <div class="shipment-row">
                            <span class="shipment-label">
                                Order
                            </span>

                            <span class="shipment-value">
                                {{ $orderNumber }}
                            </span>
                        </div>

                        <div class="shipment-row">
                            <span class="shipment-label">
                                Status
                            </span>

                            <span class="shipment-value">
                                {{ $orderStatus }}
                            </span>
                        </div>

                        <div class="shipment-row">
                            <span class="shipment-label">
                                Weight
                            </span>

                            <span class="shipment-value">
                                @if ($packageWeight !== null)
                                {{ number_format((float) $packageWeight, 2) }}
                                {{ $weightUnit }}
                                @else
                                Not specified
                                @endif
                            </span>
                        </div>

                        <div class="shipment-row">
                            <span class="shipment-label">
                                Contents
                            </span>

                            <span class="shipment-value">
                                {{ $totalQuantity }} unit(s)
                            </span>
                        </div>
                    </td>

                    <td class="qr-cell">
                        <div class="qr-code">
                            <img
                                src="data:image/png;base64,{{ $qrBase64 }}"
                                alt="Order QR code"
                                class="real-qr-code">
                        </div>

                        <div class="qr-caption">
                            Scan order
                        </div>
                    </td>
                </tr>
            </table>
        </section>

        <footer class="footer-section">
            <table class="footer-table">
                <tr>
                    <td class="order-reference">
                        {{ $orderNumber }}
                    </td>

                    <td class="package-reference">
                        Package {{ $packageNumber }}
                        of {{ $totalPackages }}
                    </td>
                </tr>
            </table>
        </footer>
    </div>
</body>

</html>