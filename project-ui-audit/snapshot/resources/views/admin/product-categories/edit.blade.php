@extends('admin.layouts.app')

@section('title', 'Edit Product Category')
@section('page-heading', 'Edit Product Category')

@section('content')

<div class="admin-page-header">
    <div>
        <span class="admin-page-eyebrow">Catalog organization</span>
        <h2>Edit Product Category</h2>
        <p>
            Update <strong>{{ $productCategory->title }}</strong> including hierarchy, image and SEO.
        </p>
    </div>

    <div class="admin-page-actions">
        <a
            href="{{ route('admin.product-categories.index') }}"
            class="admin-button admin-button-secondary"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to Categories
        </a>
    </div>
</div>

<form
    action="{{ route('admin.product-categories.update', $productCategory) }}"
    method="POST"
    enctype="multipart/form-data"
    data-category-form
>
    @csrf
    @method('PUT')

    @include('admin.product-categories.partials.form', [
        'productCategory' => $productCategory
    ])
</form>

@endsection
