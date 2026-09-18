@extends('admin.layouts.app')

@section('title', 'Product Categories')
@section('page-heading', 'Product Categories')

@section('content')

@php
    $categoryImageUrl = function ($path) {
        if (!$path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://') ||
            str_starts_with($path, '//')
        ) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return str_starts_with($path, 'storage/')
            ? asset($path)
            : asset('storage/' . $path);
    };
@endphp

<div class="admin-page-header">
    <div>
        <span class="admin-page-eyebrow">Catalog organization</span>
        <h2>Product Categories</h2>
        <p>
            Organize products into parent and child categories and manage their storefront metadata.
        </p>
    </div>

    <div class="admin-page-actions">
        <a
            href="{{ route('admin.product-categories.create') }}"
            class="admin-button admin-button-primary"
        >
            <i class="fa-solid fa-plus"></i>
            Add Category
        </a>
    </div>
</div>

<div class="category-statistics-grid">

    <div class="admin-stat-card">
        <div class="admin-stat-card-top">
            <div>
                <span class="admin-stat-label">Total Categories</span>
                <strong class="admin-stat-value">{{ number_format($stats['total']) }}</strong>
            </div>
            <span class="admin-stat-icon"><i class="fa-solid fa-layer-group"></i></span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top">
            <div>
                <span class="admin-stat-label">Parent Categories</span>
                <strong class="admin-stat-value">{{ number_format($stats['parents']) }}</strong>
            </div>
            <span class="admin-stat-icon"><i class="fa-solid fa-folder-tree"></i></span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top">
            <div>
                <span class="admin-stat-label">Child Categories</span>
                <strong class="admin-stat-value">{{ number_format($stats['children']) }}</strong>
            </div>
            <span class="admin-stat-icon"><i class="fa-solid fa-code-branch"></i></span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-card-top">
            <div>
                <span class="admin-stat-label">With Products</span>
                <strong class="admin-stat-value">{{ number_format($stats['with_products']) }}</strong>
            </div>
            <span class="admin-stat-icon"><i class="fa-solid fa-box"></i></span>
        </div>
    </div>

</div>

<section class="admin-panel category-filter-panel">
    <form
        action="{{ route('admin.product-categories.index') }}"
        method="GET"
        class="category-filter-form"
    >
        <div class="category-filter-search">
            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search title, slug or description..."
            >
        </div>

        <select name="parent">
            <option value="">All parents</option>

            @foreach($parents as $parent)
                <option
                    value="{{ $parent->id }}"
                    @selected((string) request('parent') === (string) $parent->id)
                >
                    {{ $parent->title }}
                </option>
            @endforeach
        </select>

        <button
            type="submit"
            class="admin-button admin-button-primary"
        >
            <i class="fa-solid fa-filter"></i>
            Filter
        </button>

        @if(request()->filled('search') || request()->filled('parent'))
            <a
                href="{{ route('admin.product-categories.index') }}"
                class="admin-button admin-button-secondary"
            >
                Reset
            </a>
        @endif
    </form>
</section>

