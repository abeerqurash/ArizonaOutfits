<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        {{ $purchaseOrder->reference }}
    </title>

    <style>
        @page {
            margin: 22px 24px;
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

        .document-header {
            width: 100%;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 2px solid #111827;
        }

        .document-header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .document-header-table td {
            vertical-align: top;
        }

        .company-name {
            margin: 0 0 5px;
            color: #111827;
            font-size: 21px;
            font-weight: bold;
        }

        .company-subtitle {
            margin: 0;
            color: #64748b;
            font-size: 9px;
        }

        .document-title-cell {
            width: 40%;
            text-align: right;
        }

        .document-title {
            margin: 0 0 6px;
            color: #111827;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .document-reference {
            margin: 0;
            color: #4f46e5;
            font-size: 13px;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            margin-top: 7px;
            padding: 5px 9px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft {
            background: #f1f5f9;
            color: #475569;
        }

        .status-ordered {
            background: #e0f2fe;
            color: #0369a1;
        }

        .status-partially_received {
            background: #ffedd5;
            color: #c2410c;
        }

        .status-received {
            background: #dcfce7;
            color: #15803d;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #b91c1c;
        }

        .information-grid {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .information-grid td {
            width: 50%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            vertical-align: top;
        }

        .section-label {
            margin: 0 0 8px;
            color: #4f46e5;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .information-row {
            margin-bottom: 6px;
        }

        .information-row:last-child {
            margin-bottom: 0;
        }

        .information-row span {
            display: inline-block;
            width: 120px;
            color: #64748b;
        }

        .information-row strong {
            color: #111827;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 7px 0;
        }

        .summary-table td {
            width: 25%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            background: #f8fafc;
            text-align: center;
        }

        .summary-table span {
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 8px;
        }

        .summary-table strong {
            display: block;
            color: #111827;
            font-size: 15px;
        }

        .items-heading {
            margin: 0 0 8px;
            color: #111827;
            font-size: 14px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            padding: 7px 6px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .items-table th {
            background: #111827;
            color: #ffffff;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .items-table td {
            font-size: 8px;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .item-name {
            color: #111827;
            font-weight: bold;
        }

        .variant-name {
            display: block;
            margin-top: 2px;
            color: #64748b;
            font-size: 7px;
        }

        .number-column {
            text-align: right;
            white-space: nowrap;
        }

        .center-column {
            text-align: center;
        }

        .remaining-positive {
            color: #c2410c;
            font-weight: bold;
        }

        .remaining-complete {
            color: #15803d;
            font-weight: bold;
        }

        .financial-section {
            width: 100%;
            margin-top: 17px;
            border-collapse: separate;
            border-spacing: 12px 0;
        }

        .financial-section > tbody > tr > td {
            vertical-align: top;
        }

        .notes-column {
            width: 62%;
        }

        .totals-column {
            width: 38%;
        }

        .notes-box {
            min-height: 95px;
            padding: 11px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
        }

        .notes-box + .notes-box {
            margin-top: 8px;
        }

        .notes-title {
            margin: 0 0 5px;
            color: #111827;
            font-size: 10px;
            font-weight: bold;
        }

        .notes-text {
            margin: 0;
            color: #64748b;
            font-size: 8px;
            white-space: pre-line;
        }

        .totals-box {
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
        }

        .total-row {
            width: 100%;
            padding: 7px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .total-row:last-child {
            border-bottom: 0;
        }

        .total-row-table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-row-table td:last-child {
            text-align: right;
        }

        .total-label {
            color: #64748b;
        }

        .total-value {
            color: #111827;
            font-weight: bold;
        }

        .discount-value {
            color: #b91c1c;
        }

        .grand-total {
            padding-top: 11px;
            font-size: 13px;
            font-weight: bold;
        }

        .grand-total .total-value {
            color: #15803d;
        }

        .document-footer {
            width: 100%;
            margin-top: 18px;
            padding-top: 9px;
            border-top: 1px solid #e5e7eb;
            color: #94a3b8;
            font-size: 7px;
            text-align: center;
        }
    </style>

</head>

<body>

    <header class="document-header">

        <table class="document-header-table">

            <tr>

                <td>

                    <h1 class="company-name">
                        Arizona Outfits
                    </h1>

                    <p class="company-subtitle">
                        Inventory Purchasing Department
                    </p>

                </td>

                <td class="document-title-cell">

                    <h2 class="document-title">
                        Purchase Order
                    </h2>

                    <p class="document-reference">
                        {{ $purchaseOrder->reference }}
                    </p>

                    <span
                        class="status-badge status-{{
                            $purchaseOrder->status
                        }}">

                        {{ $purchaseOrder->status_label }}

                    </span>

                </td>

            </tr>

        </table>

    </header>

    <table class="information-grid">

        <tr>

            <td>

                <p class="section-label">
                    Supplier Information
                </p>

                <div class="information-row">

                    <span>
                        Supplier:
                    </span>

                    <strong>
                        {{
                            $purchaseOrder->supplier_name
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div class="information-row">

                    <span>
                        Email:
                    </span>

                    <strong>
                        {{
                            $purchaseOrder->supplier_email
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div class="information-row">

                    <span>
                        Phone:
                    </span>

                    <strong>
                        {{
                            $purchaseOrder->supplier_phone
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div class="information-row">

                    <span>
                        Address:
                    </span>

                    <strong>
                        {{
                            $purchaseOrder->supplier_address
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

            </td>

            <td>

                <p class="section-label">
                    Purchase Information
                </p>

                <div class="information-row">

                    <span>
                        Reference:
                    </span>

                    <strong>
                        {{ $purchaseOrder->reference }}
                    </strong>

                </div>

                <div class="information-row">

                    <span>
                        Order Date:
                    </span>

                    <strong>
                        {{
                            optional(
                                $purchaseOrder->order_date
                            )->format('d M Y')
                            ?: 'Not set'
                        }}
                    </strong>

                </div>

                <div class="information-row">

                    <span>
                        Expected:
                    </span>

                    <strong>
                        {{
                            optional(
                                $purchaseOrder->expected_date
                            )->format('d M Y')
                            ?: 'Not set'
                        }}
                    </strong>

                </div>

                <div class="information-row">

                    <span>
                        Created:
                    </span>

                    <strong>
                        {{
                            $purchaseOrder->created_at
                                ->format('d M Y H:i')
                        }}
                    </strong>

                </div>

                <div class="information-row">

                    <span>
                        Created By:
                    </span>

                    <strong>
                        {{
                            $purchaseOrder->creator
                                ? (
                                    $purchaseOrder->creator->name
                                    ?? $purchaseOrder->creator->email
                                )
                                : 'System'
                        }}
                    </strong>

                </div>

            </td>

        </tr>

    </table>

    <table class="summary-table">

        <tr>

            <td>

                <span>
                    Ordered Quantity
                </span>

                <strong>
                    {{ number_format($totalOrderedQuantity) }}
                </strong>

            </td>

            <td>

                <span>
                    Received Quantity
                </span>

                <strong>
                    {{ number_format($totalReceivedQuantity) }}
                </strong>

            </td>

            <td>

                <span>
                    Remaining Quantity
                </span>

                <strong>
                    {{ number_format($remainingQuantity) }}
                </strong>

            </td>

            <td>

                <span>
                    Receiving Progress
                </span>

                <strong>
                    {{ $receivingPercentage }}%
                </strong>

            </td>

        </tr>

    </table>

    <h3 class="items-heading">
        Purchase Order Items
    </h3>

    <table class="items-table">

        <thead>

            <tr>

                <th>
                    Product / Variant
                </th>

                <th>
                    SKU
                </th>

                <th>
                    Stock Before
                </th>

                <th>
                    Reorder Point
                </th>

                <th>
                    Ordered
                </th>

                <th>
                    Received
                </th>

                <th>
                    Remaining
                </th>

                <th>
                    Unit Cost
                </th>

                <th>
                    Line Total
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($purchaseOrder->items as $item)

                @php
                    $itemRemaining = max(
                        0,
                        (int) $item->quantity_ordered
                            - (int) $item->quantity_received
                    );
                @endphp

                <tr>

                    <td>

                        <span class="item-name">
                            {{ $item->item_name }}
                        </span>

                        @if ($item->variant_name)

                            <span class="variant-name">
                                {{ $item->variant_name }}
                            </span>

                        @endif

                    </td>

                    <td>
                        {{ $item->sku ?: 'Not assigned' }}
                    </td>

                    <td class="number-column">
                        {{ number_format($item->stock_before) }}
                    </td>

                    <td class="number-column">

                        {{
                            $item->reorder_point !== null
                                ? number_format(
                                    $item->reorder_point
                                )
                                : 'Not set'
                        }}

                    </td>

                    <td class="number-column">
                        {{ number_format($item->quantity_ordered) }}
                    </td>

                    <td class="number-column">
                        {{ number_format($item->quantity_received) }}
                    </td>

                    <td class="number-column">

                        <span class="{{
                            $itemRemaining > 0
                                ? 'remaining-positive'
                                : 'remaining-complete'
                        }}">

                            {{ number_format($itemRemaining) }}

                        </span>

                    </td>

                    <td class="number-column">

                        £{{ number_format(
                            $item->unit_cost,
                            2
                        ) }}

                    </td>

                    <td class="number-column">

                        £{{ number_format(
                            $item->line_total,
                            2
                        ) }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="9"
                        class="center-column">

                        No purchase-order items found.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

    <table class="financial-section">

        <tr>

            <td class="notes-column">

                <div class="notes-box">

                    <h4 class="notes-title">
                        Supplier Notes
                    </h4>

                    <p class="notes-text">
                        {{
                            $purchaseOrder->notes
                            ?: 'No supplier notes were added.'
                        }}
                    </p>

                </div>

                <div class="notes-box">

                    <h4 class="notes-title">
                        Internal Notes
                    </h4>

                    <p class="notes-text">
                        {{
                            $purchaseOrder->internal_notes
                            ?: 'No internal notes were added.'
                        }}
                    </p>

                </div>

            </td>

            <td class="totals-column">

                <div class="totals-box">

                    <div class="total-row">

                        <table class="total-row-table">

                            <tr>

                                <td class="total-label">
                                    Subtotal
                                </td>

                                <td class="total-value">

                                    £{{ number_format(
                                        $purchaseOrder->subtotal,
                                        2
                                    ) }}

                                </td>

                            </tr>

                        </table>

                    </div>

                    <div class="total-row">

                        <table class="total-row-table">

                            <tr>

                                <td class="total-label">
                                    Tax
                                </td>

                                <td class="total-value">

                                    £{{ number_format(
                                        $purchaseOrder->tax_amount,
                                        2
                                    ) }}

                                </td>

                            </tr>

                        </table>

                    </div>

                    <div class="total-row">

                        <table class="total-row-table">

                            <tr>

                                <td class="total-label">
                                    Shipping
                                </td>

                                <td class="total-value">

                                    £{{ number_format(
                                        $purchaseOrder->shipping_amount,
                                        2
                                    ) }}

                                </td>

                            </tr>

                        </table>

                    </div>

                    <div class="total-row">

                        <table class="total-row-table">

                            <tr>

                                <td class="total-label">
                                    Discount
                                </td>

                                <td class="total-value discount-value">

                                    -£{{ number_format(
                                        $purchaseOrder->discount_amount,
                                        2
                                    ) }}

                                </td>

                            </tr>

                        </table>

                    </div>

                    <div class="total-row grand-total">

                        <table class="total-row-table">

                            <tr>

                                <td class="total-label">
                                    Grand Total
                                </td>

                                <td class="total-value">

                                    £{{ number_format(
                                        $purchaseOrder->total_amount,
                                        2
                                    ) }}

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

            </td>

        </tr>

    </table>

    <footer class="document-footer">

        Generated on
        {{ now()->format('d M Y H:i') }}

        ·

        {{ $purchaseOrder->reference }}

        ·

        Arizona Outfits

    </footer>

</body>

</html>