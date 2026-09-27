@extends('admin.layouts.app')

@section('title', 'Edit Product Tag')
@section('page-heading', 'Edit Product Tag')

@section('content')
<div class="admin-page-header tag-page-header">
    <div>
        <span class="admin-page-eyebrow">Catalog organization</span>
        <h2>Edit Product Tag</h2>
        <p>Update the tag title or customize its URL slug.</p>
    </div>
    <div class="admin-page-actions">
        <a href="{{ route('admin.product-tags.index') }}" class="admin-button admin-button-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Tags
        </a>
    </div>
</div>

<form action="{{ route('admin.product-tags.update', $productTag) }}" method="POST" novalidate>
    @csrf
    @method('PUT')
    @include('admin.product-tags.partials.form', ['productTag' => $productTag])
</form>
@endsection

@push('page-styles')
<style>
.tag-page-header{margin-bottom:18px}.tag-page-header h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800;letter-spacing:-.025em}.tag-page-header p{color:#7b8497;font-size:12px}
</style>
@endpush
