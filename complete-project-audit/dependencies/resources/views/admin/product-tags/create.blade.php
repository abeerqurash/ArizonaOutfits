@extends('admin.layouts.app')

@section('title', 'Create Product Tag')
@section('page-heading', 'Create Product Tag')

@section('content')
<div class="admin-page-header tag-page-header">
    <div>
        <span class="admin-page-eyebrow">Catalog organization</span>
        <h2>Create Product Tag</h2>
        <p>Create a reusable tag for organizing and identifying related products.</p>
    </div>
    <div class="admin-page-actions">
        <a href="{{ route('admin.product-tags.index') }}" class="admin-button admin-button-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Tags
        </a>
    </div>
</div>

<form action="{{ route('admin.product-tags.store') }}" method="POST" novalidate>
    @csrf
    @include('admin.product-tags.partials.form', ['productTag' => null])
</form>
@endsection

@push('page-styles')
<style>
.tag-page-header{margin-bottom:18px}.tag-page-header h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800;letter-spacing:-.025em}.tag-page-header p{color:#7b8497;font-size:12px}
</style>
@endpush
