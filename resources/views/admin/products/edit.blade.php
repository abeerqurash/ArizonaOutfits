@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('page-heading', 'Edit Product')

@section('content')

{{-- ============================================================
     PAGE HEADER
============================================================ --}}

<div class="admin-page-header">

    <div>

        <span class="admin-page-eyebrow">
            Product management
        </span>

        <h2>
            Edit Product
        </h2>

        <p>
            Update
            <strong>{{ $product->title }}</strong>
            including pricing, inventory, images,
            categories, options, variants and SEO.
        </p>

    </div>


    <div class="admin-page-actions">

        @if(
            $product->status === 'active' &&
            $product->slug
        )

            <a
                href="{{ route('products.show', $product->slug) }}"
                target="_blank"
                rel="noopener"
                class="admin-button admin-button-secondary"
            >
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                View Product
            </a>

        @endif


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
     PRODUCT SUMMARY
============================================================ --}}

<div class="product-edit-summary">

    <div class="product-edit-summary-item">

        <span>
            Product ID
        </span>

        <strong>
            #{{ $product->id }}
        </strong>

    </div>


    <div class="product-edit-summary-item">

        <span>
            SKU
        </span>

        <strong>
            {{ $product->sku ?: '—' }}
        </strong>

    </div>


    <div class="product-edit-summary-item">

        <span>
            Status
        </span>

        <strong>
            {{ ucfirst($product->status ?: 'draft') }}
        </strong>

    </div>


    <div class="product-edit-summary-item">

        <span>
            Variants
        </span>

        <strong>
            {{ number_format($product->variants->count()) }}
        </strong>

    </div>


    <div class="product-edit-summary-item">

        <span>
            Stock
        </span>

        <strong>
            {{ number_format((int) $product->stock) }}
        </strong>

    </div>


    <div class="product-edit-summary-item">

        <span>
            Last Updated
        </span>

        <strong>
            {{ $product->updated_at?->format('d M Y, h:i A') ?: '—' }}
        </strong>

    </div>

</div>


{{-- ============================================================
     PRODUCT FORM
============================================================ --}}

<form
    action="{{ route('admin.products.update', $product) }}"
    method="POST"
    enctype="multipart/form-data"
    data-product-form
    novalidate
>

    @csrf
    @method('PUT')


    @include('admin.products.partials.form', [
        'product' => $product
    ])


</form>

@endsection


@push('page-styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Edit Product Summary
    |--------------------------------------------------------------------------
    */

    .product-edit-summary {
        display: grid;
        grid-template-columns:
            repeat(6, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 24px;
    }


    .product-edit-summary-item {
        min-width: 0;
        padding: 13px 14px;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 11px;
        background: #fff;
    }


    .product-edit-summary-item span {
        display: block;
        margin-bottom: 5px;
        color: #9ca3af;
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .04em;
        text-transform: uppercase;
    }


    .product-edit-summary-item strong {
        display: block;
        overflow: hidden;
        color: #111827;
        font-size: 12px;
        font-weight: 750;
        text-overflow: ellipsis;
        white-space: nowrap;
    }


    @media (max-width: 1200px) {

        .product-edit-summary {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }

    }


    @media (max-width: 640px) {

        .product-edit-summary {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

    }


    @media (max-width: 420px) {

        .product-edit-summary {
            grid-template-columns: 1fr;
        }

    }

</style>

@endpush