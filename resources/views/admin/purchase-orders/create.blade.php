@extends('admin.layouts.app')

@section('title', 'Create Purchase Order')

@section('content')

<div class="purchase-order-create-page">

    <section class="purchase-order-header">

        <div>

            <span class="purchase-order-eyebrow">
                Inventory purchasing
            </span>

            <h1>
                Create Purchase Order
            </h1>

            <p>
                Review selected products and variants before creating
                the purchase order.
            </p>

        </div>

        <a
            href="{{ route('admin.reorder-dashboard.index') }}"
            class="purchase-order-back">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Reorder Dashboard
        </a>

    </section>

    <form
        method="POST"
        action="{{ route(
        'admin.purchase-orders.store'
    ) }}"
        id="purchaseOrderForm">

        @csrf

        <section class="purchase-order-details">

            <div class="purchase-order-field purchase-order-field-wide">

                <label for="supplier_id">

                    Supplier

                    <span class="purchase-required-mark">
                        *
                    </span>

                </label>

                <div class="purchase-supplier-select-wrapper">

                    <i class="fa-solid fa-truck-field"></i>

                    <select
                        id="supplier_id"
                        name="supplier_id"
                        required>

                        <option value="">
                            Select a supplier
                        </option>

                        @foreach ($suppliers as $supplier)

                        <option
                            value="{{ $supplier->id }}"
                            data-company="{{ $supplier->company_name }}"
                            data-code="{{ $supplier->supplier_code }}"
                            data-contact="{{ $supplier->contact_person }}"
                            data-email="{{ $supplier->email }}"
                            data-phone="{{ $supplier->phone ?: $supplier->alternate_phone }}"
                            data-address="{{ $supplier->full_address }}"
                            data-country="{{ $supplier->country }}"
                            data-currency="{{ $supplier->currency }}"
                            data-payment-terms="{{ $supplier->payment_terms }}"
                            data-lead-time="{{ $supplier->lead_time_days }}"
                            data-preferred="{{ $supplier->is_preferred ? '1' : '0' }}"
                            @selected(
                            (string) old('supplier_id')===(string) $supplier->id
                            )>

                            {{ $supplier->company_name }}

                            @if ($supplier->supplier_code)

                            — {{ $supplier->supplier_code }}

                            @endif

                            @if ($supplier->is_preferred)

                            — Preferred

                            @endif

                        </option>

                        @endforeach

                    </select>

                </div>

                @error('supplier_id')

                <small class="purchase-field-error">
                    {{ $message }}
                </small>

                @enderror

                @if ($suppliers->isEmpty())

                <div class="purchase-no-suppliers-warning">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>
                        No active suppliers are available.
                    </span>

                    <a
                        href="{{ route('admin.suppliers.create') }}"
                        target="_blank">

                        Add Supplier

                    </a>

                </div>

                @endif

            </div>

            <div
                class="purchase-supplier-preview purchase-order-field-wide"
                id="purchaseSupplierPreview"
                hidden>

                <div class="purchase-supplier-preview-header">

                    <span class="purchase-supplier-preview-icon">

                        <i class="fa-solid fa-building"></i>

                    </span>

                    <div>

                        <span>
                            Selected supplier
                        </span>

                        <strong id="purchaseSupplierCompany">
                            —
                        </strong>

                    </div>

                    <span
                        class="purchase-supplier-preferred"
                        id="purchaseSupplierPreferred"
                        hidden>

                        <i class="fa-solid fa-star"></i>

                        Preferred

                    </span>

                </div>

                <div class="purchase-supplier-preview-grid">

                    <div>

                        <span>
                            Supplier Code
                        </span>

                        <strong id="purchaseSupplierCode">
                            Not assigned
                        </strong>

                    </div>

                    <div>

                        <span>
                            Contact Person
                        </span>

                        <strong id="purchaseSupplierContact">
                            Not assigned
                        </strong>

                    </div>

                    <div>

                        <span>
                            Email
                        </span>

                        <strong id="purchaseSupplierEmail">
                            Not assigned
                        </strong>

                    </div>

                    <div>

                        <span>
                            Phone
                        </span>

                        <strong id="purchaseSupplierPhone">
                            Not assigned
                        </strong>

                    </div>

                    <div>

                        <span>
                            Currency
                        </span>

                        <strong id="purchaseSupplierCurrency">
                            GBP
                        </strong>

                    </div>

                    <div>

                        <span>
                            Payment Terms
                        </span>

                        <strong id="purchaseSupplierTerms">
                            Not configured
                        </strong>

                    </div>

                    <div>

                        <span>
                            Lead Time
                        </span>

                        <strong id="purchaseSupplierLeadTime">
                            Not configured
                        </strong>

                    </div>

                    <div>

                        <span>
                            Country
                        </span>

                        <strong id="purchaseSupplierCountry">
                            Not assigned
                        </strong>

                    </div>

                    <div class="purchase-supplier-preview-wide">

                        <span>
                            Address
                        </span>

                        <strong id="purchaseSupplierAddress">
                            Not assigned
                        </strong>

                    </div>

                    <input
                        type="hidden"
                        id="supplier_name"
                        name="supplier_name"
                        value="{{ old('supplier_name') }}">

                    <input
                        type="hidden"
                        id="supplier_email"
                        name="supplier_email"
                        value="{{ old('supplier_email') }}">

                    <input
                        type="hidden"
                        id="supplier_phone"
                        name="supplier_phone"
                        value="{{ old('supplier_phone') }}">

                    <input
                        type="hidden"
                        id="supplier_address"
                        name="supplier_address"
                        value="{{ old('supplier_address') }}">

                </div>

            </div>



            <div class="purchase-order-field">

                <label for="order_date">
                    Order Date
                </label>

                <input
                    type="date"
                    id="order_date"
                    name="order_date"
                    value="{{ old(
                        'order_date',
                        now()->toDateString()
                    ) }}">

            </div>

            <div class="purchase-order-field">

                <label for="expected_date">
                    Expected Delivery
                </label>

                <input
                    type="date"
                    id="expected_date"
                    name="expected_date"
                    value="{{ old('expected_date') }}">

            </div>

            <div class="purchase-order-field">

                <label for="supplier_phone">
                    Supplier Phone
                </label>

                <input
                    type="text"
                    id="supplier_phone"
                    name="supplier_phone"
                    value="{{ old('supplier_phone') }}"
                    placeholder="Supplier phone number">

            </div>

            <div class="purchase-order-field purchase-order-field-wide">

                <label for="supplier_address">
                    Supplier Address
                </label>

                <textarea
                    id="supplier_address"
                    name="supplier_address"
                    rows="3"
                    placeholder="Supplier billing or delivery address">{{ old('supplier_address') }}</textarea>

            </div>

            <div class="purchase-order-field">

                <label for="tax_amount">
                    Tax Amount
                </label>

                <div class="purchase-money-input">

                    <span>£</span>

                    <input
                        type="number"
                        id="tax_amount"
                        name="tax_amount"
                        value="{{ old('tax_amount', '0.00') }}"
                        min="0"
                        step="0.01">

                </div>

            </div>

            <div class="purchase-order-field">

                <label for="shipping_amount">
                    Shipping Amount
                </label>

                <div class="purchase-money-input">

                    <span>£</span>

                    <input
                        type="number"
                        id="shipping_amount"
                        name="shipping_amount"
                        value="{{ old('shipping_amount', '0.00') }}"
                        min="0"
                        step="0.01">

                </div>

            </div>

            <div class="purchase-order-field">

                <label for="discount_amount">
                    Discount Amount
                </label>

                <div class="purchase-money-input">

                    <span>£</span>

                    <input
                        type="number"
                        id="discount_amount"
                        name="discount_amount"
                        value="{{ old('discount_amount', '0.00') }}"
                        min="0"
                        step="0.01">

                </div>

            </div>

            <div class="purchase-order-field purchase-order-field-wide">

                <label for="notes">
                    Supplier Notes
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    placeholder="Notes that may appear on the purchase order">{{ old('notes') }}</textarea>

            </div>

            <div class="purchase-order-field purchase-order-field-wide">

                <label for="internal_notes">
                    Internal Notes
                </label>

                <textarea
                    id="internal_notes"
                    name="internal_notes"
                    rows="4"
                    placeholder="Private notes for administrators">{{ old('internal_notes') }}</textarea>

            </div>

        </section>

        <section class="purchase-order-panel">

            <div class="purchase-order-panel-header">

                <div>
                    <h2>
                        Selected Purchase Items
                    </h2>

                    <p>
                        Quantities and costs can be adjusted in the next step.
                    </p>
                </div>

                <div class="purchase-order-item-count">

                    <strong>
                        {{ number_format($selectedItems->count()) }}
                    </strong>

                    <span>
                        items
                    </span>

                </div>

            </div>

            <div class="purchase-order-table-wrapper">

                <table class="purchase-order-table">

                    <thead>

                        <tr>
                            <th>Product / Variant</th>
                            <th>SKU</th>
                            <th class="number-column">Current Stock</th>
                            <th class="number-column">Reorder Point</th>
                            <th class="number-column">Quantity</th>
                            <th class="number-column">Unit Cost</th>
                            <th class="number-column">Line Total</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($selectedItems as $index => $item)

                        <tr>

                            <td>

                                <strong>
                                    {{ $item['item_name'] }}
                                </strong>

                                @if ($item['categories'] !== '')

                                <span class="item-categories">
                                    {{ $item['categories'] }}
                                </span>

                                @endif

                                <input
                                    type="hidden"
                                    name="items[{{ $index }}][identifier]"
                                    value="{{ $item['identifier'] }}">

                                <input
                                    type="hidden"
                                    name="items[{{ $index }}][product_id]"
                                    value="{{ $item['product_id'] }}">

                                <input
                                    type="hidden"
                                    name="items[{{ $index }}][product_variant_id]"
                                    value="{{ $item['product_variant_id'] }}">

                            </td>

                            <td>
                                {{ $item['sku'] ?: 'Not assigned' }}
                            </td>

                            <td class="number-column">
                                {{ number_format($item['stock']) }}
                            </td>

                            <td class="number-column">
                                {{ number_format($item['reorder_point']) }}
                            </td>

                            <td class="number-column">

                                <input
                                    type="number"
                                    class="purchase-quantity-input"
                                    name="items[{{ $index }}][quantity]"
                                    value="{{ old(
            'items.' . $index . '.quantity',
            $item['quantity']
        ) }}"
                                    min="1"
                                    max="1000000"
                                    step="1"
                                    required>

                            </td>

                            <td class="number-column">

                                <div class="purchase-money-input">

                                    <span>
                                        £
                                    </span>

                                    <input
                                        type="number"
                                        class="purchase-unit-cost-input"
                                        name="items[{{ $index }}][unit_cost]"
                                        value="{{ old(
                'items.' . $index . '.unit_cost',
                number_format(
                    $item['unit_cost'],
                    2,
                    '.',
                    ''
                )
            ) }}"
                                        min="0"
                                        max="999999999999.99"
                                        step="0.01"
                                        required>

                                </div>

                            </td>

                            <td class="number-column">

                                <strong
                                    class="purchase-line-total"
                                    data-line-total>

                                    £{{ number_format($item['line_total'], 2) }}

                                </strong>

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                    <tfoot>

                        <tr>

                            <td colspan="4">
                                Purchase Order Totals
                            </td>

                            <td class="number-column">
                                {{ number_format($totalQuantity) }}
                            </td>

                            <td></td>

                            <td class="number-column">
                                £{{ number_format($subtotal, 2) }}
                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </section>

        <section class="purchase-order-footer">

            <div>

                <span>
                    Estimated subtotal
                </span>

                <div class="purchase-order-total-summary">

                    <div>
                        <span>
                            Subtotal
                        </span>

                        <strong data-purchase-subtotal>
                            £{{ number_format($subtotal, 2) }}
                        </strong>
                    </div>

                    <div>
                        <span>
                            Grand Total
                        </span>

                        <strong id="purchaseOrderGrandTotal">
                            £{{ number_format($subtotal, 2) }}
                        </strong>
                    </div>

                </div>

            </div>

            <button
                type="submit"
                id="createPurchaseOrderButton">

                <i class="fa-solid fa-file-circle-plus"></i>

                Create Purchase Order
            </button>

        </section>

    </form>

