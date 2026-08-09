@extends('admin.layouts.app')

@section('title', 'Edit ' . $purchaseOrder->reference)

@section('content')

@php
    $catalogByIdentifier = $catalogItems->keyBy('identifier');
    $submittedItems = old('items');

    if (!is_array($submittedItems)) {
        $submittedItems = $purchaseOrder->items
            ->values()
            ->map(function ($item): array {
                return [
                    'id' => $item->id,
                    'identifier' => $item->product_variant_id
                        ? 'variant:' . $item->product_variant_id
                        : 'product:' . $item->product_id,
                    'quantity' => $item->quantity_ordered,
                    'unit_cost' => number_format(
                        (float) $item->unit_cost,
                        2,
                        '.',
                        ''
                    ),
                ];
            })
            ->all();
    }

    $submittedItemIndexes = array_map(
        'intval',
        array_keys($submittedItems)
    );
    $nextSubmittedItemIndex = $submittedItemIndexes === []
        ? 0
        : max($submittedItemIndexes) + 1;
@endphp

<div class="purchase-order-edit-page">
    <header class="purchase-order-edit-header">
        <div>
            <span class="purchase-order-edit-eyebrow">Draft purchase order</span>
            <div class="purchase-order-edit-title">
                <h1>Edit {{ $purchaseOrder->reference }}</h1>
                <span>Draft</span>
            </div>
            <p>Changes are allowed until this purchase order is marked as ordered.</p>
        </div>

        <a
            href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}"
            class="purchase-order-edit-button secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Purchase Order
        </a>
    </header>

    @if ($errors->any())
        <div class="purchase-order-edit-alert" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>The draft was not saved.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.purchase-orders.draft.update', $purchaseOrder) }}"
        id="purchaseOrderEditForm">
        @csrf
        @method('PUT')

        <input
            type="hidden"
            name="version"
            value="{{ old('version', optional($purchaseOrder->updated_at)->getTimestamp() ?? 0) }}">

        <section class="purchase-order-edit-panel">
            <div class="purchase-order-edit-panel-heading">
                <span><i class="fa-solid fa-building"></i></span>
                <div>
                    <h2>Supplier and dates</h2>
                    <p>Changing the supplier applies its configured prices where available.</p>
                </div>
            </div>

            <div class="purchase-order-edit-grid">
                <div class="field supplier-field">
                    <label for="supplier_id">Supplier <b>*</b></label>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="">Select a supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option
                                value="{{ $supplier->id }}"
                                data-currency="{{ $supplier->currency ?: 'GBP' }}"
                                data-status="{{ $supplier->status }}"
                                @selected(
                                    (string) old('supplier_id', $purchaseOrder->supplier_id)
                                    === (string) $supplier->id
                                )>
                                {{ $supplier->company_name }}
                                {{ $supplier->supplier_code ? '— ' . $supplier->supplier_code : '' }}
                                {{ $supplier->is_preferred ? '— Preferred' : '' }}
                                {{ $supplier->status !== 'active' ? '— ' . ucfirst($supplier->status) : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="order_date">Order date <b>*</b></label>
                    <input
                        id="order_date"
                        type="date"
                        name="order_date"
                        value="{{ old('order_date', optional($purchaseOrder->order_date)->format('Y-m-d')) }}"
                        required>
                </div>

                <div class="field">
                    <label for="expected_date">Expected delivery</label>
                    <input
                        id="expected_date"
                        type="date"
                        name="expected_date"
                        value="{{ old('expected_date', optional($purchaseOrder->expected_date)->format('Y-m-d')) }}">
                </div>
            </div>
        </section>

        <section class="purchase-order-edit-panel">
            <div class="purchase-order-edit-panel-heading items-heading">
                <div class="heading-copy">
                    <span><i class="fa-solid fa-boxes-stacked"></i></span>
                    <div>
                        <h2>Purchase-order items</h2>
                        <p>Edit quantities and costs, remove lines, or add another inventory item.</p>
                    </div>
                </div>

                <div class="purchase-order-add-item">
                    <select id="purchaseOrderCatalogSelect">
                        <option value="">Choose product or variant</option>
                        @foreach ($catalogItems as $catalogItem)
                            <option value="{{ $catalogItem['identifier'] }}">
                                {{ $catalogItem['item_name'] }}
                                @if ($catalogItem['variant_name'])
                                    — {{ $catalogItem['variant_name'] }}
                                @endif
                                @if ($catalogItem['sku'])
                                    — {{ $catalogItem['sku'] }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="purchaseOrderAddItemButton">
                        <i class="fa-solid fa-plus"></i> Add Item
                    </button>
                </div>
            </div>

            <div id="supplierPricingMessage" class="supplier-pricing-message" hidden></div>

            <div class="purchase-order-edit-table-wrapper">
                <table class="purchase-order-edit-table">
                    <thead>
                        <tr>
                            <th>Product / Variant</th>
                            <th>SKU</th>
                            <th class="number">Quantity</th>
                            <th class="number">Unit Cost</th>
                            <th class="number">Line Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="purchaseOrderEditItems">
                        @foreach ($submittedItems as $index => $submittedItem)
                            @php
                                $identifier = $submittedItem['identifier'] ?? '';
                                $catalogItem = $catalogByIdentifier->get($identifier);
                                $existingItem = !empty($submittedItem['id'])
                                    ? $purchaseOrder->items->firstWhere(
                                        'id',
                                        (int) $submittedItem['id']
                                    )
                                    : null;
                                $itemName = $catalogItem['item_name']
                                    ?? $existingItem?->item_name
                                    ?? 'Unavailable product';
                                $variantName = $catalogItem['variant_name']
                                    ?? $existingItem?->variant_name;
                                $sku = $existingItem?->sku
                                    ?? $catalogItem['sku']
                                    ?? null;
                                $productId = $catalogItem['product_id']
                                    ?? $existingItem?->product_id;
                                $variantId = $catalogItem['product_variant_id']
                                    ?? $existingItem?->product_variant_id;
                                $generalCost = $catalogItem['unit_cost']
                                    ?? $submittedItem['unit_cost']
                                    ?? 0;
                            @endphp

                            <tr
                                data-edit-item-row
                                data-identifier="{{ $identifier }}"
                                data-product-id="{{ $productId }}"
                                data-variant-id="{{ $variantId }}"
                                data-general-cost="{{ $generalCost }}">
                                <td>
                                    <strong>{{ $itemName }}</strong>
                                    @if ($variantName)
                                        <small>{{ $variantName }}</small>
                                    @endif
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $submittedItem['id'] ?? '' }}">
                                    <input type="hidden" name="items[{{ $index }}][identifier]" value="{{ $identifier }}">
                                </td>
                                <td data-item-sku>{{ $sku ?: 'Not assigned' }}</td>
                                <td class="number">
                                    <input
                                        type="number"
                                        class="edit-item-quantity"
                                        name="items[{{ $index }}][quantity]"
                                        value="{{ $submittedItem['quantity'] ?? 1 }}"
                                        min="1"
                                        max="1000000"
                                        step="1"
                                        required>
                                </td>
                                <td class="number">
                                    <div class="money-input">
                                        <span data-currency-label>{{ old('currency', $purchaseOrder->currency) }}</span>
                                        <input
                                            type="number"
                                            class="edit-item-cost"
                                            name="items[{{ $index }}][unit_cost]"
                                            value="{{ $submittedItem['unit_cost'] ?? 0 }}"
                                            min="0"
                                            max="999999999999.99"
                                            step="0.01"
                                            required>
                                    </div>
                                </td>
                                <td class="number"><strong data-line-total>0.00</strong></td>
                                <td class="row-action">
                                    <button type="button" data-remove-item aria-label="Remove {{ $itemName }}">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="purchase-order-edit-bottom-grid">
            <section class="purchase-order-edit-panel notes-panel">
                <div class="purchase-order-edit-panel-heading">
                    <span><i class="fa-regular fa-note-sticky"></i></span>
                    <div><h2>Notes</h2><p>Supplier-facing and internal information.</p></div>
                </div>
                <div class="notes-grid">
                    <div class="field">
                        <label for="notes">Supplier notes</label>
                        <textarea id="notes" name="notes" rows="5" maxlength="5000">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                    </div>
                    <div class="field">
                        <label for="internal_notes">Internal notes</label>
                        <textarea id="internal_notes" name="internal_notes" rows="5" maxlength="5000">{{ old('internal_notes', $purchaseOrder->internal_notes) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="purchase-order-edit-panel totals-panel">
                <div class="purchase-order-edit-panel-heading">
                    <span><i class="fa-solid fa-calculator"></i></span>
                    <div><h2>Order totals</h2><p>Updated automatically from the item lines.</p></div>
                </div>

                <div class="totals-fields">
                    <div><label>Subtotal</label><strong data-subtotal>0.00</strong></div>
                    <div class="field inline"><label for="tax_amount">Tax</label><input id="tax_amount" type="number" name="tax_amount" value="{{ old('tax_amount', $purchaseOrder->tax_amount) }}" min="0" step="0.01"></div>
                    <div class="field inline"><label for="shipping_amount">Shipping</label><input id="shipping_amount" type="number" name="shipping_amount" value="{{ old('shipping_amount', $purchaseOrder->shipping_amount) }}" min="0" step="0.01"></div>
                    <div class="field inline"><label for="discount_amount">Discount</label><input id="discount_amount" type="number" name="discount_amount" value="{{ old('discount_amount', $purchaseOrder->discount_amount) }}" min="0" step="0.01"></div>
                    <div class="grand-total"><label>Grand total</label><strong data-grand-total>0.00</strong></div>
                </div>
            </section>
        </div>

        <footer class="purchase-order-edit-footer">
            <p><i class="fa-solid fa-shield-halved"></i> Saving keeps the order in Draft status.</p>
            <div>
                <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}" class="purchase-order-edit-button secondary">Cancel</a>
                <button type="submit" class="purchase-order-edit-button primary" id="purchaseOrderSaveButton">
                    <i class="fa-solid fa-floppy-disk"></i> Save Draft Changes
                </button>
            </div>
        </footer>
    </form>
</div>

@endsection

@push('page-styles')
<style>
    .purchase-order-edit-page{--blue:#2563eb;--navy:#172033;--muted:#64748b;--border:#e2e8f0;display:grid;gap:20px;color:var(--navy)}.purchase-order-edit-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:26px 28px;border-radius:20px;background:linear-gradient(135deg,#111827,#1d4ed8);color:#fff;box-shadow:0 18px 45px rgba(15,23,42,.15)}.purchase-order-edit-eyebrow{display:block;color:#bfdbfe;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.purchase-order-edit-title{display:flex;align-items:center;gap:11px;margin-top:6px}.purchase-order-edit-title h1{margin:0;font-size:28px}.purchase-order-edit-title span{padding:5px 9px;border-radius:999px;background:rgba(255,255,255,.14);font-size:11px;font-weight:800}.purchase-order-edit-header p{margin:7px 0 0;color:#dbeafe}.purchase-order-edit-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid transparent;border-radius:10px;padding:11px 16px;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}.purchase-order-edit-button.secondary{background:#fff;color:#1e3a5f;border-color:#dbe4f0}.purchase-order-edit-button.primary{background:var(--blue);color:#fff}.purchase-order-edit-alert{display:flex;gap:12px;padding:15px 18px;border:1px solid #fecdd3;border-radius:13px;background:#fff1f2;color:#be123c}.purchase-order-edit-alert ul{margin:7px 0 0;padding-left:19px}.purchase-order-edit-panel{overflow:hidden;border:1px solid var(--border);border-radius:17px;background:#fff;box-shadow:0 8px 26px rgba(15,23,42,.05)}#purchaseOrderEditForm{display:grid;gap:20px}.purchase-order-edit-panel-heading{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid var(--border)}.purchase-order-edit-panel-heading>span,.heading-copy>span{display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;border-radius:11px;background:#dbeafe;color:var(--blue)}.purchase-order-edit-panel-heading h2{margin:0;font-size:18px}.purchase-order-edit-panel-heading p{margin:4px 0 0;color:var(--muted);font-size:12px}.purchase-order-edit-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px;padding:20px}.field label{display:block;margin-bottom:7px;font-size:13px;font-weight:750}.field label b{color:#dc2626}.field input,.field select,.field textarea,.purchase-order-add-item select{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:10px;padding:10px 11px;background:#fff;color:var(--navy);font:inherit;outline:none}.field input:focus,.field select:focus,.field textarea:focus,.purchase-order-add-item select:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(37,99,235,.11)}.items-heading{justify-content:space-between}.heading-copy{display:flex;align-items:center;gap:12px}.purchase-order-add-item{display:flex;gap:8px}.purchase-order-add-item select{width:300px}.purchase-order-add-item button{border:0;border-radius:10px;padding:10px 13px;background:#172033;color:#fff;font:inherit;font-weight:750;cursor:pointer}.supplier-pricing-message{margin:14px 20px 0;padding:11px 13px;border:1px solid #bfdbfe;border-radius:10px;background:#eff6ff;color:#1d4ed8;font-size:13px;font-weight:700}.supplier-pricing-message.error{border-color:#fecdd3;background:#fff1f2;color:#be123c}.purchase-order-edit-table-wrapper{overflow-x:auto}.purchase-order-edit-table{width:100%;border-collapse:collapse;min-width:850px}.purchase-order-edit-table th{padding:12px 14px;background:#f8fafc;color:#64748b;font-size:11px;letter-spacing:.04em;text-align:left;text-transform:uppercase}.purchase-order-edit-table td{padding:14px;border-top:1px solid var(--border);vertical-align:middle}.purchase-order-edit-table td strong,.purchase-order-edit-table td small{display:block}.purchase-order-edit-table td small{margin-top:4px;color:var(--muted)}.purchase-order-edit-table .number{text-align:right}.purchase-order-edit-table input[type=number]{width:115px;border:1px solid #cbd5e1;border-radius:9px;padding:9px;text-align:right;font:inherit}.money-input{display:flex;align-items:center;justify-content:flex-end;gap:6px}.money-input span{color:var(--muted);font-size:11px;font-weight:800}.row-action button{display:grid;place-items:center;width:36px;height:36px;border:1px solid #fecdd3;border-radius:9px;background:#fff1f2;color:#be123c;cursor:pointer}.purchase-order-edit-bottom-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(330px,.65fr);gap:20px}.notes-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:20px}.totals-fields{padding:10px 20px 20px}.totals-fields>div{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}.field.inline{margin:0}.field.inline label{margin:0}.field.inline input{width:130px;text-align:right}.totals-fields .grand-total{margin-top:5px;border:0;color:#047857}.grand-total strong{font-size:22px}.purchase-order-edit-footer{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:17px 20px;border:1px solid var(--border);border-radius:15px;background:#fff}.purchase-order-edit-footer p{margin:0;color:var(--muted);font-size:13px}.purchase-order-edit-footer>div{display:flex;gap:9px}
    @media(max-width:1050px){.purchase-order-edit-grid,.purchase-order-edit-bottom-grid{grid-template-columns:1fr 1fr}.supplier-field{grid-column:1/-1}.purchase-order-edit-bottom-grid .notes-panel{grid-column:1/-1}}
    @media(max-width:720px){.purchase-order-edit-header,.items-heading,.purchase-order-edit-footer{align-items:stretch;flex-direction:column}.purchase-order-edit-grid,.purchase-order-edit-bottom-grid,.notes-grid{grid-template-columns:1fr}.supplier-field,.purchase-order-edit-bottom-grid .notes-panel{grid-column:auto}.purchase-order-add-item{flex-direction:column;width:100%}.purchase-order-add-item select{width:100%}.purchase-order-edit-footer>div{flex-direction:column}.purchase-order-edit-button{width:100%;box-sizing:border-box}}
</style>
@endpush

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const form = document.getElementById('purchaseOrderEditForm');
        const rowsContainer = document.getElementById('purchaseOrderEditItems');
        const catalogSelect = document.getElementById('purchaseOrderCatalogSelect');
        const addButton = document.getElementById('purchaseOrderAddItemButton');
        const supplierSelect = document.getElementById('supplier_id');
        const pricingMessage = document.getElementById('supplierPricingMessage');
        const saveButton = document.getElementById('purchaseOrderSaveButton');
        const taxInput = document.getElementById('tax_amount');
        const shippingInput = document.getElementById('shipping_amount');
        const discountInput = document.getElementById('discount_amount');
        const catalog = @json($catalogItems->values());
        const catalogMap = new Map(catalog.map(item => [item.identifier, item]));
        const pricingUrlTemplate = @json(
            route(
                'admin.suppliers.products.pricing',
                ['supplier' => '__SUPPLIER_ID__']
            )
        );
        let nextIndex = {{ $nextSubmittedItemIndex }};
        let currentPricing = {};
        let pricingRequest = 0;

        function numberValue(value) {
            const number = Number.parseFloat(value);
            return Number.isFinite(number) ? number : 0;
        }

        function currencyCode() {
            const option = supplierSelect?.options[supplierSelect.selectedIndex];
            return option?.dataset.currency || @json($purchaseOrder->currency ?: 'GBP');
        }

        function money(value) {
            return new Intl.NumberFormat('en-GB', {
                style: 'currency',
                currency: currencyCode()
            }).format(value);
        }

        function escapeHtml(value) {
            const element = document.createElement('div');
            element.textContent = String(value ?? '');
            return element.innerHTML;
        }

        function rows() {
            return Array.from(rowsContainer.querySelectorAll('[data-edit-item-row]'));
        }

        function updateTotals() {
            let subtotal = 0;

            rows().forEach(function (row) {
                const quantity = Math.max(0, numberValue(row.querySelector('.edit-item-quantity')?.value));
                const cost = Math.max(0, numberValue(row.querySelector('.edit-item-cost')?.value));
                const lineTotal = quantity * cost;
                subtotal += lineTotal;
                const lineElement = row.querySelector('[data-line-total]');
                if (lineElement) lineElement.textContent = money(lineTotal);
            });

            document.querySelectorAll('[data-subtotal]').forEach(
                element => element.textContent = money(subtotal)
            );

            const grandTotal = Math.max(
                0,
                subtotal
                    + Math.max(0, numberValue(taxInput?.value))
                    + Math.max(0, numberValue(shippingInput?.value))
                    - Math.max(0, numberValue(discountInput?.value))
            );

            document.querySelectorAll('[data-grand-total]').forEach(
                element => element.textContent = money(grandTotal)
            );

            document.querySelectorAll('[data-currency-label]').forEach(
                element => element.textContent = currencyCode()
            );
        }

        function bindRow(row) {
            row.querySelectorAll('.edit-item-quantity, .edit-item-cost').forEach(
                input => input.addEventListener('input', updateTotals)
            );

            row.querySelector('[data-remove-item]')?.addEventListener('click', function () {
                if (rows().length <= 1) {
                    window.alert('A purchase order must contain at least one item.');
                    return;
                }

                row.remove();
                updateTotals();
            });
        }

        function pricingFor(row) {
            const exactKey = row.dataset.variantId
                ? 'variant:' + row.dataset.variantId
                : '';
            const fallbackKey = row.dataset.productId
                ? 'product:' + row.dataset.productId
                : '';
            return (exactKey && currentPricing[exactKey])
                || (fallbackKey && currentPricing[fallbackKey])
                || null;
        }

        function applyPriceToRow(row) {
            const price = pricingFor(row);
            if (!price) return false;

            const costInput = row.querySelector('.edit-item-cost');
            const quantityInput = row.querySelector('.edit-item-quantity');
            const skuCell = row.querySelector('[data-item-sku]');

            if (costInput) costInput.value = Number(price.unit_cost || 0).toFixed(2);
            if (quantityInput) {
                quantityInput.value = Math.max(
                    Number.parseInt(quantityInput.value || '1', 10),
                    Number.parseInt(price.minimum_order_quantity || '1', 10)
                );
            }
            if (skuCell && price.supplier_sku) skuCell.textContent = price.supplier_sku;

            return true;
        }

        function addCatalogItem() {
            const item = catalogMap.get(catalogSelect.value);
            if (!item) return;

            if (rows().some(row => row.dataset.identifier === item.identifier)) {
                window.alert('That product or variant is already on this purchase order.');
                return;
            }

            const row = document.createElement('tr');
            row.dataset.editItemRow = '1';
            row.dataset.identifier = item.identifier;
            row.dataset.productId = item.product_id || '';
            row.dataset.variantId = item.product_variant_id || '';
            row.dataset.generalCost = item.unit_cost || 0;
            row.innerHTML = `
                <td>
                    <strong>${escapeHtml(item.item_name)}</strong>
                    ${item.variant_name ? `<small>${escapeHtml(item.variant_name)}</small>` : ''}
                    <input type="hidden" name="items[${nextIndex}][id]" value="">
                    <input type="hidden" name="items[${nextIndex}][identifier]" value="${escapeHtml(item.identifier)}">
                </td>
                <td data-item-sku>${escapeHtml(item.sku || 'Not assigned')}</td>
                <td class="number"><input type="number" class="edit-item-quantity" name="items[${nextIndex}][quantity]" value="1" min="1" max="1000000" step="1" required></td>
                <td class="number"><div class="money-input"><span data-currency-label>${escapeHtml(currencyCode())}</span><input type="number" class="edit-item-cost" name="items[${nextIndex}][unit_cost]" value="${Number(item.unit_cost || 0).toFixed(2)}" min="0" max="999999999999.99" step="0.01" required></div></td>
                <td class="number"><strong data-line-total>0.00</strong></td>
                <td class="row-action"><button type="button" data-remove-item aria-label="Remove item"><i class="fa-regular fa-trash-can"></i></button></td>`;

            rowsContainer.appendChild(row);
            nextIndex += 1;
            bindRow(row);
            applyPriceToRow(row);
            catalogSelect.value = '';
            updateTotals();
        }

        function showPricingMessage(message, error) {
            if (!pricingMessage) return;
            pricingMessage.hidden = false;
            pricingMessage.textContent = message;
            pricingMessage.classList.toggle('error', Boolean(error));
        }

        async function loadSupplierPricing() {
            const supplierId = supplierSelect?.value;
            const requestId = ++pricingRequest;
            currentPricing = {};

            if (!supplierId) {
                if (pricingMessage) pricingMessage.hidden = true;
                updateTotals();
                return;
            }

            rows().forEach(function (row) {
                const input = row.querySelector('.edit-item-cost');
                if (input) input.value = Number(row.dataset.generalCost || 0).toFixed(2);
            });

            showPricingMessage('Loading supplier-specific prices…', false);

            try {
                const response = await fetch(
                    pricingUrlTemplate.replace('__SUPPLIER_ID__', supplierId),
                    { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }
                );
                if (!response.ok) throw new Error('Pricing request failed');
                const payload = await response.json();
                if (requestId !== pricingRequest) return;

                currentPricing = payload.pricing || {};
                let applied = 0;
                rows().forEach(row => { if (applyPriceToRow(row)) applied += 1; });
                showPricingMessage(
                    applied > 0
                        ? applied + ' supplier-specific price(s) applied. Review all values before saving.'
                        : 'No configured supplier prices matched these items; general costs are shown.',
                    false
                );
                updateTotals();
            } catch (error) {
                if (requestId !== pricingRequest) return;
                showPricingMessage('Supplier pricing could not be loaded. Review the displayed costs before saving.', true);
                updateTotals();
            }
        }

        rows().forEach(bindRow);
        addButton?.addEventListener('click', addCatalogItem);
        supplierSelect?.addEventListener('change', loadSupplierPricing);
        [taxInput, shippingInput, discountInput].forEach(
            input => input?.addEventListener('input', updateTotals)
        );

        form?.addEventListener('submit', function (event) {
            if (rows().length === 0) {
                event.preventDefault();
                window.alert('Add at least one purchase-order item.');
                return;
            }

            if (saveButton) {
                saveButton.disabled = true;
                saveButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';
            }
        });

        updateTotals();
    });
</script>
@endpush
