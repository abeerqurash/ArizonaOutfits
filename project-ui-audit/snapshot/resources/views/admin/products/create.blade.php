@extends('admin.layouts.app')

@section('title', 'Create Product')
@section('page-heading', 'Create Product')

@section('content')

<div class="admin-page-header product-editor-header">
    <div>
        <span class="admin-page-eyebrow">Product management</span>
        <h2>Create Product</h2>
        <p>Add a product with pricing, inventory, media, categories, tags, options, variants and SEO.</p>
    </div>

    <div class="admin-page-actions">
        <a href="{{ route('admin.products.index') }}" class="admin-button admin-button-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Products
        </a>
    </div>
</div>

<form
    action="{{ route('admin.products.store') }}"
    method="POST"
    enctype="multipart/form-data"
    data-product-form
    novalidate
>
    @csrf
    @include('admin.products.partials.form', ['product' => null])
</form>

@endsection

@push('page-styles')
<style>
.product-editor-header{margin-bottom:18px}
.product-editor-header .admin-page-eyebrow{color:#635bff;font-size:9px;font-weight:800;letter-spacing:.12em}
.product-editor-header h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800;letter-spacing:-.025em}
.product-editor-header p{color:#7b8497;font-size:12px}
.product-editor-header .admin-button{min-height:38px;border-radius:7px;font-size:10px}
</style>
@endpush
