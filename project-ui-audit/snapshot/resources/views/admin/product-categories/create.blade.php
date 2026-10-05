@extends('admin.layouts.app')

@section('title', 'Create Product Category')
@section('page-heading', 'Create Product Category')

@section('content')

<div class="admin-page-header">
    <div>
        <span class="admin-page-eyebrow">Catalog organization</span>
        <h2>Create Product Category</h2>
        <p>
            Add a category with hierarchy, imagery, description and search metadata.
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
    action="{{ route('admin.product-categories.store') }}"
    method="POST"
    enctype="multipart/form-data"
    data-category-form
>
    @csrf

    @include('admin.product-categories.partials.form', [
        'productCategory' => null
    ])
</form>

@endsection
