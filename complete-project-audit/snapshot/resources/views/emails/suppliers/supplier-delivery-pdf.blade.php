<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchase Order {{ $purchaseOrder->reference }}</title>

    <style>
        @page { margin: 32px; }
        body { margin: 0; color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .header { padding-bottom: 18px; border-bottom: 2px solid #111827; }
        .header-table, .details-table, .items-table, .totals-table { width: 100%; border-collapse: collapse; }
        .brand { font-size: 20px; font-weight: bold; }
        .document-title { color: #4f46e5; font-size: 24px; font-weight: bold; text-align: right; }
        .reference { margin-top: 4px; color: #6b7280; text-align: right; }
        .details { margin: 22px 0; }
        .details-box { padding: 12px; border: 1px solid #e5e7eb; vertical-align: top; }
        .label { display: block; margin-bottom: 4px; color: #6b7280; font-size: 9px; text-transform: uppercase; }
        .items-table th { padding: 10px 8px; background: #111827; color: #fff; font-size: 9px; text-align: left; }
        .items-table td { padding: 9px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .right { text-align: right !important; }
        .muted { color: #6b7280; font-size: 9px; }
        .totals { width: 42%; margin: 18px 0 0 auto; }
        .totals-table td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; }
        .grand-total td { background: #eef2ff; color: #312e81; font-size: 14px; font-weight: bold; }
        .notes { margin-top: 24px; padding: 13px; border: 1px solid #e5e7eb; background: #f9fafb; line-height: 1.6; }
        .footer { margin-top: 28px; padding-top: 12px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 9px; text-align: center; }
    </style>
</head>

<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand">{{ config('app.name') }}</div>
                    <div class="muted">Procurement Department</div>
                </td>
                <td>
                    <div class="document-title">PURCHASE ORDER</div>
                    <div class="reference">{{ $purchaseOrder->reference }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="details">
        <table class="details-table">
            <tr>
                <td class="details-box" width="50%">
                    <span class="label">Supplier</span>
                    <strong>{{ $purchaseOrder->supplier->company_name ?? 'Supplier' }}</strong><br>
                    {{ $purchaseOrder->supplier->full_address ?? '' }}<br>
                    {{ $purchaseOrder->supplier->email ?? '' }}
                </td>
                <td width="3%"></td>
                <td class="details-box" width="47%">
                    <span class="label">Order date</span>
                    <strong>{{ optional($purchaseOrder->order_date)->format('d M Y') ?: optional($purchaseOrder->created_at)->format('d M Y') }}</strong><br><br>
                    <span class="label">Expected delivery</span>
                    <strong>{{ optional($purchaseOrder->expected_date)->format('d M Y') ?: 'To be confirmed' }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="46%">Item</th>
                <th width="16%">SKU</th>
                <th class="right" width="10%">Qty</th>
                <th class="right" width="14%">Unit Price</th>
                <th class="right" width="14%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchaseOrder->items as $item)
            @php
                $quantity = (float) ($item->quantity ?? $item->ordered_quantity ?? 0);
                $unitPrice = (float) ($item->unit_price ?? $item->cost_price ?? 0);
                $lineTotal = (float) ($item->total_amount ?? $item->line_total ?? ($quantity * $unitPrice));
                $itemName = $item->product_name ?? $item->description ?? optional($item->product)->title ?? 'Purchase-order item';
                $itemSku = $item->sku ?? optional($item->product)->sku ?? '—';
            @endphp
            <tr>
                <td><strong>{{ $itemName }}</strong></td>
                <td>{{ $itemSku }}</td>
                <td class="right">{{ number_format($quantity, 2) }}</td>
                <td class="right">£{{ number_format($unitPrice, 2) }}</td>
                <td class="right">£{{ number_format($lineTotal, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:18px;text-align:center;color:#6b7280;">No line items are available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals">
        <table class="totals-table">
            <tr class="grand-total">
                <td>Total</td>
                <td class="right">£{{ number_format((float) $purchaseOrder->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    @if ($purchaseOrder->notes ?? null)
    <div class="notes">
        <span class="label">Purchase-order notes</span>
        {{ $purchaseOrder->notes }}
    </div>
    @endif

    <div class="footer">
        Purchase Order {{ $purchaseOrder->reference }} · Generated {{ now()->format('d M Y H:i') }}
    </div>

</body>

</html>
