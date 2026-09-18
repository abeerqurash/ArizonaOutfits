@extends('admin.layouts.app')

@section('title', 'Create Product')
@section('page-heading', 'Create Product')

@section('content')

{{-- ============================================================
     PAGE HEADER
============================================================ --}}

<div class="admin-page-header">

    <div>
        <span class="admin-page-eyebrow">
            Product management
        </span>

        <h2>Create Product</h2>

        <p>
            Add a new product with pricing, inventory,
            images, categories, options, variants and SEO.
        </p>
    </div>


    <div class="admin-page-actions">

        <a
            href="{{ route('admin.products.index') }}"
            class="admin-button admin-button-secondary"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to Products
        </a>

    </div>

</div>


{{-- ============================================================
     PRODUCT FORM
============================================================ --}}

<form
    action="{{ route('admin.products.store') }}"
    method="POST"
    enctype="multipart/form-data"
    data-product-form
    novalidate
>

    @csrf


    @include('admin.products.partials.form', [
        'product' => null
    ])


</form>

@endsection