</div>

@endsection

@push('page-styles')

<style>
    .purchase-order-create-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        color: #111827;
    }

    .purchase-order-create-page *,
    .purchase-order-create-page *::before,
    .purchase-order-create-page *::after {
        box-sizing: border-box;
    }

    .purchase-order-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 22px;
        padding: 26px 28px;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background:
            radial-gradient(circle at top right,
                rgba(79, 70, 229, 0.13),
                transparent 38%),
            linear-gradient(135deg,
                #ffffff,
                #f8f9ff);
    }

    .purchase-order-eyebrow {
        display: block;
        margin-bottom: 6px;
        color: #4f46e5;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .purchase-order-header h1 {
        margin: 0 0 7px;
        font-size: 28px;
    }

    .purchase-order-header p {
        margin: 0;
        color: #64748b;
        font-size: 13px;
    }

    .purchase-order-back {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 15px;
        border: 1px solid #d7dce5;
        border-radius: 10px;
        background: #ffffff;
        color: #374151;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
        white-space: nowrap;
    }

    .purchase-order-details {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        padding: 21px;
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        background: #ffffff;
    }

    .purchase-order-field label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 12px;
        font-weight: 750;
    }

    .purchase-order-field input {
        width: 100%;
        height: 43px;
        padding: 0 12px;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        color: #111827;
        font-family: inherit;
        outline: none;
    }

    .purchase-order-field input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .purchase-order-panel {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #ffffff;
        overflow: hidden;
    }

    .purchase-order-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 23px;
        border-bottom: 1px solid #e5e7eb;
    }

    .purchase-order-panel-header h2 {
        margin: 0 0 5px;
        font-size: 19px;
    }

    .purchase-order-panel-header p {
        margin: 0;
        color: #64748b;
        font-size: 11px;
    }

    .purchase-order-item-count {
        min-width: 75px;
        padding: 9px 12px;
        border-radius: 10px;
        background: #eef2ff;
        color: #4f46e5;
        text-align: center;
    }

    .purchase-order-item-count strong,
    .purchase-order-item-count span {
        display: block;
    }

    .purchase-order-item-count strong {
        font-size: 18px;
    }

    .purchase-order-item-count span {
        font-size: 9px;
        font-weight: 750;
        text-transform: uppercase;
    }

    .purchase-order-table-wrapper {
        overflow-x: auto;
    }

    .purchase-order-table {
        width: 100%;
        min-width: 950px;
        border-collapse: collapse;
    }

    .purchase-order-table th,
    .purchase-order-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #eef2f7;
        font-size: 12px;
        text-align: left;
    }

    .purchase-order-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .purchase-order-table .number-column {
        text-align: right;
        white-space: nowrap;
    }

    .item-categories {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 10px;
    }

    .purchase-order-table tfoot td {
        border-bottom: 0;
        background: #f8fafc;
        font-weight: 800;
    }

    .purchase-order-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 25px;
        padding: 19px 22px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
    }

    .purchase-order-footer span,
    .purchase-order-footer strong {
        display: block;
        text-align: right;
    }

    .purchase-order-footer span {
        margin-bottom: 3px;
        color: #64748b;
        font-size: 10px;
    }

    .purchase-order-footer strong {
        font-size: 21px;
    }

    .purchase-order-footer button {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 17px;
        border: 0;
        border-radius: 10px;
        background: #cbd5e1;
        color: #ffffff;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: not-allowed;
    }

    .purchase-order-footer button {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 17px;
        border: 1px solid #4f46e5;
        border-radius: 10px;
        background: #4f46e5;
        color: #ffffff;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .purchase-order-footer button:hover {
        border-color: #4338ca;
        background: #4338ca;
        transform: translateY(-1px);
    }

    .purchase-order-field-wide {
        grid-column: span 2;
    }

    .purchase-order-field textarea {
        width: 100%;
        min-height: 95px;
        padding: 11px 12px;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        color: #111827;
        font-family: inherit;
        resize: vertical;
        outline: none;
    }

    .purchase-order-field textarea:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .purchase-quantity-input {
        width: 90px;
        height: 38px;
        padding: 0 9px;
        border: 1px solid #d7dce5;
        border-radius: 8px;
        background: #ffffff;
        text-align: right;
    }

    .purchase-money-input {
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        border: 1px solid #d7dce5;
        border-radius: 8px;
        background: #ffffff;
        overflow: hidden;
    }

    .purchase-money-input span {
        align-self: stretch;
        display: inline-flex;
        align-items: center;
        padding: 0 9px;
        border-right: 1px solid #e5e7eb;
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
    }

    .purchase-money-input input {
        width: 105px;
        height: 36px;
        padding: 0 9px;
        border: 0;
        outline: none;
        text-align: right;
    }

    .purchase-line-total {
        color: #15803d;
    }

    .purchase-required-mark {
        color: #b91c1c;
    }

    .purchase-supplier-select-wrapper {
        position: relative;
    }

    .purchase-supplier-select-wrapper>i {
        position: absolute;
        top: 50%;
        left: 13px;
        z-index: 1;
        color: #94a3b8;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .purchase-supplier-select-wrapper select {
        width: 100%;
        height: 45px;
        padding: 0 38px;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        color: #111827;
        font-family: inherit;
        outline: none;
    }

    .purchase-supplier-select-wrapper select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .purchase-field-error {
        display: block;
        margin-top: 6px;
        color: #b91c1c;
        font-size: 9px;
        font-weight: 700;
    }

    .purchase-no-suppliers-warning {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 8px;
        padding: 10px 11px;
        border: 1px solid #fed7aa;
        border-radius: 9px;
        background: #fff7ed;
        color: #c2410c;
        font-size: 9px;
        font-weight: 700;
    }

    .purchase-no-suppliers-warning a {
        margin-left: auto;
        color: #c2410c;
        font-weight: 800;
    }

    .purchase-supplier-preview {
        padding: 16px;
        border: 1px solid #c7d2fe;
        border-radius: 12px;
        background:
            linear-gradient(135deg,
                #ffffff,
                #f8f9ff);
    }

    .purchase-supplier-preview-header {
        display: flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 14px;
        padding-bottom: 13px;
        border-bottom: 1px solid #e0e7ff;
    }

    .purchase-supplier-preview-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .purchase-supplier-preview-header span,
    .purchase-supplier-preview-header strong {
        display: block;
    }

    .purchase-supplier-preview-header>div>span {
        margin-bottom: 3px;
        color: #64748b;
        font-size: 8px;
    }

    .purchase-supplier-preview-header>div>strong {
        color: #111827;
        font-size: 12px;
    }

    .purchase-supplier-preferred {
        display: inline-flex !important;
        align-items: center;
        gap: 5px;
        margin-left: auto;
        padding: 5px 8px;
        border-radius: 999px;
        background: #fefce8;
        color: #a16207;
        font-size: 8px;
        font-weight: 800;
    }

    .purchase-supplier-preview-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .purchase-supplier-preview-grid>div {
        min-width: 0;
        padding: 10px;
        border-radius: 8px;
        background: #ffffff;
    }

    .purchase-supplier-preview-grid span,
    .purchase-supplier-preview-grid strong {
        display: block;
    }

    .purchase-supplier-preview-grid span {
        margin-bottom: 4px;
        color: #64748b;
        font-size: 8px;
    }

    .purchase-supplier-preview-grid strong {
        color: #111827;
        font-size: 9px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .purchase-supplier-preview-wide {
        grid-column: 1 / -1;
    }


    @media (max-width: 1050px) {
        .purchase-order-details {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 950px) {
        .purchase-supplier-preview-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .purchase-order-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .purchase-order-back {
            width: 100%;
        }

        .purchase-order-details {
            grid-template-columns: 1fr;
        }

        .purchase-order-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .purchase-order-footer span,
        .purchase-order-footer strong {
            text-align: left;
        }

        .purchase-order-footer button {
            width: 100%;
        }

        .purchase-order-field-wide {
            grid-column: auto;
        }

        .purchase-supplier-preview-grid {
            grid-template-columns: 1fr;
        }

        .purchase-supplier-preview-wide {
            grid-column: auto;
        }
    }
</style>

@endpush
@push('page-scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {
            'use strict';

            const quantityInputs = Array.from(
                document.querySelectorAll(
                    '.purchase-quantity-input'
                )
            );

            const costInputs = Array.from(
                document.querySelectorAll(
                    '.purchase-unit-cost-input'
                )
            );

            const taxInput = document.getElementById(
                'tax_amount'
            );

            const shippingInput = document.getElementById(
                'shipping_amount'
            );

            const discountInput = document.getElementById(
                'discount_amount'
            );

            const subtotalElements =
                document.querySelectorAll(
                    '[data-purchase-subtotal]'
                );

            const totalElement =
                document.getElementById(
                    'purchaseOrderGrandTotal'
                );

            function numberValue(value) {
                const parsed = Number.parseFloat(
                    value
                );

                return Number.isFinite(parsed) ?
                    parsed :
                    0;
            }

            function formatCurrency(value) {
                return new Intl.NumberFormat(
                    'en-GB', {
                        style: 'currency',
                        currency: 'GBP'
                    }
                ).format(value);
            }

            function updateTotals() {
                let subtotal = 0;

                quantityInputs.forEach(
                    function(quantityInput, index) {
                        const costInput =
                            costInputs[index];

                        const row =
                            quantityInput.closest('tr');

                        const lineTotalElement =
                            row ?
                            row.querySelector(
                                '[data-line-total]'
                            ) :
                            null;

                        const quantity = Math.max(
                            0,
                            numberValue(
                                quantityInput.value
                            )
                        );

                        const unitCost = Math.max(
                            0,
                            numberValue(
                                costInput ?
                                costInput.value :
                                0
                            )
                        );

                        const lineTotal =
                            quantity * unitCost;

                        subtotal += lineTotal;

                        if (lineTotalElement) {
                            lineTotalElement.textContent =
                                formatCurrency(
                                    lineTotal
                                );
                        }
                    }
                );

                subtotalElements.forEach(
                    function(element) {
                        element.textContent =
                            formatCurrency(
                                subtotal
                            );
                    }
                );

                const tax = Math.max(
                    0,
                    numberValue(
                        taxInput ?
                        taxInput.value :
                        0
                    )
                );

                const shipping = Math.max(
                    0,
                    numberValue(
                        shippingInput ?
                        shippingInput.value :
                        0
                    )
                );

                const discount = Math.max(
                    0,
                    numberValue(
                        discountInput ?
                        discountInput.value :
                        0
                    )
                );

                const grandTotal = Math.max(
                    0,
                    subtotal +
                    tax +
                    shipping -
                    discount
                );

                if (totalElement) {
                    totalElement.textContent =
                        formatCurrency(
                            grandTotal
                        );
                }
            }

            [
                ...quantityInputs,
                ...costInputs,
                taxInput,
                shippingInput,
                discountInput
            ].forEach(function(input) {
                if (!input) {
                    return;
                }

                input.addEventListener(
                    'input',
                    updateTotals
                );
            });


            /*
|--------------------------------------------------------------------------
| Supplier selection
|--------------------------------------------------------------------------
*/

            const supplierSelect = document.getElementById(
                'supplier_id'
            );

            const supplierPreview = document.getElementById(
                'purchaseSupplierPreview'
            );

            const supplierCompany = document.getElementById(
                'purchaseSupplierCompany'
            );

            const supplierCode = document.getElementById(
                'purchaseSupplierCode'
            );

            const supplierContact = document.getElementById(
                'purchaseSupplierContact'
            );

            const supplierEmailPreview = document.getElementById(
                'purchaseSupplierEmail'
            );

            const supplierPhonePreview = document.getElementById(
                'purchaseSupplierPhone'
            );

            const supplierCurrencyPreview = document.getElementById(
                'purchaseSupplierCurrency'
            );

            const supplierTerms = document.getElementById(
                'purchaseSupplierTerms'
            );

            const supplierLeadTime = document.getElementById(
                'purchaseSupplierLeadTime'
            );

            const supplierCountry = document.getElementById(
                'purchaseSupplierCountry'
            );

            const supplierAddressPreview = document.getElementById(
                'purchaseSupplierAddress'
            );

            const supplierPreferred = document.getElementById(
                'purchaseSupplierPreferred'
            );

            const supplierNameInput = document.getElementById(
                'supplier_name'
            );

            const supplierEmailInput = document.getElementById(
                'supplier_email'
            );

            const supplierPhoneInput = document.getElementById(
                'supplier_phone'
            );

            const supplierAddressInput = document.getElementById(
                'supplier_address'
            );

            function supplierValue(value, fallback) {
                const cleaned = String(
                    value || ''
                ).trim();

                return cleaned !== '' ?
                    cleaned :
                    fallback;
            }

            function updateSupplierPreview() {
                if (!supplierSelect) {
                    return;
                }

                const option =
                    supplierSelect.options[
                        supplierSelect.selectedIndex
                    ];

                if (
                    !option ||
                    option.value === ''
                ) {
                    if (supplierPreview) {
                        supplierPreview.hidden = true;
                    }

                    if (supplierNameInput) {
                        supplierNameInput.value = '';
                    }

                    if (supplierEmailInput) {
                        supplierEmailInput.value = '';
                    }

                    if (supplierPhoneInput) {
                        supplierPhoneInput.value = '';
                    }

                    if (supplierAddressInput) {
                        supplierAddressInput.value = '';
                    }

                    return;
                }

                const dataset = option.dataset;

                if (supplierPreview) {
                    supplierPreview.hidden = false;
                }

                if (supplierCompany) {
                    supplierCompany.textContent =
                        supplierValue(
                            dataset.company,
                            'Not assigned'
                        );
                }

                if (supplierCode) {
                    supplierCode.textContent =
                        supplierValue(
                            dataset.code,
                            'Not assigned'
                        );
                }

                if (supplierContact) {
                    supplierContact.textContent =
                        supplierValue(
                            dataset.contact,
                            'Not assigned'
                        );
                }

                if (supplierEmailPreview) {
                    supplierEmailPreview.textContent =
                        supplierValue(
                            dataset.email,
                            'Not assigned'
                        );
                }

                if (supplierPhonePreview) {
                    supplierPhonePreview.textContent =
                        supplierValue(
                            dataset.phone,
                            'Not assigned'
                        );
                }

                if (supplierCurrencyPreview) {
                    supplierCurrencyPreview.textContent =
                        supplierValue(
                            dataset.currency,
                            'GBP'
                        );
                }

                if (supplierTerms) {
                    supplierTerms.textContent =
                        supplierValue(
                            dataset.paymentTerms,
                            'Not configured'
                        );
                }

                if (supplierLeadTime) {
                    const leadTime =
                        supplierValue(
                            dataset.leadTime,
                            ''
                        );

                    supplierLeadTime.textContent =
                        leadTime !== '' ?
                        Number(leadTime)
                        .toLocaleString('en-GB') +
                        ' days' :
                        'Not configured';
                }

                if (supplierCountry) {
                    supplierCountry.textContent =
                        supplierValue(
                            dataset.country,
                            'Not assigned'
                        );
                }

                if (supplierAddressPreview) {
                    supplierAddressPreview.textContent =
                        supplierValue(
                            dataset.address,
                            'Not assigned'
                        );
                }

                if (supplierPreferred) {
                    supplierPreferred.hidden =
                        dataset.preferred !== '1';
                }

                if (supplierNameInput) {
                    supplierNameInput.value =
                        dataset.company || '';
                }

                if (supplierEmailInput) {
                    supplierEmailInput.value =
                        dataset.email || '';
                }

                if (supplierPhoneInput) {
                    supplierPhoneInput.value =
                        dataset.phone || '';
                }

                if (supplierAddressInput) {
                    supplierAddressInput.value =
                        dataset.address || '';
                }
            }

            if (supplierSelect) {
                supplierSelect.addEventListener(
                    'change',
                    updateSupplierPreview
                );
            }

            updateSupplierPreview();
            updateTotals();
        }


    );
</script>

@endpush