<section class="admin-panel">
    <div class="admin-panel-header">
        <div>
            <span class="admin-panel-eyebrow">Category directory</span>
            <h3>
                {{ number_format($categories->total()) }}
                {{ Str::plural('Category', $categories->total()) }}
            </h3>
        </div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table category-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Slug</th>
                    <th>Parent</th>
                    <th>Products</th>
                    <th>Children</th>
                    <th class="category-actions-heading">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <div class="category-identity">
                                <div class="category-thumbnail">
                                    @if($category->featured_image)
                                        <img
                                            src="{{ $categoryImageUrl($category->featured_image) }}"
                                            alt="{{ $category->title }}"
                                            loading="lazy"
                                            onerror="this.hidden=true;this.nextElementSibling.hidden=false;"
                                        >
                                        <span hidden>
                                            <i class="fa-regular fa-image"></i>
                                        </span>
                                    @else
                                        <span>
                                            <i class="fa-regular fa-image"></i>
                                        </span>
                                    @endif
                                </div>

                                <div class="category-identity-copy">
                                    <strong>{{ $category->title }}</strong>

                                    @if($category->description)
                                        <span>
                                            {{ Str::limit(strip_tags($category->description), 70) }}
                                        </span>
                                    @else
                                        <span>No description</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <code class="category-slug">{{ $category->slug }}</code>
                        </td>

                        <td>
                            @if($category->parent)
                                <span class="category-parent-badge">
                                    {{ $category->parent->title }}
                                </span>
                            @else
                                <span class="category-root-badge">
                                    Root
                                </span>
                            @endif
                        </td>

                        <td>
                            <strong class="category-count">
                                {{ number_format($category->products_count) }}
                            </strong>
                        </td>

                        <td>
                            <strong class="category-count">
                                {{ number_format($category->children_count) }}
                            </strong>
                        </td>

                        <td>
                            <div class="category-actions">
                                <a
                                    href="{{ route('admin.product-categories.edit', $category) }}"
                                    class="category-action-button"
                                    title="Edit category"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                    Edit
                                </a>

                                <button
                                    type="button"
                                    class="category-action-button is-danger"
                                    data-delete-category
                                    data-delete-url="{{ route('admin.product-categories.destroy', $category) }}"
                                    data-category-title="{{ $category->title }}"
                                    @disabled(
                                        $category->products_count > 0 ||
                                        $category->children_count > 0
                                    )
                                    title="{{
                                        $category->products_count > 0
                                            ? 'Move assigned products before deleting'
                                            : (
                                                $category->children_count > 0
                                                    ? 'Move or delete child categories first'
                                                    : 'Delete category'
                                            )
                                    }}"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="category-empty-state">
                                <i class="fa-solid fa-layer-group"></i>
                                <strong>No categories found</strong>
                                <span>
                                    Try different filters or create your first product category.
                                </span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="category-pagination">
            {{ $categories->links() }}
        </div>
    @endif
</section>

<div
    class="category-delete-modal"
    id="categoryDeleteModal"
    hidden
