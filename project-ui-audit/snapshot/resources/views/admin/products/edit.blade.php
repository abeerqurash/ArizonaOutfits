@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('page-heading', 'Edit Product')

@section('content')

<div class="admin-page-header product-editor-header">
    <div>
        <span class="admin-page-eyebrow">Product management</span>
        <h2>Edit Product</h2>
        <p>Update <strong>{{ $product->title }}</strong> including pricing, inventory, media, variants and SEO.</p>
    </div>

    <div class="admin-page-actions">
        @if($product->status === 'active' && $product->slug)
            <a href="{{ route('products.show', $product->slug) }}" target="_blank" rel="noopener" class="admin-button admin-button-secondary">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                View Product
            </a>
        @endif

        <a href="{{ route('admin.products.index') }}" class="admin-button admin-button-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Products
        </a>
    </div>
</div>

<div class="product-edit-summary">
    <div class="product-edit-summary-item"><span>Product ID</span><strong>#{{ $product->id }}</strong></div>
    <div class="product-edit-summary-item"><span>SKU</span><strong>{{ $product->sku ?: '—' }}</strong></div>
    <div class="product-edit-summary-item"><span>Status</span><strong>{{ ucfirst($product->status ?: 'draft') }}</strong></div>
    <div class="product-edit-summary-item"><span>Variants</span><strong>{{ number_format($product->variants->count()) }}</strong></div>
    <div class="product-edit-summary-item"><span>Stock</span><strong>{{ number_format((int) $product->stock) }}</strong></div>
    <div class="product-edit-summary-item"><span>Last Updated</span><strong>{{ $product->updated_at?->format('d M Y, h:i A') ?: '—' }}</strong></div>
</div>

<form
    action="{{ route('admin.products.update', $product) }}"
    method="POST"
    enctype="multipart/form-data"
    data-product-form
    novalidate
>
    @csrf
    @method('PUT')
    @include('admin.products.partials.form', ['product' => $product])
</form>

@endsection

@push('page-styles')
<style>
.product-editor-header{margin-bottom:18px}
.product-editor-header .admin-page-eyebrow{color:#635bff;font-size:9px;font-weight:800;letter-spacing:.12em}
.product-editor-header h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800;letter-spacing:-.025em}
.product-editor-header p{color:#7b8497;font-size:12px}
.product-editor-header .admin-button{min-height:38px;border-radius:7px;font-size:10px}
.product-edit-summary{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;margin-bottom:18px}
.product-edit-summary-item{min-width:0;padding:13px 14px;border:1px solid #e6eaf1;border-radius:11px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.02)}
.product-edit-summary-item span{display:block;margin-bottom:5px;color:#8a93a4;font-size:8px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.product-edit-summary-item strong{display:block;overflow:hidden;color:#172033;font-size:11px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}
.product-edit-summary-item:nth-child(3) strong{color:#13875b}
@media(max-width:1200px){.product-edit-summary{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:640px){.product-edit-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:420px){.product-edit-summary{grid-template-columns:1fr}}
</style>
@endpush
