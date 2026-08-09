@extends('admin.layouts.app')

@section('title', 'Receiving & Returns — ' . $purchaseOrder->reference)

@section('content')

<div class="po-receiving-page">
    <header class="po-receiving-header">
        <div>
            <span class="po-receiving-eyebrow">Inventory control</span>
            <h1>Receiving & Returns</h1>
            <p>{{ $purchaseOrder->reference }} · {{ $purchaseOrder->supplier_name ?: 'No supplier' }}</p>
        </div>
        <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}" class="po-action secondary">
            <i class="fa-solid fa-arrow-left"></i> Purchase Order
        </a>
    </header>

    @if (session('success'))
        <div class="po-message success"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="po-message error"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="po-message error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div><strong>The action was not completed.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif

    <section class="po-receiving-stats">
        <article><span class="blue"><i class="fa-solid fa-boxes-packing"></i></span><div><small>Net received</small><strong>{{ number_format($stats['net_received']) }}</strong></div></article>
        <article><span class="amber"><i class="fa-solid fa-rotate-left"></i></span><div><small>Corrected units</small><strong>{{ number_format($stats['corrected']) }}</strong></div></article>
        <article><span class="red"><i class="fa-solid fa-truck-arrow-right"></i></span><div><small>Returned units</small><strong>{{ number_format($stats['returned']) }}</strong></div></article>
        <article><span class="purple"><i class="fa-solid fa-hourglass-half"></i></span><div><small>Open returns</small><strong>{{ number_format($stats['open_returns']) }}</strong></div></article>
    </section>

    <section class="po-panel">
        <div class="po-panel-heading">
            <span><i class="fa-solid fa-truck-arrow-right"></i></span>
            <div><h2>Create supplier return</h2><p>Stock is deducted immediately when the return is submitted.</p></div>
        </div>

        @if ($returnableItems->isNotEmpty())
            <form method="POST" action="{{ route('admin.purchase-orders.supplier-returns.store', $purchaseOrder) }}" class="po-return-form">
                @csrf

                <div class="po-return-fields">
                    <div class="field">
                        <label for="reason_category">Reason category <b>*</b></label>
                        <select id="reason_category" name="reason_category" required>
                            <option value="">Select a reason</option>
                            <option value="damaged" @selected(old('reason_category') === 'damaged')>Damaged</option>
                            <option value="incorrect_item" @selected(old('reason_category') === 'incorrect_item')>Incorrect item</option>
                            <option value="quality_issue" @selected(old('reason_category') === 'quality_issue')>Quality issue</option>
                            <option value="over_delivery" @selected(old('reason_category') === 'over_delivery')>Over-delivery</option>
                            <option value="other" @selected(old('reason_category') === 'other')>Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="returned_at">Return date</label>
                        <input id="returned_at" type="datetime-local" name="returned_at" value="{{ old('returned_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="field full">
                        <label for="return_reason">Detailed reason <b>*</b></label>
                        <textarea id="return_reason" name="reason" rows="3" minlength="5" maxlength="5000" required>{{ old('reason') }}</textarea>
                    </div>
                </div>

                <div class="po-table-wrap">
                    <table class="po-table">
                        <thead><tr><th>Item</th><th>SKU</th><th class="number">Received</th><th class="number">Already returned</th><th class="number">Returnable</th><th class="number">Return now</th></tr></thead>
                        <tbody>
                            @foreach ($returnableItems as $index => $row)
                                <tr>
                                    <td><strong>{{ $row['item']->item_name }}</strong>@if ($row['item']->variant_name)<small>{{ $row['item']->variant_name }}</small>@endif<input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $row['item']->id }}"></td>
                                    <td>{{ $row['item']->sku ?: 'Not assigned' }}</td>
                                    <td class="number">{{ number_format($row['item']->quantity_received) }}</td>
                                    <td class="number">{{ number_format($row['returned']) }}</td>
                                    <td class="number"><strong>{{ number_format($row['returnable']) }}</strong></td>
                                    <td class="number"><input class="return-quantity" type="number" name="items[{{ $index }}][quantity]" value="{{ old('items.' . $index . '.quantity', 0) }}" min="0" max="{{ $row['returnable'] }}" step="1"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="po-return-footer">
                    <div class="field"><label for="return_notes">Internal notes</label><textarea id="return_notes" name="notes" rows="2" maxlength="5000">{{ old('notes') }}</textarea></div>
                    <button type="submit" class="po-action danger" data-confirm="Submit this supplier return and deduct the selected stock?">
                        <i class="fa-solid fa-truck-arrow-right"></i> Submit Return
                    </button>
                </div>
            </form>
        @else
            <div class="po-empty"><i class="fa-solid fa-box-open"></i><h3>No inventory is currently returnable</h3><p>Receive stock first, or review previous supplier returns below.</p></div>
        @endif
    </section>

    <section class="po-panel">
        <div class="po-panel-heading">
            <span><i class="fa-solid fa-clock-rotate-left"></i></span>
            <div><h2>Receiving history</h2><p>Corrections reverse an incorrect receipt and reduce current stock.</p></div>
        </div>

        @forelse ($receipts as $receipt)
            <article class="receipt-card">
                <div class="receipt-header">
                    <div><strong>{{ $receipt->reference }}</strong><small>{{ $receipt->received_at->format('d M Y H:i') }} · {{ $receipt->receiver?->name ?: 'System' }}</small></div>
                    <div><span>Received {{ number_format($receipt->total_received) }}</span><span>Corrected {{ number_format($receipt->total_corrected) }}</span></div>
                </div>
                @if ($receipt->notes)<p class="receipt-notes">{{ $receipt->notes }}</p>@endif

                <div class="receipt-items">
                    @foreach ($receipt->items as $receiptItem)
                        @php
                            $orderItem = $receiptItem->purchaseOrderItem;
                            $returnedForItem = (int) ($activeReturnedByItem[$receiptItem->purchase_order_item_id] ?? 0);
                            $itemNetAvailable = max(0, (int) ($orderItem?->quantity_received ?? 0) - $returnedForItem);
                            $correctable = min($receiptItem->correctable_quantity, $itemNetAvailable);
                        @endphp
                        <div class="receipt-item">
                            <div><strong>{{ $orderItem?->item_name ?: 'Deleted item' }}</strong><small>{{ $orderItem?->sku ?: 'No SKU' }} · Received {{ number_format($receiptItem->quantity_received) }} · Corrected {{ number_format($receiptItem->quantity_corrected) }}</small></div>
                            @if ($correctable > 0)
                                <details>
                                    <summary><i class="fa-solid fa-rotate-left"></i> Correct receipt</summary>
                                    <form method="POST" action="{{ route('admin.purchase-orders.receipts.correct', [$purchaseOrder, $receipt, $receiptItem]) }}" class="correction-form">
                                        @csrf
                                        <div class="field"><label>Quantity</label><input type="number" name="quantity" value="1" min="1" max="{{ $correctable }}" required></div>
                                        <div class="field grow"><label>Correction reason</label><input name="reason" minlength="5" maxlength="5000" required placeholder="Explain the receiving error"></div>
                                        <button class="po-action warning" type="submit" data-confirm="Apply this receiving correction and reduce stock?">Apply</button>
                                    </form>
                                </details>
                            @else
                                <span class="closed-label">No correction available</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="po-empty"><i class="fa-solid fa-clipboard-check"></i><h3>No tracked receipts yet</h3><p>New receiving actions will appear here automatically.</p></div>
        @endforelse
    </section>

    <section class="po-panel">
        <div class="po-panel-heading">
            <span><i class="fa-solid fa-file-arrow-up"></i></span>
            <div><h2>Supplier return history</h2><p>Complete a return when the supplier confirms its credit.</p></div>
        </div>

        @forelse ($supplierReturns as $supplierReturn)
            <article class="supplier-return-card">
                <div class="supplier-return-header">
                    <div><strong>{{ $supplierReturn->reference }}</strong><small>{{ $supplierReturn->returned_at->format('d M Y H:i') }} · {{ $supplierReturn->creator?->name ?: 'System' }}</small></div>
                    <span class="return-status {{ $supplierReturn->status }}">{{ $supplierReturn->status_label }}</span>
                </div>
                <div class="return-summary">
                    <div><small>Reason</small><strong>{{ str($supplierReturn->reason_category)->headline() }}</strong></div>
                    <div><small>Units</small><strong>{{ number_format($supplierReturn->items->sum('quantity')) }}</strong></div>
                    <div><small>Expected credit</small><strong>{{ $purchaseOrder->currency }} {{ number_format((float) $supplierReturn->expected_credit, 2) }}</strong></div>
                    <div><small>Actual credit</small><strong>{{ $supplierReturn->actual_credit !== null ? $purchaseOrder->currency . ' ' . number_format((float) $supplierReturn->actual_credit, 2) : 'Pending' }}</strong></div>
                </div>
                <p class="return-reason">{{ $supplierReturn->reason }}</p>
                <div class="return-item-list">
                    @foreach ($supplierReturn->items as $returnItem)
                        <span>{{ $returnItem->purchaseOrderItem?->item_name ?: 'Deleted item' }} × {{ number_format($returnItem->quantity) }}</span>
                    @endforeach
                </div>

                @if ($supplierReturn->isSubmitted())
                    <div class="return-actions">
                        <form method="POST" action="{{ route('admin.purchase-orders.supplier-returns.complete', [$purchaseOrder, $supplierReturn]) }}">
                            @csrf @method('PATCH')
                            <div class="field"><label>Actual supplier credit</label><input type="number" name="actual_credit" value="{{ number_format((float) $supplierReturn->expected_credit, 2, '.', '') }}" min="0" step="0.01" required></div>
                            <button class="po-action success" type="submit" data-confirm="Complete this return with the entered supplier credit?">Complete Return</button>
                        </form>
                        <form method="POST" action="{{ route('admin.purchase-orders.supplier-returns.cancel', [$purchaseOrder, $supplierReturn]) }}">
                            @csrf @method('PATCH')
                            <div class="field"><label>Cancellation reason</label><input name="cancellation_reason" minlength="5" maxlength="5000" required></div>
                            <button class="po-action danger-outline" type="submit" data-confirm="Cancel this return and restore all its stock?">Cancel & Restore Stock</button>
                        </form>
                    </div>
                @elseif ($supplierReturn->isCancelled() && $supplierReturn->cancellation_reason)
                    <p class="cancel-note"><strong>Cancelled:</strong> {{ $supplierReturn->cancellation_reason }}</p>
                @endif
            </article>
        @empty
            <div class="po-empty"><i class="fa-solid fa-truck-arrow-right"></i><h3>No supplier returns</h3><p>Returns created for this purchase order will appear here.</p></div>
        @endforelse
    </section>
