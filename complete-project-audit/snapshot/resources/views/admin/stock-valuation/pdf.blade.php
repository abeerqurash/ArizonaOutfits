<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Stock Valuation Report</title>

    <style>
        @page {
            margin: 25px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2937;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
        }

        .report-header {
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #111827;
        }

        .report-header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-header-table td {
            vertical-align: middle;
        }

        .brand-cell {
            width: 65%;
        }

        .report-meta-cell {
            width: 35%;
            text-align: right;
        }

        .brand-name {
            margin: 0 0 4px;
            color: #111827;
            font-size: 22px;
            font-weight: bold;
        }

        .report-title {
            margin: 0;
            color: #4f46e5;
            font-size: 14px;
            font-weight: bold;
        }

        .report-description {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 9px;
        }

        .report-meta {
            margin: 0 0 3px;
            color: #374151;
            font-size: 9px;
        }

        .report-meta strong {
            color: #111827;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 18px;
            border-spacing: 7px;
            border-collapse: separate;
        }

        .summary-table td {
            width: 25%;
            padding: 13px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            background: #f8fafc;
            vertical-align: top;
        }

        .summary-label {
            display: block;
            margin-bottom: 7px;
            color: #6b7280;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .summary-value {
            display: block;
            color: #111827;
            font-size: 16px;
            font-weight: bold;
        }

        .summary-value.profit {
            color: #15803d;
        }

        .health-section {
            width: 100%;
            margin-bottom: 19px;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            background: #ffffff;
        }

        .health-title {
            margin: 0 0 10px;
            color: #111827;
            font-size: 13px;
            font-weight: bold;
        }

        .health-table {
            width: 100%;
            border-collapse: collapse;
        }

        .health-table td {
            width: 16.66%;
            padding: 7px;
            border-right: 1px solid #e5e7eb;
            text-align: center;
        }

        .health-table td:last-child {
            border-right: 0;
        }

        .health-number {
            display: block;
            margin-bottom: 3px;
            color: #111827;
            font-size: 14px;
            font-weight: bold;
        }

        .health-label {
            color: #6b7280;
            font-size: 8px;
        }

        .health-score {
            color: #4f46e5;
        }

        .section-title {
            margin: 0 0 9px;
            color: #111827;
            font-size: 13px;
            font-weight: bold;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .product-table thead {
            display: table-header-group;
        }

        .product-table tr {
            page-break-inside: avoid;
        }

        .product-table th {
            padding: 8px 5px;
            border: 1px solid #d1d5db;
            background: #111827;
            color: #ffffff;
            font-size: 7px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
        }

        .product-table td {
            padding: 7px 5px;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-size: 7.5px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .product-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .product-name {
            color: #111827;
            font-weight: bold;
        }

        .stock-status {
            font-weight: bold;
        }

        .stock-status.available {
            color: #15803d;
        }

        .stock-status.low {
            color: #c2410c;
        }

        .stock-status.out {
            color: #b91c1c;
        }

        .positive-value {
            color: #15803d;
            font-weight: bold;
        }

        .negative-value {
            color: #b91c1c;
            font-weight: bold;
        }

        .missing-value {
            color: #c2410c;
            font-weight: bold;
        }

        .totals-row td {
            padding-top: 9px;
            padding-bottom: 9px;
            border-top: 2px solid #111827;
            background: #eef2ff;
            color: #111827;
            font-weight: bold;
        }

        .report-footer {
            margin-top: 17px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 8px;
            text-align: center;
        }

        .page-number::after {
            content: counter(page);
        }
    </style>
</head>

<body>

    <header class="report-header">

        <table class="report-header-table">

            <tr>

                <td class="brand-cell">

                    <h1 class="brand-name">
                        Arizona Outfits
                    </h1>

                    <h2 class="report-title">
                        Stock Valuation Report
                    </h2>

                    <p class="report-description">
                        Complete inventory cost, retail value, expected profit
                        and stock health summary.
                    </p>

                </td>

                <td class="report-meta-cell">

                    <p class="report-meta">
                        <strong>Generated:</strong>
                        {{ now()->format('d M Y') }}
                    </p>

                    <p class="report-meta">
                        <strong>Time:</strong>
                        {{ now()->format('h:i A') }}
                    </p>

                    <p class="report-meta">
                        <strong>Total Products:</strong>
                        {{ number_format($products->count()) }}
                    </p>

                    <p class="report-meta">
                        <strong>Search:</strong>
                        {{ filled($appliedSearch ?? null)
        ? $appliedSearch
        : 'None'
    }}
                    </p>

                    <p class="report-meta">
                        <strong>Stock:</strong>
                        {{ $appliedStockFilter ?? 'All Stock' }}
                    </p>

                    <p class="report-meta">
                        <strong>Margin:</strong>
                        {{ $appliedMarginFilter ?? 'All Margins' }}
                    </p>

                    <p class="report-meta">
                        <strong>Sort:</strong>
                        {{ $appliedSortFilter ?? 'Product Name A–Z' }}
                    </p>

                </td>

            </tr>

        </table>

    </header>

    <table class="summary-table">

        <tr>

            <td>
                <span class="summary-label">
                    Inventory Cost
                </span>

                <span class="summary-value">
                    £{{ number_format($totalCost, 2) }}
                </span>
            </td>

            <td>
                <span class="summary-label">
                    Retail Value
                </span>

                <span class="summary-value">
                    £{{ number_format($totalRetail, 2) }}
                </span>
            </td>

            <td>
                <span class="summary-label">
                    Expected Profit
                </span>

                <span
                    class="summary-value {{
                        $totalProfit < 0
                            ? ''
                            : 'profit'
                    }}">

                    {{ $totalProfit < 0 ? '-' : '' }}
                    £{{ number_format(abs($totalProfit), 2) }}
                </span>
            </td>

            <td>
                <span class="summary-label">
                    Total Units
                </span>

                <span class="summary-value">
                    {{ number_format($totalUnits) }}
                </span>
            </td>

        </tr>

    </table>

    <section class="health-section">

        <h2 class="health-title">
            Inventory Health
        </h2>

        <table class="health-table">

            <tr>

                <td>
                    <span class="health-number health-score">
                        {{ $healthScore }}%
                    </span>

                    <span class="health-label">
                        Health Score
                    </span>
                </td>

                <td>
                    <span class="health-number">
                        {{ number_format($healthyProducts) }}
                    </span>

                    <span class="health-label">
                        Healthy
                    </span>
                </td>

                <td>
                    <span class="health-number">
                        {{ number_format($lowStockProducts) }}
                    </span>

                    <span class="health-label">
                        Low Stock
                    </span>
                </td>

                <td>
                    <span class="health-number">
                        {{ number_format($outOfStockProducts) }}
                    </span>

                    <span class="health-label">
                        Out of Stock
                    </span>
                </td>

                <td>
                    <span class="health-number">
                        {{ number_format($missingCostProducts) }}
                    </span>

                    <span class="health-label">
                        Missing Cost
                    </span>
                </td>

                <td>
                    <span class="health-number">
                        {{ number_format($negativeMarginProducts) }}
                    </span>

                    <span class="health-label">
                        Negative Margin
                    </span>
                </td>

            </tr>

        </table>

    </section>

    <h2 class="section-title">
        Product Valuation Breakdown
    </h2>

    <table class="product-table">

        <thead>

            <tr>
                <th style="width:16%;">Product</th>
                <th style="width:9%;">SKU</th>
                <th style="width:13%;">Categories</th>
                <th style="width:8%;">Status</th>
                <th class="text-right" style="width:6%;">Units</th>
                <th class="text-right" style="width:8%;">Cost</th>
                <th class="text-right" style="width:8%;">Selling</th>
                <th class="text-right" style="width:9%;">Inv. Cost</th>
                <th class="text-right" style="width:9%;">Retail</th>
                <th class="text-right" style="width:9%;">Profit</th>
                <th class="text-right" style="width:5%;">Margin</th>
            </tr>

        </thead>

        <tbody>

            @forelse ($products as $product)

            @php
            $stock = (int) ($product->stock ?? 0);
            $costPrice = (float) ($product->cost_price ?? 0);
            $sellingPrice = (float) (
            $product->selling_price ?? 0
            );

            $inventoryCost = (float) (
            $product->inventory_cost ?? 0
            );

            $inventoryRetail = (float) (
            $product->inventory_retail ?? 0
            );

            $inventoryProfit = (float) (
            $product->inventory_profit ?? 0
            );

            $margin = (float) (
            $product->margin ?? 0
            );

            if ($stock <= 0) {
                $stockClass='out' ;
                $stockLabel='Out of stock' ;
                } elseif ($stock <=5) {
                $stockClass='low' ;
                $stockLabel='Low stock' ;
                } else {
                $stockClass='available' ;
                $stockLabel='In stock' ;
                }

                $categoryNames=$product
                ->categories
                ->pluck('title')
                ->filter()
                ->implode(', ');
                @endphp

                <tr>

                    <td class="product-name">
                        {{ $product->title }}
                    </td>

                    <td>
                        {{ $product->sku ?: 'Not assigned' }}
                    </td>

                    <td>
                        {{ $categoryNames ?: 'Uncategorised' }}
                    </td>

                    <td>
                        <span
                            class="stock-status {{ $stockClass }}">

                            {{ $stockLabel }}
                        </span>
                    </td>

                    <td class="text-right">
                        {{ number_format($stock) }}
                    </td>

                    <td class="text-right">

                        @if ($costPrice > 0)

                        £{{ number_format($costPrice, 2) }}

                        @else

                        <span class="missing-value">
                            Not set
                        </span>

                        @endif

                    </td>

                    <td class="text-right">
                        £{{ number_format($sellingPrice, 2) }}
                    </td>

                    <td class="text-right">
                        £{{ number_format($inventoryCost, 2) }}
                    </td>

                    <td class="text-right">
                        £{{ number_format($inventoryRetail, 2) }}
                    </td>

                    <td class="text-right">

                        <span
                            class="{{
                                $inventoryProfit < 0
                                    ? 'negative-value'
                                    : 'positive-value'
                            }}">

                            {{ $inventoryProfit < 0 ? '-' : '' }}
                            £{{ number_format(
                                abs($inventoryProfit),
                                2
                            ) }}
                        </span>

                    </td>

                    <td class="text-right">

                        <span
                            class="{{
                                $margin < 0
                                    ? 'negative-value'
                                    : 'positive-value'
                            }}">

                            {{ number_format($margin, 1) }}%
                        </span>

                    </td>

                </tr>

                @empty

                <tr>

                    <td
                        colspan="11"
                        class="text-center">

                        No products found.
                    </td>

                </tr>

                @endforelse

        </tbody>

        @if ($products->isNotEmpty())

        <tfoot>

            <tr class="totals-row">

                <td colspan="4">
                    Inventory Totals
                </td>

                <td class="text-right">
                    {{ number_format($totalUnits) }}
                </td>

                <td colspan="2"></td>

                <td class="text-right">
                    £{{ number_format($totalCost, 2) }}
                </td>

                <td class="text-right">
                    £{{ number_format($totalRetail, 2) }}
                </td>

                <td class="text-right">
                    {{ $totalProfit < 0 ? '-' : '' }}
                    £{{ number_format(abs($totalProfit), 2) }}
                </td>

                <td class="text-right">
                    {{ number_format(
                            $totalRetail > 0
                                ? (
                                    $totalProfit
                                    / $totalRetail
                                ) * 100
                                : 0,
                            1
                        ) }}%
                </td>

            </tr>

        </tfoot>

        @endif

    </table>

    <footer class="report-footer">

        Arizona Outfits inventory report — generated automatically by the
        administration system.

    </footer>

</body>

</html>