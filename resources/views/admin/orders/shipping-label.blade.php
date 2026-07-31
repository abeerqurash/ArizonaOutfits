@php
    $orderNumber = $order->order_number
        ?: 'ORD-' . str_pad(
            (string) $order->id,
            6,
            '0',
            STR_PAD_LEFT
        );

    $recipientName = $order->shipping_name
        ?: $order->billing_name
        ?: $order->user?->name
        ?: 'Guest customer';

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

    $cityStateLine = trim(
        collect([
            $shippingCity,
            $shippingState,
        ])->filter()->implode(', ')
    );

    $postcodeCountryLine = trim(
        collect([
            $shippingPostcode,
            $shippingCountry,
        ])->filter()->implode(' ')
    );

    $trackingNumber = $order->tracking_number
        ?? $order->shipment_tracking_number
        ?? null;

    $barcodeValue = $trackingNumber ?: $orderNumber;

    $courier = $order->courier
        ?? $order->shipping_carrier
        ?? $order->carrier
        ?? 'Courier not assigned';

    $service = $order->shipping_service
        ?? $order->delivery_method
        ?? $order->shipping_method
        ?? 'Standard delivery';

    $packageWeight = $order->package_weight
        ?? $order->shipping_weight
        ?? $order->weight
        ?? null;

    $weightUnit = $order->weight_unit ?? 'kg';

    $packageNumber = $order->package_number ?? 1;
    $totalPackages = $order->total_packages ?? 1;

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

    $barcodePattern = preg_replace(
        '/[^A-Za-z0-9]/',
        '',
        strtoupper($barcodeValue)
    );

    $barcodePattern = $barcodePattern ?: 'ORDER';

    $qrData = implode('|', [
        'ORDER:' . $orderNumber,
        'TRACKING:' . ($trackingNumber ?: 'NOT-ASSIGNED'),
        'CUSTOMER:' . $recipientName,
    ]);
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
        Shipping Label {{ $orderNumber }}
    </title>

    <style>
        :root {
            --black: #111111;
            --white: #ffffff;
            --border: #d1d5db;
            --muted: #6b7280;
            --background: #f3f4f6;
            --accent: #111827;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--black);
            background: var(--background);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.35;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        a {
            font: inherit;
        }

        .page {
            padding: 28px 16px 50px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            width: 100%;
            max-width: 760px;
            margin: 0 auto 18px;
        }

        .toolbar-group {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .toolbar-button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 42px;
            padding: 10px 16px;
            color: #111827;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
        }

        .toolbar-button:hover {
            background: #f9fafb;
        }

        .toolbar-button-primary {
            color: #ffffff;
            background: #111827;
            border-color: #111827;
        }

        .toolbar-button-primary:hover {
            background: #1f2937;
        }

        .label-sheet {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            padding: 30px;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            box-shadow: 0 14px 45px rgba(17, 24, 39, 0.1);
        }

        .shipping-label {
            width: 100%;
            max-width: 432px;
            min-height: 648px;
            margin: 0 auto;
            color: #000000;
            background: #ffffff;
            border: 3px solid #000000;
        }

        .label-section {
            padding: 12px 14px;
            border-bottom: 2px solid #000000;
        }

        .label-section:last-child {
            border-bottom: 0;
        }

        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .company-name {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .company-subtitle {
            margin-top: 3px;
            color: #444444;
            font-size: 10px;
            font-weight: 700;
        }

        .service-box {
            min-width: 105px;
            padding: 7px;
            border: 2px solid #000000;
            text-align: center;
        }

        .service-label {
            display: block;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .service-value {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .address-title {
            margin-bottom: 7px;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sender-name {
            margin-bottom: 2px;
            font-size: 12px;
            font-weight: 900;
        }

        .sender-address {
            font-size: 10px;
            line-height: 1.45;
        }

        .recipient-section {
            min-height: 168px;
            padding: 15px 18px;
        }

        .recipient-name {
            margin-bottom: 5px;
            font-size: 24px;
            font-weight: 900;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .recipient-address {
            font-size: 17px;
            font-weight: 800;
            line-height: 1.35;
            text-transform: uppercase;
        }

        .recipient-contact {
            margin-top: 9px;
            font-size: 10px;
            font-weight: 700;
            text-transform: none;
        }

        .tracking-heading {
            margin-bottom: 6px;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 1px;
            text-align: center;
            text-transform: uppercase;
        }

        .tracking-number {
            margin-bottom: 9px;
            font-size: 17px;
            font-weight: 900;
            letter-spacing: 1px;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .barcode {
            height: 70px;
            margin: 0 4px;
            background:
                repeating-linear-gradient(
                    90deg,
                    #000000 0,
                    #000000 2px,
                    transparent 2px,
                    transparent 4px,
                    #000000 4px,
                    #000000 5px,
                    transparent 5px,
                    transparent 8px,
                    #000000 8px,
                    #000000 11px,
                    transparent 11px,
                    transparent 13px
                );
        }

        .barcode-caption {
            margin-top: 5px;
            font-family: monospace;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-align: center;
        }

        .shipment-grid {
            display: grid;
            grid-template-columns: 1fr 115px;
            min-height: 125px;
        }

        .shipment-info {
            padding: 12px 14px;
            border-right: 2px solid #000000;
        }

        .shipment-row {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 10px;
        }

        .shipment-row:last-child {
            margin-bottom: 0;
        }

        .shipment-label {
            font-weight: 900;
            text-transform: uppercase;
        }

        .shipment-value {
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .qr-box {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 8px;
        }

        .qr-code {
            display: grid;
            grid-template-columns: repeat(11, 1fr);
            grid-template-rows: repeat(11, 1fr);
            width: 88px;
            height: 88px;
            padding: 5px;
            background: #ffffff;
            border: 2px solid #000000;
        }

        .qr-code span {
            background: transparent;
        }

        .qr-code span:nth-child(2n),
        .qr-code span:nth-child(3n),
        .qr-code span:nth-child(7n),
        .qr-code span:nth-child(11n) {
            background: #000000;
        }

        .qr-caption {
            margin-top: 5px;
            font-size: 7px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .order-reference {
            font-size: 13px;
            font-weight: 900;
        }

        .package-reference {
            font-size: 11px;
            font-weight: 800;
            text-align: right;
        }

        .preview-note {
            max-width: 760px;
            margin: 16px auto 0;
            color: #6b7280;
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 620px) {
            .toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .toolbar-group {
                width: 100%;
            }

            .toolbar-button {
                flex: 1;
            }

            .label-sheet {
                padding: 12px;
            }

            .shipping-label {
                max-width: 100%;
            }

            .recipient-name {
                font-size: 20px;
            }

            .recipient-address {
                font-size: 14px;
            }
        }

        @media print {
            @page {
                size: 4in 6in;
                margin: 0;
            }

            body {
                background: #ffffff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                padding: 0;
            }

            .toolbar,
            .preview-note {
                display: none !important;
            }

            .label-sheet {
                width: 4in;
                max-width: 4in;
                margin: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .shipping-label {
                width: 4in;
                max-width: 4in;
                height: 6in;
                min-height: 6in;
                margin: 0;
                border-width: 2px;
                border-radius: 0;
                overflow: hidden;
            }

            .label-section {
                padding: 8px 10px;
            }

            .recipient-section {
                min-height: 148px;
                padding: 11px 14px;
            }

            .recipient-name {
                font-size: 20px;
            }

            .recipient-address {
                font-size: 14px;
            }

            .barcode {
                height: 55px;
            }

            .shipment-grid {
                min-height: 105px;
            }

            .qr-code {
                width: 75px;
                height: 75px;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <div class="toolbar">
            <div class="toolbar-group">
                <a
                    href="{{ route('admin.orders.show', $order) }}"
                    class="toolbar-button"
                >
                    ← Back to order
                </a>
            </div>

            <div class="toolbar-group">
                <button
                    type="button"
                    class="toolbar-button"
                    onclick="window.print()"
                >
                    Print 4 × 6 label
                </button>

                <a
                    href="{{ route('admin.orders.shipping-label.download', $order) }}"
                    class="toolbar-button toolbar-button-primary"
                >
                    Download PDF
                </a>
            </div>
        </div>

        <section class="label-sheet">
            <article class="shipping-label">
                <header class="label-section label-header">
                    <div>
                        <div class="company-name">
                            Arizona Outfits
                        </div>

                        <div class="company-subtitle">
                            Premium clothing and lifestyle products
                        </div>
                    </div>

                    <div class="service-box">
                        <span class="service-label">
                            Service
                        </span>

                        <span class="service-value">
                            {{ $service }}
                        </span>
                    </div>
                </header>

                <section class="label-section">
                    <div class="address-title">
                        From
                    </div>

                    <div class="sender-name">
                        Arizona Outfits
                    </div>

                    <div class="sender-address">
                        Main fulfilment warehouse<br>
                        Warehouse address<br>
                        City, State, Postal Code<br>
                        Pakistan<br>
                        support@arizonaoutfits.com
                    </div>
                </section>

                <section class="label-section recipient-section">
                    <div class="address-title">
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
                            <div>Shipping address not provided</div>
                        @endif
                    </div>

                    @if ($recipientPhone || $recipientEmail)
                        <div class="recipient-contact">
                            @if ($recipientPhone)
                                Phone: {{ $recipientPhone }}
                            @endif

                            @if ($recipientPhone && $recipientEmail)
                                <br>
                            @endif

                            @if ($recipientEmail)
                                Email: {{ $recipientEmail }}
                            @endif
                        </div>
                    @endif
                </section>

                <section class="label-section">
                    <div class="tracking-heading">
                        Tracking number
                    </div>

                    <div class="tracking-number">
                        {{ $trackingNumber ?: 'NOT ASSIGNED' }}
                    </div>

                    <div class="barcode"></div>

                    <div class="barcode-caption">
                        {{ $barcodePattern }}
                    </div>
                </section>

                <section class="shipment-grid">
                    <div class="shipment-info">
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
                    </div>

                    <div class="qr-box">
                        <div
                            class="qr-code"
                            title="{{ $qrData }}"
                        >
                            @for ($i = 1; $i <= 121; $i++)
                                <span></span>
                            @endfor
                        </div>

                        <div class="qr-caption">
                            Scan order
                        </div>
                    </div>
                </section>

                <footer class="label-section footer-row">
                    <div class="order-reference">
                        {{ $orderNumber }}
                    </div>

                    <div class="package-reference">
                        Package {{ $packageNumber }}
                        of {{ $totalPackages }}
                    </div>
                </footer>
            </article>
        </section>

        <div class="preview-note">
            This preview is formatted for a 4 × 6-inch thermal printer.
            The visual barcode and QR pattern will be replaced with actual
            scannable codes in the barcode integration step.
        </div>
    </main>
</body>
</html>