</div>

@endsection

@push('page-styles')
<style>
    .po-receiving-page{--navy:#172033;--muted:#64748b;--border:#e2e8f0;display:grid;gap:20px;color:var(--navy)}.po-receiving-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:27px;border-radius:20px;background:linear-gradient(135deg,#111827,#0f766e);color:#fff;box-shadow:0 18px 45px rgba(15,23,42,.15)}.po-receiving-eyebrow{color:#99f6e4;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.po-receiving-header h1{margin:6px 0 0;font-size:29px}.po-receiving-header p{margin:7px 0 0;color:#ccfbf1}.po-action{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:0;border-radius:9px;padding:10px 13px;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}.po-action.secondary{background:#fff;color:#115e59}.po-action.danger{background:#be123c;color:#fff}.po-action.warning{background:#d97706;color:#fff}.po-action.success{background:#047857;color:#fff}.po-action.danger-outline{border:1px solid #fecdd3;background:#fff1f2;color:#be123c}.po-message{display:flex;align-items:flex-start;gap:10px;padding:14px 17px;border-radius:12px}.po-message.success{border:1px solid #a7f3d0;background:#ecfdf5;color:#047857}.po-message.error{border:1px solid #fecdd3;background:#fff1f2;color:#be123c}.po-message ul{margin:6px 0 0;padding-left:18px}.po-receiving-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.po-receiving-stats article{display:flex;align-items:center;gap:12px;padding:17px;border:1px solid var(--border);border-radius:15px;background:#fff}.po-receiving-stats article>span,.po-panel-heading>span{display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;border-radius:11px}.po-receiving-stats .blue,.po-panel-heading>span{background:#dbeafe;color:#2563eb}.po-receiving-stats .amber{background:#fef3c7;color:#d97706}.po-receiving-stats .red{background:#ffe4e6;color:#be123c}.po-receiving-stats .purple{background:#ede9fe;color:#7c3aed}.po-receiving-stats small,.return-summary small{display:block;color:var(--muted);font-size:11px}.po-receiving-stats strong{display:block;margin-top:3px;font-size:20px}.po-panel{overflow:hidden;border:1px solid var(--border);border-radius:17px;background:#fff;box-shadow:0 8px 26px rgba(15,23,42,.05)}.po-panel-heading{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid var(--border)}.po-panel-heading h2{margin:0;font-size:18px}.po-panel-heading p{margin:4px 0 0;color:var(--muted);font-size:12px}.po-return-fields{display:grid;grid-template-columns:1fr 1fr;gap:15px;padding:20px}.field.full{grid-column:1/-1}.field.grow{flex:1}.field label{display:block;margin-bottom:6px;font-size:12px;font-weight:800}.field label b{color:#dc2626}.field input,.field select,.field textarea{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:9px;padding:9px 10px;background:#fff;color:var(--navy);font:inherit}.po-table-wrap{overflow-x:auto}.po-table{width:100%;min-width:800px;border-collapse:collapse}.po-table th{padding:11px 14px;background:#f8fafc;color:var(--muted);font-size:10px;text-align:left;text-transform:uppercase}.po-table td{padding:13px 14px;border-top:1px solid var(--border)}.po-table td strong,.po-table td small{display:block}.po-table td small{margin-top:3px;color:var(--muted)}.po-table .number{text-align:right}.po-table input[type=number]{width:90px;border:1px solid #cbd5e1;border-radius:8px;padding:8px;text-align:right}.po-return-footer{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;padding:17px 20px;border-top:1px solid var(--border)}.po-return-footer .field{flex:1}.receipt-card,.supplier-return-card{margin:16px;border:1px solid var(--border);border-radius:13px}.receipt-header,.supplier-return-header{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:14px 16px;background:#f8fafc;border-radius:13px 13px 0 0}.receipt-header strong,.receipt-header small,.supplier-return-header strong,.supplier-return-header small{display:block}.receipt-header small,.supplier-return-header small{margin-top:3px;color:var(--muted)}.receipt-header>div:last-child{display:flex;gap:7px}.receipt-header>div:last-child span{padding:5px 8px;border-radius:999px;background:#e2e8f0;font-size:10px;font-weight:800}.receipt-notes,.return-reason{margin:0;padding:12px 16px;color:#475569;border-top:1px solid var(--border)}.receipt-item{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:13px 16px;border-top:1px solid var(--border)}.receipt-item strong,.receipt-item small{display:block}.receipt-item small{margin-top:3px;color:var(--muted)}.receipt-item summary{color:#b45309;font-size:12px;font-weight:800;cursor:pointer}.correction-form{display:flex;align-items:flex-end;gap:9px;margin-top:10px;padding:11px;border-radius:10px;background:#fffbeb}.correction-form input[type=number]{width:90px}.closed-label{color:#94a3b8;font-size:11px}.return-status{padding:6px 9px;border-radius:999px;font-size:10px;font-weight:850}.return-status.submitted{background:#fef3c7;color:#b45309}.return-status.completed{background:#d1fae5;color:#047857}.return-status.cancelled{background:#ffe4e6;color:#be123c}.return-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-top:1px solid var(--border);border-bottom:1px solid var(--border)}.return-summary>div{padding:12px 16px;border-right:1px solid var(--border)}.return-summary>div:last-child{border-right:0}.return-summary strong{display:block;margin-top:3px}.return-item-list{display:flex;flex-wrap:wrap;gap:7px;padding:12px 16px}.return-item-list span{padding:5px 8px;border-radius:8px;background:#f1f5f9;font-size:11px}.return-actions{display:grid;grid-template-columns:1fr 1fr;gap:13px;padding:14px 16px;border-top:1px solid var(--border)}.return-actions form{display:flex;align-items:flex-end;gap:9px;padding:11px;border-radius:10px;background:#f8fafc}.return-actions .field{flex:1}.cancel-note{margin:0;padding:12px 16px;border-top:1px solid #fecdd3;background:#fff1f2;color:#be123c}.po-empty{text-align:center;padding:45px 20px;color:var(--muted)}.po-empty i{font-size:34px;color:#cbd5e1}.po-empty h3{margin:12px 0 4px;color:#334155}.po-empty p{margin:0}
    @media(max-width:950px){.po-receiving-stats,.return-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.return-actions{grid-template-columns:1fr}.return-summary>div:nth-child(2){border-right:0}}
    @media(max-width:680px){.po-receiving-header,.po-return-footer,.receipt-header,.supplier-return-header,.receipt-item{align-items:stretch;flex-direction:column}.po-receiving-stats,.po-return-fields,.return-summary{grid-template-columns:1fr}.field.full{grid-column:auto}.correction-form,.return-actions form{align-items:stretch;flex-direction:column}.po-action{width:100%;box-sizing:border-box}.return-summary>div{border-right:0;border-bottom:1px solid var(--border)}}
</style>
@endpush

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('button[data-confirm]').forEach(function (button) {
            button.closest('form')?.addEventListener('submit', function (event) {
                if (!window.confirm(button.dataset.confirm || 'Continue?')) {
                    event.preventDefault();
                    return;
                }
                button.disabled = true;
            });
        });
    });
</script>
@endpush
