@extends('admin.layouts.app')

@section('title', 'Supplier Products — ' . $supplier->company_name)

@section('content')

<div class="supplier-products-page">

    <header class="supplier-products-header">
        <div>
            <span class="supplier-products-eyebrow">Supplier catalogue</span>
            <h1>{{ $supplier->company_name }}</h1>
            <p>Manage supplier SKUs, costs, minimum quantities, and lead times.</p>
        </div>

        <a
            href="{{ route('admin.suppliers.show', $supplier) }}"
            class="supplier-products-button secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Supplier Profile
        </a>
    </header>

    @if (session('success'))
        <div class="supplier-products-alert success">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="supplier-products-alert error" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <section class="supplier-products-stats">
        <article>
            <span class="icon blue"><i class="fa-solid fa-boxes-stacked"></i></span>
            <div><small>Total assignments</small><strong>{{ number_format($stats['total']) }}</strong></div>
        </article>
        <article>
            <span class="icon green"><i class="fa-solid fa-circle-check"></i></span>
            <div><small>Active prices</small><strong>{{ number_format($stats['active']) }}</strong></div>
        </article>
        <article>
            <span class="icon amber"><i class="fa-solid fa-star"></i></span>
            <div><small>Preferred items</small><strong>{{ number_format($stats['preferred']) }}</strong></div>
        </article>
        <article>
            <span class="icon purple"><i class="fa-solid fa-coins"></i></span>
            <div><small>Average cost</small><strong>{{ $supplier->currency }} {{ number_format($stats['average_cost'], 2) }}</strong></div>
        </article>
    </section>

    <section class="supplier-products-panel">
        <div class="supplier-products-panel-heading">
            <div>
                <span class="panel-icon"><i class="fa-solid fa-link"></i></span>
                <div>
                    <h2>Assign a product</h2>
                    <p>A product-level entry acts as the default for all its variants.</p>
                </div>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.suppliers.products.store', $supplier) }}"
            class="supplier-products-form"
            id="supplierProductCreateForm">
            @csrf

            <div class="field wide">
                <label for="supplier_product_id">Product <span>*</span></label>
                <select id="supplier_product_id" name="product_id" required>
                    <option value="">Select a product</option>
                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-base-cost="{{ $product->cost_price ?? 0 }}"
                            @selected((string) old('product_id') === (string) $product->id)>
                            {{ $product->title }}{{ $product->sku ? ' — ' . $product->sku : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field wide">
                <label for="supplier_product_variant_id">Variant</label>
                <select
                    id="supplier_product_variant_id"
                    name="product_variant_id"
                    data-old-value="{{ old('product_variant_id') }}">
                    <option value="">Default product price / all variants</option>
                    @foreach ($products as $product)
                        @foreach ($product->variants as $variant)
                            <option
                                value="{{ $variant->id }}"
                                data-product-id="{{ $product->id }}"
                                disabled>
                                {{ $variant->sku ?: 'Variant #' . $variant->id }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
                <small id="supplierVariantHelp">Choose a product first.</small>
            </div>

            <div class="field">
                <label for="supplier_sku">Supplier SKU</label>
                <input
                    id="supplier_sku"
                    name="supplier_sku"
                    value="{{ old('supplier_sku') }}"
                    maxlength="120"
                    placeholder="Example: SUP-BLK-001">
            </div>

            <div class="field">
                <label for="supplier_unit_cost">Unit cost ({{ $supplier->currency }}) <span>*</span></label>
                <input
                    id="supplier_unit_cost"
                    type="number"
                    name="unit_cost"
                    value="{{ old('unit_cost') }}"
                    min="0"
                    max="999999999999.99"
                    step="0.01"
                    required>
            </div>

            <div class="field">
                <label for="minimum_order_quantity">Minimum order quantity <span>*</span></label>
                <input
                    id="minimum_order_quantity"
                    type="number"
                    name="minimum_order_quantity"
                    value="{{ old('minimum_order_quantity', 1) }}"
                    min="1"
                    max="1000000"
                    step="1"
                    required>
            </div>

            <div class="field">
                <label for="supplier_lead_time_days">Lead time (days)</label>
                <input
                    id="supplier_lead_time_days"
                    type="number"
                    name="lead_time_days"
                    value="{{ old('lead_time_days', $supplier->lead_time_days) }}"
                    min="0"
                    max="3650"
                    step="1">
            </div>

            <div class="field full">
                <label for="supplier_product_notes">Notes</label>
                <textarea
                    id="supplier_product_notes"
                    name="notes"
                    rows="3"
                    maxlength="5000"
                    placeholder="Packaging, ordering, or price-agreement notes">{{ old('notes') }}</textarea>
            </div>

            <div class="supplier-products-checks full">
                <label>
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                    <span><strong>Active price</strong><small>Allow this price to be used on new purchase orders.</small></span>
                </label>
                <label>
                    <input type="hidden" name="is_preferred" value="0">
                    <input type="checkbox" name="is_preferred" value="1" @checked(old('is_preferred'))>
                    <span><strong>Preferred supplier</strong><small>Make this the preferred supplier for this exact product or variant.</small></span>
                </label>
            </div>

            <div class="supplier-products-form-actions full">
                <button type="submit" class="supplier-products-button primary">
                    <i class="fa-solid fa-plus"></i>
                    Assign Product
                </button>
            </div>
        </form>
    </section>

    <section class="supplier-products-panel">
        <div class="supplier-products-panel-heading list-heading">
            <div>
                <span class="panel-icon"><i class="fa-solid fa-list-check"></i></span>
                <div>
                    <h2>Assigned products</h2>
                    <p>These prices automatically feed into new purchase orders.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.suppliers.products.index', $supplier) }}" class="supplier-products-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input name="search" value="{{ $search }}" placeholder="Search product or SKU">
                <button type="submit">Search</button>
                @if ($search !== '')
                    <a href="{{ route('admin.suppliers.products.index', $supplier) }}">Clear</a>
                @endif
            </form>
        </div>

        @forelse ($assignments as $assignment)
            <article class="supplier-product-card">
                <div class="supplier-product-main">
                    <div class="supplier-product-title">
                        <span class="product-avatar"><i class="fa-solid fa-box"></i></span>
                        <div>
                            <h3>{{ $assignment->product?->title ?: 'Deleted product' }}</h3>
                            <p>
                                @if ($assignment->variant)
                                    Variant: {{ $assignment->variant->sku ?: '#' . $assignment->variant->id }}
                                @else
                                    Default product price
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="supplier-product-badges">
                        <span class="badge {{ $assignment->is_active ? 'active' : 'inactive' }}">
                            {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if ($assignment->is_preferred)
                            <span class="badge preferred"><i class="fa-solid fa-star"></i> Preferred</span>
                        @endif
                    </div>
                </div>

                <div class="supplier-product-details">
                    <div><small>Supplier SKU</small><strong>{{ $assignment->supplier_sku ?: 'Not assigned' }}</strong></div>
                    <div><small>Unit cost</small><strong>{{ $supplier->currency }} {{ number_format((float) $assignment->unit_cost, 2) }}</strong></div>
                    <div><small>Minimum quantity</small><strong>{{ number_format($assignment->minimum_order_quantity) }}</strong></div>
                    <div><small>Lead time</small><strong>{{ $assignment->lead_time_days !== null ? $assignment->lead_time_days . ' days' : 'Supplier default' }}</strong></div>
                </div>

                <div class="supplier-product-actions">
                    <details>
                        <summary><i class="fa-regular fa-pen-to-square"></i> Edit pricing</summary>
                        <form
                            method="POST"
                            action="{{ route('admin.suppliers.products.update', [$supplier, $assignment]) }}"
                            class="supplier-product-edit-form">
                            @csrf
                            @method('PUT')

                            <div class="field">
                                <label>Supplier SKU</label>
                                <input name="supplier_sku" value="{{ $assignment->supplier_sku }}" maxlength="120">
                            </div>
                            <div class="field">
                                <label>Unit cost</label>
                                <input type="number" name="unit_cost" value="{{ number_format((float) $assignment->unit_cost, 2, '.', '') }}" min="0" step="0.01" required>
                            </div>
                            <div class="field">
                                <label>Minimum quantity</label>
                                <input type="number" name="minimum_order_quantity" value="{{ $assignment->minimum_order_quantity }}" min="1" step="1" required>
                            </div>
                            <div class="field">
                                <label>Lead time (days)</label>
                                <input type="number" name="lead_time_days" value="{{ $assignment->lead_time_days }}" min="0" step="1">
                            </div>
                            <div class="field full">
                                <label>Notes</label>
                                <textarea name="notes" rows="2" maxlength="5000">{{ $assignment->notes }}</textarea>
                            </div>
                            <div class="supplier-products-checks full compact">
                                <label>
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($assignment->is_active)>
                                    <span><strong>Active price</strong></span>
                                </label>
                                <label>
                                    <input type="hidden" name="is_preferred" value="0">
                                    <input type="checkbox" name="is_preferred" value="1" @checked($assignment->is_preferred)>
                                    <span><strong>Preferred supplier</strong></span>
                                </label>
                            </div>
                            <div class="full">
                                <button class="supplier-products-button primary" type="submit">
                                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </details>

                    <form
                        method="POST"
                        action="{{ route('admin.suppliers.products.destroy', [$supplier, $assignment]) }}"
                        onsubmit="return confirm('Remove this supplier product assignment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="supplier-products-button danger">
                            <i class="fa-regular fa-trash-can"></i> Remove
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="supplier-products-empty">
                <i class="fa-solid fa-box-open"></i>
                <h3>No supplier products found</h3>
                <p>Assign the first product using the form above.</p>
            </div>
        @endforelse

        @if ($assignments->hasPages())
            <div class="supplier-products-pagination">{{ $assignments->links() }}</div>
        @endif
    </section>
</div>

@endsection

@push('page-styles')
<style>
    .supplier-products-page{display:grid;gap:22px;color:#172033}.supplier-products-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:28px;border-radius:20px;background:linear-gradient(135deg,#111827,#1e3a5f);color:#fff;box-shadow:0 18px 45px rgba(15,23,42,.14)}.supplier-products-eyebrow{display:block;margin-bottom:7px;color:#93c5fd;font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.supplier-products-header h1{margin:0;font-size:30px}.supplier-products-header p{margin:8px 0 0;color:#cbd5e1}.supplier-products-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:10px;padding:11px 16px;font:inherit;font-weight:750;text-decoration:none;cursor:pointer}.supplier-products-button.secondary{background:#fff;color:#1e3a5f}.supplier-products-button.primary{background:#2563eb;color:#fff}.supplier-products-button.danger{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}.supplier-products-alert{display:flex;align-items:flex-start;gap:12px;padding:15px 18px;border-radius:13px}.supplier-products-alert.success{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0}.supplier-products-alert.error{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}.supplier-products-alert ul{margin:7px 0 0;padding-left:19px}.supplier-products-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.supplier-products-stats article{display:flex;align-items:center;gap:13px;padding:18px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.05)}.supplier-products-stats .icon,.panel-icon,.product-avatar{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:12px}.icon.blue,.panel-icon{background:#dbeafe;color:#2563eb}.icon.green{background:#d1fae5;color:#059669}.icon.amber{background:#fef3c7;color:#d97706}.icon.purple{background:#ede9fe;color:#7c3aed}.supplier-products-stats small,.supplier-product-details small{display:block;color:#64748b;font-size:12px}.supplier-products-stats strong{display:block;margin-top:3px;font-size:20px}.supplier-products-panel{overflow:hidden;background:#fff;border:1px solid #e2e8f0;border-radius:18px;box-shadow:0 10px 30px rgba(15,23,42,.05)}.supplier-products-panel-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:20px 22px;border-bottom:1px solid #e2e8f0}.supplier-products-panel-heading>div{display:flex;align-items:center;gap:13px}.supplier-products-panel-heading h2{margin:0;font-size:19px}.supplier-products-panel-heading p{margin:4px 0 0;color:#64748b;font-size:13px}.supplier-products-form,.supplier-product-edit-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;padding:22px}.supplier-products-form .wide{grid-column:span 2}.field.full,.supplier-products-checks.full,.supplier-products-form-actions.full,.supplier-product-edit-form .full{grid-column:1/-1}.field label{display:block;margin-bottom:7px;font-size:13px;font-weight:750}.field label span{color:#dc2626}.field input,.field select,.field textarea,.supplier-products-search input{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:10px;padding:11px 12px;background:#fff;color:#172033;font:inherit;outline:none}.field input:focus,.field select:focus,.field textarea:focus,.supplier-products-search input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.11)}.field small{display:block;margin-top:6px;color:#64748b}.supplier-products-checks{display:flex;flex-wrap:wrap;gap:14px}.supplier-products-checks>label{display:flex;align-items:flex-start;gap:10px;min-width:250px;padding:13px;border:1px solid #e2e8f0;border-radius:11px;cursor:pointer}.supplier-products-checks input[type=checkbox]{margin-top:3px}.supplier-products-checks span strong,.supplier-products-checks span small{display:block}.supplier-products-checks span small{margin-top:3px;color:#64748b}.supplier-products-search{display:flex;align-items:center;gap:8px}.supplier-products-search input{width:230px;padding-left:35px}.supplier-products-search i{position:absolute;margin-left:12px;color:#94a3b8}.supplier-products-search button{border:0;border-radius:9px;padding:10px 13px;background:#1e293b;color:#fff;cursor:pointer}.supplier-products-search a{color:#2563eb;text-decoration:none}.supplier-product-card{margin:18px;border:1px solid #e2e8f0;border-radius:14px}.supplier-product-main{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:17px}.supplier-product-title{display:flex;align-items:center;gap:12px}.product-avatar{background:#f1f5f9;color:#475569}.supplier-product-title h3{margin:0;font-size:16px}.supplier-product-title p{margin:4px 0 0;color:#64748b;font-size:13px}.supplier-product-badges{display:flex;flex-wrap:wrap;gap:7px}.badge{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:5px 9px;font-size:11px;font-weight:800}.badge.active{background:#d1fae5;color:#047857}.badge.inactive{background:#f1f5f9;color:#64748b}.badge.preferred{background:#fef3c7;color:#b45309}.supplier-product-details{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;background:#f8fafc}.supplier-product-details>div{padding:14px 17px;border-right:1px solid #e2e8f0}.supplier-product-details>div:last-child{border-right:0}.supplier-product-details strong{display:block;margin-top:4px}.supplier-product-actions{display:flex;align-items:flex-start;justify-content:flex-end;gap:10px;padding:13px 17px}.supplier-product-actions details{flex:1}.supplier-product-actions summary{display:inline-flex;align-items:center;gap:7px;padding:10px 14px;border-radius:9px;background:#eff6ff;color:#1d4ed8;font-weight:750;cursor:pointer;list-style:none}.supplier-product-edit-form{margin-top:13px;padding:17px;background:#f8fafc;border-radius:12px}.supplier-products-checks.compact>label{min-width:180px;padding:9px}.supplier-products-empty{text-align:center;padding:55px 20px;color:#64748b}.supplier-products-empty i{font-size:38px;color:#cbd5e1}.supplier-products-empty h3{margin:14px 0 5px;color:#334155}.supplier-products-empty p{margin:0}.supplier-products-pagination{padding:0 20px 20px}
    @media(max-width:1000px){.supplier-products-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.supplier-products-form,.supplier-product-edit-form{grid-template-columns:repeat(2,minmax(0,1fr))}.supplier-product-details{grid-template-columns:repeat(2,minmax(0,1fr))}.supplier-product-details>div:nth-child(2){border-right:0}}
    @media(max-width:680px){.supplier-products-header,.supplier-products-panel-heading.list-heading,.supplier-product-main,.supplier-product-actions{align-items:stretch;flex-direction:column}.supplier-products-stats,.supplier-products-form,.supplier-product-edit-form,.supplier-product-details{grid-template-columns:1fr}.supplier-products-form .wide{grid-column:auto}.supplier-product-details>div{border-right:0;border-bottom:1px solid #e2e8f0}.supplier-products-search{width:100%;flex-wrap:wrap}.supplier-products-search input{flex:1;width:auto}.supplier-products-button{width:100%;box-sizing:border-box}}
</style>
@endpush

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const productSelect = document.getElementById('supplier_product_id');
        const variantSelect = document.getElementById('supplier_product_variant_id');
        const unitCostInput = document.getElementById('supplier_unit_cost');
        const variantHelp = document.getElementById('supplierVariantHelp');

        if (!productSelect || !variantSelect) {
            return;
        }

        function updateVariants(useOldValue) {
            const productId = productSelect.value;
            const oldValue = useOldValue ? variantSelect.dataset.oldValue : '';
            let available = 0;

            Array.from(variantSelect.options).forEach(function (option, index) {
                if (index === 0) {
                    option.disabled = false;
                    option.hidden = false;
                    return;
                }

                const matches = option.dataset.productId === productId;
                option.disabled = !matches;
                option.hidden = !matches;

                if (matches) {
                    available += 1;
                }
            });

            variantSelect.value = oldValue && Array.from(variantSelect.options).some(
                option => option.value === oldValue && !option.disabled
            ) ? oldValue : '';

            variantSelect.disabled = productId === '';
            variantHelp.textContent = productId === ''
                ? 'Choose a product first.'
                : available > 0
                    ? 'Optional: choose one exact variant, or keep the default for all variants.'
                    : 'This product has no variants; the default price will be used.';
        }

        productSelect.addEventListener('change', function () {
            updateVariants(false);

            const selected = productSelect.options[productSelect.selectedIndex];
            if (selected && unitCostInput && unitCostInput.value === '') {
                unitCostInput.value = Number(selected.dataset.baseCost || 0).toFixed(2);
            }
        });

        updateVariants(true);
    });
</script>
@endpush