>
    <div
        class="category-delete-backdrop"
        data-close-delete-modal
    ></div>

    <div
        class="category-delete-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="categoryDeleteTitle"
    >
        <div class="category-delete-icon">
            <i class="fa-solid fa-trash"></i>
        </div>

        <h3 id="categoryDeleteTitle">Delete Category?</h3>

        <p>
            You are about to permanently delete
            <strong id="categoryDeleteName"></strong>.
            This action cannot be undone.
        </p>

        <div class="category-delete-actions">
            <button
                type="button"
                class="admin-button admin-button-secondary"
                data-close-delete-modal
            >
                Cancel
            </button>

            <form
                method="POST"
                id="categoryDeleteForm"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="category-confirm-delete"
                >
                    <i class="fa-solid fa-trash"></i>
                    Delete Category
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('page-styles')
<style>
    .category-statistics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .category-filter-panel {
        margin-bottom: 20px;
        padding: 15px;
    }

    .category-filter-form {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 220px auto auto;
        gap: 10px;
        align-items: center;
    }

    .category-filter-search {
        position: relative;
    }

    .category-filter-search i {
        position: absolute;
        top: 50%;
        left: 13px;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 11px;
        pointer-events: none;
    }

    .category-filter-search input,
    .category-filter-form select {
        width: 100%;
        min-height: 41px;
        border: 1px solid rgba(15, 23, 42, .10);
        border-radius: 9px;
        outline: 0;
        background: #fff;
        color: #111827;
        font: inherit;
        font-size: 10px;
    }

    .category-filter-search input {
        padding: 9px 12px 9px 35px;
    }

    .category-filter-form select {
        padding: 9px 11px;
    }

    .category-identity {
        min-width: 260px;
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .category-thumbnail {
        flex: 0 0 52px;
        width: 52px;
        height: 52px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 10px;
        background: #f8fafc;
        color: #94a3b8;
    }

    .category-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .category-thumbnail span {
        font-size: 16px;
    }

    .category-identity-copy {
        min-width: 0;
    }

    .category-identity-copy strong {
        display: block;
        margin-bottom: 4px;
        color: #111827;
        font-size: 11px;
    }

    .category-identity-copy span {
        display: block;
        max-width: 330px;
        overflow: hidden;
        color: #94a3b8;
        font-size: 9px;
        line-height: 1.45;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .category-slug {
        padding: 4px 7px;
        border-radius: 6px;
        background: #f8fafc;
        color: #64748b;
        font-size: 9px;
    }

    .category-parent-badge,
    .category-root-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 750;
    }

    .category-parent-badge {
        background: #f5f3ff;
        color: #6d5dfc;
    }

    .category-root-badge {
        background: #f1f5f9;
        color: #64748b;
    }

    .category-count {
        color: #374151;
        font-size: 11px;
    }

    .category-actions-heading {
        text-align: right !important;
    }

    .category-actions {
        display: flex;
        justify-content: flex-end;
        gap: 6px;
    }

    .category-action-button {
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 9px;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 7px;
        background: #fff;
        color: #475569;
        font: inherit;
        font-size: 8px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
    }

    .category-action-button:hover {
        border-color: #7c6cff;
        color: #5b4df6;
    }

    .category-action-button.is-danger {
        border-color: #fecaca;
        color: #dc2626;
    }

    .category-action-button:disabled {
        opacity: .38;
        cursor: not-allowed;
    }

    .category-empty-state {
        min-height: 220px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        color: #94a3b8;
        text-align: center;
    }

    .category-empty-state i {
        font-size: 28px;
    }

    .category-empty-state strong {
        color: #475569;
        font-size: 12px;
    }

    .category-empty-state span {
        font-size: 9px;
    }

    .category-pagination {
        padding: 15px 18px;
        border-top: 1px solid rgba(15, 23, 42, .07);
    }

    .category-delete-modal[hidden] {
        display: none !important;
    }

    .category-delete-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .category-delete-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .52);
        backdrop-filter: blur(3px);
    }

    .category-delete-dialog {
        position: relative;
        z-index: 1;
        width: min(420px, 100%);
        padding: 24px;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .20);
        text-align: center;
    }

    .category-delete-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        border-radius: 50%;
        background: #fef2f2;
        color: #dc2626;
        font-size: 17px;
    }

    .category-delete-dialog h3 {
        margin: 0 0 8px;
        color: #111827;
        font-size: 16px;
    }

    .category-delete-dialog p {
        margin: 0;
        color: #64748b;
        font-size: 10px;
        line-height: 1.65;
    }

    .category-delete-actions {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
    }

    .category-confirm-delete {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 13px;
        border: 0;
        border-radius: 9px;
        background: #dc2626;
        color: #fff;
        font: inherit;
        font-size: 10px;
        font-weight: 750;
        cursor: pointer;
    }

    @media (max-width: 1050px) {
        .category-statistics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .category-filter-form {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 620px) {
        .category-statistics-grid,
        .category-filter-form {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('page-scripts')
<script>
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const modal =
        document.getElementById(
            'categoryDeleteModal'
        );

    const deleteForm =
        document.getElementById(
            'categoryDeleteForm'
        );

    const deleteName =
        document.getElementById(
            'categoryDeleteName'
        );

    function closeDeleteModal() {
        if (modal) {
            modal.hidden = true;
        }
    }

    document
        .querySelectorAll(
            '[data-delete-category]'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {
                    if (
                        button.disabled ||
                        !modal ||
                        !deleteForm
                    ) {
                        return;
                    }

                    deleteForm.action =
                        button.dataset.deleteUrl || '';

                    if (deleteName) {
                        deleteName.textContent =
                            button.dataset.categoryTitle || '';
                    }

                    modal.hidden = false;
                }
            );

        });

    document
        .querySelectorAll(
            '[data-close-delete-modal]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                closeDeleteModal
            );
        });

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape' &&
                modal &&
                !modal.hidden
            ) {
                closeDeleteModal();
            }
        }
    );

});
</script>
@endpush
