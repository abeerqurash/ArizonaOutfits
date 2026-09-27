@extends('admin.layouts.app')

@section('title', 'Products')
@section('page-heading', 'Products')

@section('content')

{{-- ============================================================
     ADMIN FEEDBACK POPUP
============================================================ --}}

@php
    $productFeedbackType = null;
    $productFeedbackTitle = null;
    $productFeedbackMessage = null;
    $productFeedbackErrors = [];

    if (session('error')) {
        $productFeedbackType = 'error';
        $productFeedbackTitle = 'Action could not be completed';
        $productFeedbackMessage = session('error');
    } elseif ($errors->any()) {
        $productFeedbackType = 'error';
        $productFeedbackTitle = 'Please check the request';
        $productFeedbackErrors = $errors->all();
    } elseif (session('warning')) {
        $productFeedbackType = 'warning';
        $productFeedbackTitle = 'Attention required';
        $productFeedbackMessage = session('warning');
    } elseif (session('success')) {
        $productFeedbackType = 'success';
        $productFeedbackTitle = 'Completed successfully';
        $productFeedbackMessage = session('success');
    }
@endphp

@if($productFeedbackType)
<div
    class="product-feedback-popup is-{{ $productFeedbackType }}"
    id="productFeedbackPopup"
    role="dialog"
    aria-modal="true"
    aria-labelledby="productFeedbackTitle"
>
    <div class="product-feedback-backdrop" data-product-feedback-close></div>

    <div class="product-feedback-dialog">
        <button
            type="button"
            class="product-feedback-close"
            data-product-feedback-close
            aria-label="Close message"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="product-feedback-icon">
            @if($productFeedbackType === 'success')
                <i class="fa-solid fa-circle-check"></i>
            @elseif($productFeedbackType === 'warning')
                <i class="fa-solid fa-triangle-exclamation"></i>
            @else
                <i class="fa-solid fa-circle-exclamation"></i>
            @endif
        </div>

        <span class="admin-page-eyebrow">Product management</span>
        <h3 id="productFeedbackTitle">{{ $productFeedbackTitle }}</h3>

        @if($productFeedbackMessage)
            <p>{{ $productFeedbackMessage }}</p>
        @endif

        @if(!empty($productFeedbackErrors))
            <ul class="product-feedback-errors">
                @foreach($productFeedbackErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button
            type="button"
            class="admin-button admin-button-primary product-feedback-ok"
            data-product-feedback-close
        >
            OK
        </button>
    </div>
</div>
@endif


@php
    $totalProducts = $statistics['total'] ?? $products->total();

    $currentSearch = request('search', '');
    $currentStatus = request('status', '');
    $currentFeatured = request('featured', '');
    $currentCategory = request('category', '');

    $statusLabels = [
        'active' => 'Active',
        'draft' => 'Draft',
        'inactive' => 'Inactive',
    ];

    $statusIcons = [
        'active' => 'fa-circle-check',
        'draft' => 'fa-pen-to-square',
        'inactive' => 'fa-circle-pause',
    ];

    /*
    |--------------------------------------------------------------------------
    | Product image helper
    |--------------------------------------------------------------------------
    */

    $productImage = function ($product) {
        $path = $product->featured_image;

        if (!$path && $product->images->isNotEmpty()) {
            $path = $product->images->first()->image;
        }

        if (!$path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://') ||
            str_starts_with($path, '//') ||
            str_starts_with($path, 'data:')
        ) {
            return $path;
        }

        $path = ltrim(
            str_replace('\\', '/', $path),
            '/'
        );

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    };
@endphp


{{-- ============================================================
     PAGE HEADER
============================================================ --}}

<div class="admin-page-header">

    <div>
        <span class="admin-page-eyebrow">
            Catalog management
        </span>

        <h2>Products</h2>

        <p>
            Manage your store products, pricing, stock,
            variants, categories and product visibility.
        </p>
    </div>

    <div class="admin-page-actions">

        <a
            href="{{ route('admin.product-categories.index') }}"
            class="admin-button admin-button-secondary"
        >
            <i class="fa-solid fa-layer-group"></i>
            Categories
        </a>

        <a
            href="{{ route('admin.product-tags.index') }}"
            class="admin-button admin-button-secondary"
        >
            <i class="fa-solid fa-tags"></i>
            Tags
        </a>

        <a
            href="{{ route('admin.coupons.index') }}"
            class="admin-button admin-button-secondary"
        >
            <i class="fa-solid fa-ticket"></i>
            Coupons
        </a>

        <a
            href="{{ route('admin.products.create') }}"
            class="admin-button admin-button-primary"
        >
            <i class="fa-solid fa-plus"></i>
            Add Product
        </a>

    </div>

</div>


{{-- ============================================================
     STATISTICS
============================================================ --}}

<div class="admin-statistics-grid product-statistics-grid">

    <div class="admin-stat-card">

        <div class="admin-stat-card-top">

            <div class="admin-stat-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>

            <span class="product-stat-caption">
                Catalog
            </span>

        </div>

        <div class="admin-stat-label">
            Total Products
        </div>

        <div class="admin-stat-value">
            {{ number_format($totalProducts) }}
        </div>

    </div>


    <div class="admin-stat-card">

        <div class="admin-stat-card-top">

            <div class="admin-stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <span class="product-stat-caption">
                Catalog
            </span>

        </div>

        <div class="admin-stat-label">
            Active
        </div>

        <div class="admin-stat-value">
            {{ number_format($statistics['active'] ?? 0) }}
        </div>

    </div>


    <div class="admin-stat-card">

        <div class="admin-stat-card-top">

            <div class="admin-stat-icon">
                <i class="fa-solid fa-star"></i>
            </div>

            <span class="product-stat-caption">
                Catalog
            </span>

        </div>

        <div class="admin-stat-label">
            Featured
        </div>

        <div class="admin-stat-value">
            {{ number_format($statistics['featured'] ?? 0) }}
        </div>

    </div>


    <div class="admin-stat-card">

        <div class="admin-stat-card-top">

            <div class="admin-stat-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <span class="product-stat-caption">
                Catalog
            </span>

        </div>

        <div class="admin-stat-label">
            Low / Out of Stock
        </div>

        <div class="admin-stat-value">
            {{ number_format($statistics['low_stock'] ?? 0) }}
        </div>

    </div>

</div>


{{-- ============================================================
     FILTER PANEL
============================================================ --}}

<div class="admin-panel product-filter-panel">

    <div class="admin-panel-header">

        <div>

            <span class="admin-panel-eyebrow">
                Find products
            </span>

            <h3>Search & Filter</h3>

        </div>

        @if(
            request()->filled('search') ||
            request()->filled('status') ||
            request()->filled('featured') ||
            request()->filled('category')
        )

            <a
                href="{{ route('admin.products.index') }}"
                class="admin-button admin-button-secondary admin-button-small"
            >
                <i class="fa-solid fa-rotate-left"></i>
                Reset
            </a>

        @endif

    </div>


    <form
        action="{{ route('admin.products.index') }}"
        method="GET"
        class="product-filter-form"
    >

        {{-- Search --}}

        <div class="product-filter-field product-search-field">

            <label for="search">
                Search
            </label>

            <div class="product-input-icon">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    id="search"
                    name="search"
                    value="{{ $currentSearch }}"
                    placeholder="Product name, SKU or slug..."
                    autocomplete="off"
                >

            </div>

        </div>


        {{-- Category --}}

        <div class="product-filter-field">

            <label for="category">
                Category
            </label>

            <select
                id="category"
                name="category"
            >

                <option value="">
                    All categories
                </option>

                @foreach($categories as $category)

                    <option
                        value="{{ $category->id }}"
                        @selected(
                            (string) $currentCategory ===
                            (string) $category->id
                        )
                    >
                        {{ $category->title }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- Status --}}

        <div class="product-filter-field">

            <label for="status">
                Status
            </label>

            <select
                id="status"
                name="status"
            >

                <option value="">
                    All statuses
                </option>

                <option
                    value="active"
                    @selected($currentStatus === 'active')
                >
                    Active
                </option>

                <option
                    value="draft"
                    @selected($currentStatus === 'draft')
                >
                    Draft
                </option>

                <option
                    value="inactive"
                    @selected($currentStatus === 'inactive')
                >
                    Inactive
                </option>

            </select>

        </div>


        {{-- Featured --}}

        <div class="product-filter-field">

            <label for="featured">
                Featured
            </label>

            <select
                id="featured"
                name="featured"
            >

                <option value="">
                    All products
                </option>

                <option
                    value="1"
                    @selected($currentFeatured === '1')
                >
                    Featured
                </option>

                <option
                    value="0"
                    @selected($currentFeatured === '0')
                >
                    Not featured
                </option>

            </select>

        </div>


        <div class="product-filter-submit">

            <button
                type="submit"
                class="admin-button admin-button-primary"
            >
                <i class="fa-solid fa-filter"></i>
                Apply Filters
            </button>

        </div>

    </form>

</div>


{{-- ============================================================
     PRODUCTS PANEL
============================================================ --}}

<div class="admin-panel product-list-panel">

    <div class="admin-panel-header">

        <div>

            <span class="admin-panel-eyebrow">
                Product catalog
            </span>

            <h3>
                Product List
            </h3>

        </div>

        <div class="product-results-summary">

            @if($products->total() > 0)

                Showing
                <strong>
                    {{ $products->firstItem() }}
                </strong>
                –
                <strong>
                    {{ $products->lastItem() }}
                </strong>

                of

                <strong>
                    {{ number_format($products->total()) }}
                </strong>

            @else

                No products

            @endif

        </div>

    </div>


    @if($products->count())


        {{-- ====================================================
             DESKTOP TABLE
        ===================================================== --}}

        <div class="admin-table-wrapper product-desktop-table">

            <table class="admin-table product-table">

                <thead>

                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Inventory
                        </th>

                        <th>
                            Variants
                        </th>

                        <th>
                            Status
                        </th>

                        <th class="product-table-actions-heading">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($products as $product)

                        @php
                            $imageUrl = $productImage($product);

                            $regularPrice =
                                (float) $product->regular_price;

                            $salePrice =
                                $product->sale_price !== null
                                    ? (float) $product->sale_price
                                    : null;

                            $hasSale =
                                $salePrice !== null &&
                                $salePrice >= 0 &&
                                $salePrice < $regularPrice;

                            $stock = (int) $product->stock;

                            $reorderPoint =
                                $product->reorder_point !== null
                                    ? (int) $product->reorder_point
                                    : 5;

                            if ($stock <= 0) {
                                $stockClass = 'out';
                                $stockLabel = 'Out of stock';
                                $stockIcon = 'fa-circle-xmark';
                            } elseif ($stock <= $reorderPoint) {
                                $stockClass = 'low';
                                $stockLabel = 'Low stock';
                                $stockIcon = 'fa-triangle-exclamation';
                            } else {
                                $stockClass = 'good';
                                $stockLabel = 'In stock';
                                $stockIcon = 'fa-circle-check';
                            }

                            $status =
                                $product->status ?: 'draft';

                            $statusLabel =
                                $statusLabels[$status]
                                ?? ucfirst($status);

                            $statusIcon =
                                $statusIcons[$status]
                                ?? 'fa-circle';
                        @endphp


                        <tr>

                            {{-- Product --}}

                            <td>

                                <div class="product-table-product">

                                    <div class="product-table-image">

                                        @if($imageUrl)

                                            <img
                                                src="{{ $imageUrl }}"
                                                alt="{{ $product->title }}"
                                                loading="lazy"
                                                onerror="
                                                    this.style.display='none';
                                                    this.nextElementSibling.style.display='flex';
                                                "
                                            >

                                        @endif

                                        <div
                                            class="product-image-placeholder"
                                            @if($imageUrl)
                                                style="display:none;"
                                            @endif
                                        >
                                            <i class="fa-regular fa-image"></i>
                                        </div>

                                    </div>


                                    <div class="product-table-info">

                                        <div class="product-name-row">

                                            <a
                                                href="{{ route('admin.products.edit', $product) }}"
                                                class="product-name"
                                            >
                                                {{ $product->title }}
                                            </a>

                                            @if($product->is_featured)

                                                <span
                                                    class="product-featured-star"
                                                    title="Featured product"
                                                >
                                                    <i class="fa-solid fa-star"></i>
                                                </span>

                                            @endif

                                        </div>


                                        <div class="product-table-meta">

                                            @if($product->sku)

                                                <span>
                                                    SKU:
                                                    <strong>
                                                        {{ $product->sku }}
                                                    </strong>
                                                </span>

                                            @else

                                                <span>
                                                    No SKU
                                                </span>

                                            @endif

                                            <span class="product-meta-separator">
                                                •
                                            </span>

                                            <span>
                                                #{{ $product->id }}
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- Category --}}

                            <td>

                                @if($product->categories->isNotEmpty())

                                    <div class="product-category-list">

                                        @foreach(
                                            $product->categories->take(2)
                                            as $category
                                        )

                                            <span class="product-category-chip">
                                                {{ $category->title }}
                                            </span>

                                        @endforeach

                                        @if($product->categories->count() > 2)

                                            <span
                                                class="product-category-more"
                                                title="{{
                                                    $product->categories
                                                        ->skip(2)
                                                        ->pluck('title')
                                                        ->implode(', ')
                                                }}"
                                            >
                                                +{{
                                                    $product->categories->count() - 2
                                                }}
                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <span class="product-muted">
                                        Uncategorized
                                    </span>

                                @endif

                            </td>


                            {{-- Price --}}

                            <td>

                                <div class="product-price">

                                    @if($hasSale)

                                        <strong>
                                            ${{ number_format($salePrice, 2) }}
                                        </strong>

                                        <del>
                                            ${{ number_format($regularPrice, 2) }}
                                        </del>

                                    @else

                                        <strong>
                                            ${{ number_format($regularPrice, 2) }}
                                        </strong>

                                    @endif

                                </div>

                            </td>


                            {{-- Inventory --}}

                            <td>

                                <div class="product-inventory">

                                    <strong>
                                        {{ number_format($stock) }}
                                    </strong>

                                    <span
                                        class="product-stock-state product-stock-{{ $stockClass }}"
                                    >
                                        <i class="fa-solid {{ $stockIcon }}"></i>
                                        {{ $stockLabel }}
                                    </span>

                                </div>

                            </td>


                            {{-- Variants --}}

                            <td>

                                @if($product->variants_count > 0)

                                    <span class="product-variant-count">

                                        <i class="fa-solid fa-code-branch"></i>

                                        {{
                                            number_format(
                                                $product->variants_count
                                            )
                                        }}

                                    </span>

                                @else

                                    <span class="product-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}

                            <td>

                                <span
                                    class="
                                        product-status
                                        product-status-{{ $status }}
                                    "
                                >

                                    <i
                                        class="fa-solid {{ $statusIcon }}"
                                    ></i>

                                    {{ $statusLabel }}

                                </span>

                            </td>


                            {{-- Actions --}}

                            <td>

                                <div class="product-actions">

                                    <a
                                        href="{{ route('products.show', $product->slug) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="product-action-button"
                                        title="View product"
                                        aria-label="View {{ $product->title }}"
                                    >
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>


                                    <a
                                        href="{{ route('admin.products.edit', $product) }}"
                                        class="product-action-button product-action-edit"
                                        title="Edit product"
                                        aria-label="Edit {{ $product->title }}"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </a>


                                    <button
                                        type="button"
                                        class="product-action-button product-action-delete"
                                        title="Delete product"
                                        aria-label="Delete {{ $product->title }}"
                                        data-product-delete
                                        data-product-name="{{ $product->title }}"
                                        data-delete-form="delete-product-{{ $product->id }}"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>


                                    <form
                                        id="delete-product-{{ $product->id }}"
                                        action="{{ route('admin.products.destroy', $product) }}"
                                        method="POST"
                                        class="product-delete-form"
                                    >
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>


        {{-- ====================================================
             MOBILE CARDS
        ===================================================== --}}

        <div class="product-mobile-list">

            @foreach($products as $product)

                @php
                    $imageUrl = $productImage($product);

                    $regularPrice =
                        (float) $product->regular_price;

                    $salePrice =
                        $product->sale_price !== null
                            ? (float) $product->sale_price
                            : null;

                    $hasSale =
                        $salePrice !== null &&
                        $salePrice >= 0 &&
                        $salePrice < $regularPrice;

                    $stock = (int) $product->stock;

                    $reorderPoint =
                        $product->reorder_point !== null
                            ? (int) $product->reorder_point
                            : 5;

                    if ($stock <= 0) {
                        $stockClass = 'out';
                        $stockLabel = 'Out of stock';
                    } elseif ($stock <= $reorderPoint) {
                        $stockClass = 'low';
                        $stockLabel = 'Low stock';
                    } else {
                        $stockClass = 'good';
                        $stockLabel = 'In stock';
                    }

                    $status =
                        $product->status ?: 'draft';

                    $statusLabel =
                        $statusLabels[$status]
                        ?? ucfirst($status);
                @endphp


                <article class="product-mobile-card">

                    <div class="product-mobile-card-top">

                        <div class="product-mobile-image">

                            @if($imageUrl)

                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $product->title }}"
                                    loading="lazy"
                                    onerror="
                                        this.style.display='none';
                                        this.nextElementSibling.style.display='flex';
                                    "
                                >

                            @endif

                            <div
                                class="product-image-placeholder"
                                @if($imageUrl)
                                    style="display:none;"
                                @endif
                            >
                                <i class="fa-regular fa-image"></i>
                            </div>

                        </div>


                        <div class="product-mobile-main">

                            <div class="product-name-row">

                                <a
                                    href="{{ route('admin.products.edit', $product) }}"
                                    class="product-name"
                                >
                                    {{ $product->title }}
                                </a>

                                @if($product->is_featured)

                                    <span class="product-featured-star">
                                        <i class="fa-solid fa-star"></i>
                                    </span>

                                @endif

                            </div>


                            <div class="product-table-meta">

                                <span>
                                    {{ $product->sku ?: 'No SKU' }}
                                </span>

                                <span class="product-meta-separator">
                                    •
                                </span>

                                <span>
                                    #{{ $product->id }}
                                </span>

                            </div>


                            <div class="product-price">

                                @if($hasSale)

                                    <strong>
                                        ${{ number_format($salePrice, 2) }}
                                    </strong>

                                    <del>
                                        ${{ number_format($regularPrice, 2) }}
                                    </del>

                                @else

                                    <strong>
                                        ${{ number_format($regularPrice, 2) }}
                                    </strong>

                                @endif

                            </div>

                        </div>

                    </div>


                    <div class="product-mobile-details">

                        <div>

                            <span class="product-mobile-label">
                                Stock
                            </span>

                            <div class="product-mobile-value">
                                {{ number_format($stock) }}

                                <span
                                    class="
                                        product-stock-state
                                        product-stock-{{ $stockClass }}
                                    "
                                >
                                    {{ $stockLabel }}
                                </span>
                            </div>

                        </div>


                        <div>

                            <span class="product-mobile-label">
                                Variants
                            </span>

                            <div class="product-mobile-value">
                                {{
                                    number_format(
                                        $product->variants_count
                                    )
                                }}
                            </div>

                        </div>


                        <div>

                            <span class="product-mobile-label">
                                Status
                            </span>

                            <div class="product-mobile-value">

                                <span
                                    class="
                                        product-status
                                        product-status-{{ $status }}
                                    "
                                >
                                    {{ $statusLabel }}
                                </span>

                            </div>

                        </div>

                    </div>


                    @if($product->categories->isNotEmpty())

                        <div class="product-category-list">

                            @foreach(
                                $product->categories->take(3)
                                as $category
                            )

                                <span class="product-category-chip">
                                    {{ $category->title }}
                                </span>

                            @endforeach

                        </div>

                    @endif


                    <div class="product-mobile-actions">

                        <a
                            href="{{ route('products.show', $product->slug) }}"
                            target="_blank"
                            rel="noopener"
                            class="admin-button admin-button-secondary"
                        >
                            <i class="fa-solid fa-eye"></i>
                            View
                        </a>

                        <a
                            href="{{ route('admin.products.edit', $product) }}"
                            class="admin-button admin-button-primary"
                        >
                            <i class="fa-solid fa-pen"></i>
                            Edit
                        </a>

                        <button
                            type="button"
                            class="admin-button product-mobile-delete"
                            data-product-delete
                            data-product-name="{{ $product->title }}"
                            data-delete-form="delete-product-mobile-{{ $product->id }}"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                        <form
                            id="delete-product-mobile-{{ $product->id }}"
                            action="{{ route('admin.products.destroy', $product) }}"
                            method="POST"
                            class="product-delete-form"
                        >
                            @csrf
                            @method('DELETE')
                        </form>

                    </div>

                </article>

            @endforeach

        </div>


        {{-- ====================================================
             PAGINATION
        ===================================================== --}}

        @if($products->hasPages())

            <div class="product-pagination">

                <div class="product-pagination-summary">

                    Showing

                    <strong>
                        {{ $products->firstItem() }}
                    </strong>

                    to

                    <strong>
                        {{ $products->lastItem() }}
                    </strong>

                    of

                    <strong>
                        {{ number_format($products->total()) }}
                    </strong>

                    products

                </div>


                <div class="product-pagination-links">

                    {{-- Previous --}}

                    @if($products->onFirstPage())

                        <span
                            class="product-page-button product-page-disabled"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </span>

                    @else

                        <a
                            href="{{ $products->previousPageUrl() }}"
                            class="product-page-button"
                            aria-label="Previous page"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>

                    @endif


                    @php
                        $startPage = max(
                            1,
                            $products->currentPage() - 2
                        );

                        $endPage = min(
                            $products->lastPage(),
                            $products->currentPage() + 2
                        );
                    @endphp


                    @if($startPage > 1)

                        <a
                            href="{{ $products->url(1) }}"
                            class="product-page-button"
                        >
                            1
                        </a>

                        @if($startPage > 2)

                            <span class="product-page-dots">
                                …
                            </span>

                        @endif

                    @endif


                    @for(
                        $page = $startPage;
                        $page <= $endPage;
                        $page++
                    )

                        @if(
                            $page ===
                            $products->currentPage()
                        )

                            <span
                                class="
                                    product-page-button
                                    product-page-current
                                "
                            >
                                {{ $page }}
                            </span>

                        @else

                            <a
                                href="{{ $products->url($page) }}"
                                class="product-page-button"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endfor


                    @if(
                        $endPage <
                        $products->lastPage()
                    )

                        @if(
                            $endPage <
                            $products->lastPage() - 1
                        )

                            <span class="product-page-dots">
                                …
                            </span>

                        @endif

                        <a
                            href="{{
                                $products->url(
                                    $products->lastPage()
                                )
                            }}"
                            class="product-page-button"
                        >
                            {{ $products->lastPage() }}
                        </a>

                    @endif


                    {{-- Next --}}

                    @if($products->hasMorePages())

                        <a
                            href="{{ $products->nextPageUrl() }}"
                            class="product-page-button"
                            aria-label="Next page"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>

                    @else

                        <span
                            class="product-page-button product-page-disabled"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>

                    @endif

                </div>

            </div>

        @endif


    @else


        {{-- ====================================================
             EMPTY STATE
        ===================================================== --}}

        <div class="product-empty-state">

            <div class="product-empty-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>

            @if(
                request()->filled('search') ||
                request()->filled('status') ||
                request()->filled('featured') ||
                request()->filled('category')
            )

                <h3>No matching products</h3>

                <p>
                    We couldn't find any products matching
                    the filters you selected.
                </p>

                <a
                    href="{{ route('admin.products.index') }}"
                    class="admin-button admin-button-secondary"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                    Clear Filters
                </a>

            @else

                <h3>No products yet</h3>

                <p>
                    Add your first product to start building
                    your Arizona Outfits catalog.
                </p>

                <a
                    href="{{ route('admin.products.create') }}"
                    class="admin-button admin-button-primary"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add First Product
                </a>

            @endif

        </div>

    @endif

</div>


{{-- ============================================================
     DELETE CONFIRMATION MODAL
============================================================ --}}

<div
    class="product-modal"
    id="productDeleteModal"
    aria-hidden="true"
>

    <div
        class="product-modal-backdrop"
        data-close-product-modal
    ></div>


    <div
        class="product-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="productDeleteTitle"
    >

        <button
            type="button"
            class="product-modal-close"
            data-close-product-modal
            aria-label="Close"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>


        <div class="product-modal-danger-icon">
            <i class="fa-solid fa-trash-can"></i>
        </div>


        <h3 id="productDeleteTitle">
            Delete product?
        </h3>

        <p>
            You are about to permanently delete
            <strong id="productDeleteName">
                this product
            </strong>.
        </p>

        <p class="product-modal-note">
            Products connected to historical orders,
            inventory, suppliers or purchase orders will
            be protected automatically.
        </p>


        <div class="product-modal-actions">

            <button
                type="button"
                class="admin-button admin-button-secondary"
                data-close-product-modal
            >
                Cancel
            </button>

            <button
                type="button"
                class="admin-button product-confirm-delete"
                id="confirmProductDelete"
            >
                <i class="fa-solid fa-trash"></i>
                Delete Product
            </button>

        </div>

    </div>

</div>

@endsection


@push('page-styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Product Statistics
    |--------------------------------------------------------------------------
    */

    .product-statistics-grid {
        margin-bottom: 24px;
    }

    .product-stat-caption {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        opacity: .55;
    }


    /*
    |--------------------------------------------------------------------------
    | Filter
    |--------------------------------------------------------------------------
    */

    .product-filter-panel {
        margin-bottom: 24px;
    }

    .product-filter-form {
        display: grid;
        grid-template-columns:
            minmax(260px, 1.7fr)
            repeat(3, minmax(150px, .8fr))
            auto;
        gap: 16px;
        align-items: end;
    }

    .product-filter-field {
        min-width: 0;
    }

    .product-filter-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 700;
        color: var(--admin-text, #1f2937);
    }

    .product-filter-field input,
    .product-filter-field select {
        width: 100%;
        height: 44px;
        padding: 0 13px;
        border: 1px solid rgba(15, 23, 42, .12);
        border-radius: 10px;
        background: #fff;
        color: #111827;
        font: inherit;
        font-size: 13px;
        outline: none;
        transition:
            border-color .2s ease,
            box-shadow .2s ease;
    }

    .product-filter-field input:focus,
    .product-filter-field select:focus {
        border-color: rgba(17, 24, 39, .4);
        box-shadow: 0 0 0 3px rgba(17, 24, 39, .06);
    }

    .product-input-icon {
        position: relative;
    }

    .product-input-icon > i {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        font-size: 13px;
        opacity: .45;
        pointer-events: none;
    }

    .product-input-icon input {
        padding-left: 39px;
    }

    .product-filter-submit {
        display: flex;
    }

    .product-filter-submit .admin-button {
        min-height: 44px;
        white-space: nowrap;
    }

    .admin-button-small {
        min-height: 36px;
        padding: 8px 13px;
        font-size: 12px;
    }


    /*
    |--------------------------------------------------------------------------
    | Product Panel
    |--------------------------------------------------------------------------
    */

    .product-list-panel {
        overflow: hidden;
    }

    .product-results-summary {
        font-size: 12px;
        color: #6b7280;
    }

    .product-results-summary strong {
        color: #111827;
    }


    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    .product-table {
        min-width: 1040px;
    }

    .product-table th {
        white-space: nowrap;
    }

    .product-table td {
        vertical-align: middle;
    }

    .product-table-product {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 250px;
    }

    .product-table-image,
    .product-mobile-image {
        position: relative;
        overflow: hidden;
        flex: 0 0 auto;
        background: #f3f4f6;
        border: 1px solid rgba(15, 23, 42, .08);
    }

    .product-table-image {
        width: 58px;
        height: 68px;
        border-radius: 10px;
    }

    .product-table-image img,
    .product-mobile-image img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .product-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 20px;
    }

    .product-table-info {
        min-width: 0;
    }

    .product-name-row {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }

    .product-name {
        display: block;
        max-width: 260px;
        overflow: hidden;
        color: #111827;
        font-size: 13px;
        font-weight: 750;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-name:hover {
        text-decoration: underline;
    }

    .product-featured-star {
        flex: 0 0 auto;
        color: #d97706;
        font-size: 11px;
    }

    .product-table-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 5px;
        color: #9ca3af;
        font-size: 11px;
    }

    .product-table-meta strong {
        color: #6b7280;
        font-weight: 650;
    }

    .product-meta-separator {
        opacity: .55;
    }


    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    .product-category-list {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
    }

    .product-category-chip,
    .product-category-more {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
    }

    .product-category-chip {
        background: #f3f4f6;
        color: #4b5563;
    }

    .product-category-more {
        background: #111827;
        color: #fff;
    }

    .product-muted {
        color: #9ca3af;
        font-size: 12px;
    }


    /*
    |--------------------------------------------------------------------------
    | Price
    |--------------------------------------------------------------------------
    */

    .product-price {
        display: flex;
        flex-direction: column;
        gap: 2px;
        white-space: nowrap;
    }

    .product-price strong {
        color: #111827;
        font-size: 13px;
        font-weight: 750;
    }

    .product-price del {
        color: #9ca3af;
        font-size: 11px;
    }


    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    .product-inventory {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .product-inventory > strong {
        color: #111827;
        font-size: 13px;
    }

    .product-stock-state {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .product-stock-good {
        color: #15803d;
    }

    .product-stock-low {
        color: #b45309;
    }

    .product-stock-out {
        color: #b91c1c;
    }


    /*
    |--------------------------------------------------------------------------
    | Variant
    |--------------------------------------------------------------------------
    */

    .product-variant-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 28px;
        padding: 5px 9px;
        border-radius: 8px;
        background: #f3f4f6;
        color: #4b5563;
        font-size: 11px;
        font-weight: 700;
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    .product-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 28px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 750;
        white-space: nowrap;
    }

    .product-status-active {
        background: #ecfdf3;
        color: #15803d;
    }

    .product-status-draft {
        background: #fff7ed;
        color: #b45309;
    }

    .product-status-inactive {
        background: #f3f4f6;
        color: #6b7280;
    }


    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    .product-table-actions-heading {
        text-align: right;
    }

    .product-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .product-action-button {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid rgba(15, 23, 42, .1);
        border-radius: 9px;
        background: #fff;
        color: #6b7280;
        font-size: 12px;
        text-decoration: none;
        cursor: pointer;
        transition:
            background .2s ease,
            color .2s ease,
            border-color .2s ease,
            transform .2s ease;
    }

    .product-action-button:hover {
        background: #f9fafb;
        border-color: rgba(15, 23, 42, .2);
        color: #111827;
        transform: translateY(-1px);
    }

    .product-action-edit:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .product-action-delete:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    .product-delete-form {
        display: none;
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    .product-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 18px 20px;
        border-top: 1px solid rgba(15, 23, 42, .08);
    }

    .product-pagination-summary {
        color: #6b7280;
        font-size: 12px;
    }

    .product-pagination-summary strong {
        color: #111827;
    }

    .product-pagination-links {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .product-page-button {
        min-width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 9px;
        border: 1px solid rgba(15, 23, 42, .1);
        border-radius: 8px;
        background: #fff;
        color: #4b5563;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    a.product-page-button:hover {
        background: #f9fafb;
        color: #111827;
    }

    .product-page-current {
        border-color: #111827;
        background: #111827;
        color: #fff;
    }

    .product-page-disabled {
        color: #d1d5db;
        cursor: not-allowed;
    }

    .product-page-dots {
        padding: 0 3px;
        color: #9ca3af;
    }


    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

    .product-empty-state {
        max-width: 520px;
        margin: 0 auto;
        padding: 70px 20px;
        text-align: center;
    }

    .product-empty-icon {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        border-radius: 18px;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 25px;
    }

    .product-empty-state h3 {
        margin: 0 0 8px;
        color: #111827;
        font-size: 18px;
    }

    .product-empty-state p {
        max-width: 400px;
        margin: 0 auto 20px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.7;
    }


    /*
    |--------------------------------------------------------------------------
    | Mobile Cards
    |--------------------------------------------------------------------------
    */

    .product-mobile-list {
        display: none;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Modal
    |--------------------------------------------------------------------------
    */

    .product-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .product-modal.is-open {
        display: flex;
    }

    .product-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(3px);
    }

    .product-modal-dialog {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 430px;
        padding: 30px;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 80px rgba(15, 23, 42, .25);
        text-align: center;
    }

    .product-modal-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 8px;
        background: #f3f4f6;
        color: #6b7280;
        cursor: pointer;
    }

    .product-modal-danger-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 4px auto 17px;
        border-radius: 50%;
        background: #fef2f2;
        color: #dc2626;
        font-size: 22px;
    }

    .product-modal-dialog h3 {
        margin: 0 0 10px;
        color: #111827;
        font-size: 20px;
    }

    .product-modal-dialog p {
        margin: 0;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.65;
    }

    .product-modal-dialog p strong {
        color: #111827;
    }

    .product-modal-note {
        margin-top: 10px !important;
        padding: 10px;
        border-radius: 9px;
        background: #f9fafb;
        font-size: 11px !important;
    }

    .product-modal-actions {
        display: flex;
        justify-content: center;
        gap: 9px;
        margin-top: 24px;
    }

    .product-confirm-delete,
    .product-mobile-delete {
        border-color: #dc2626 !important;
        background: #dc2626 !important;
        color: #fff !important;
    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1180px) {

        .product-filter-form {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .product-search-field {
            grid-column: 1 / -1;
        }

        .product-filter-submit {
            align-items: end;
        }

    }


    @media (max-width: 820px) {

        .product-desktop-table {
            display: none;
        }

        .product-mobile-list {
            display: grid;
            gap: 12px;
            padding: 15px;
        }

        .product-mobile-card {
            padding: 14px;
            border: 1px solid rgba(15, 23, 42, .09);
            border-radius: 13px;
            background: #fff;
        }

        .product-mobile-card-top {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .product-mobile-image {
            width: 70px;
            height: 82px;
            border-radius: 10px;
        }

        .product-mobile-main {
            flex: 1;
            min-width: 0;
        }

        .product-mobile-main .product-name {
            max-width: 100%;
        }

        .product-mobile-main .product-price {
            margin-top: 9px;
        }

        .product-mobile-details {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-top: 14px;
            padding: 12px 0;
            border-top: 1px solid rgba(15, 23, 42, .07);
            border-bottom: 1px solid rgba(15, 23, 42, .07);
        }

        .product-mobile-label {
            display: block;
            margin-bottom: 5px;
            color: #9ca3af;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .product-mobile-value {
            color: #111827;
            font-size: 11px;
            font-weight: 700;
        }

        .product-mobile-details .product-stock-state {
            display: flex;
            margin-top: 3px;
        }

        .product-mobile-card > .product-category-list {
            margin-top: 12px;
        }

        .product-mobile-actions {
            display: grid;
            grid-template-columns:
                1fr
                1fr
                auto;
            gap: 7px;
            margin-top: 13px;
        }

        .product-mobile-actions .admin-button {
            justify-content: center;
        }

        .product-mobile-delete {
            min-width: 43px;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

    }


    @media (max-width: 640px) {

        .product-filter-form {
            grid-template-columns: 1fr;
        }

        .product-search-field {
            grid-column: auto;
        }

        .product-filter-submit .admin-button {
            width: 100%;
            justify-content: center;
        }

        .product-pagination {
            flex-direction: column;
            align-items: flex-start;
        }

        .product-pagination-links {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 3px;
        }

        .product-modal-dialog {
            padding: 26px 18px 20px;
        }

        .product-modal-actions {
            flex-direction: column-reverse;
        }

        .product-modal-actions .admin-button {
            width: 100%;
            justify-content: center;
        }

    }


    @media (max-width: 460px) {

        .product-mobile-details {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .product-mobile-actions {
            grid-template-columns:
                1fr
                1fr;
        }

        .product-mobile-delete {
            grid-column: 1 / -1;
            width: 100%;
        }

    }


/* ============================================================
   ARIZONA ADMIN DASHBOARD VISUAL SYSTEM — PRODUCTS
============================================================ */
.admin-page-header{margin-bottom:18px}
.admin-page-header .admin-page-eyebrow,
.product-filter-panel .admin-panel-eyebrow,
.product-list-panel .admin-panel-eyebrow{color:#635bff;font-size:9px;font-weight:800;letter-spacing:.12em}
.admin-page-header h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800;letter-spacing:-.025em}
.admin-page-header p{color:#7b8497;font-size:12px}
.admin-page-header .admin-button{min-height:38px;border-radius:7px;font-size:10px}
.admin-page-header .admin-button-primary,
.product-filter-submit .admin-button-primary{border-color:#635bff;background:#635bff;color:#fff}

.product-statistics-grid{gap:14px;margin-bottom:18px}
.product-statistics-grid .admin-stat-card{min-height:116px;padding:17px;border:1px solid #e6eaf1;border-radius:11px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.02)}
.product-statistics-grid .admin-stat-label{color:#687386;font-size:10px;font-weight:600}
.product-statistics-grid .admin-stat-value{display:block;margin-top:13px;color:#111827;font-size:24px;font-weight:800;line-height:1}
.product-statistics-grid .admin-stat-icon{width:34px;height:34px;border:0;border-radius:9px;background:#eef0ff;color:#635bff;font-size:13px}
.product-statistics-grid .admin-stat-card:nth-child(2) .admin-stat-icon{background:#eaf8f1;color:#13875b}
.product-statistics-grid .admin-stat-card:nth-child(3) .admin-stat-icon{background:#fff4df;color:#d88716}
.product-statistics-grid .admin-stat-card:nth-child(4) .admin-stat-icon{background:#fff0f0;color:#d14343}
.product-stat-caption{font-size:9px;color:#8a93a4}

.product-filter-panel,.product-list-panel{margin-bottom:18px;border:1px solid #e6eaf1;border-radius:11px;background:#fff;box-shadow:none}
.product-filter-panel .admin-panel-header,.product-list-panel .admin-panel-header{padding:16px 18px;border-bottom:1px solid #edf0f5}
.product-filter-panel .admin-panel-header h3,.product-list-panel .admin-panel-header h3{margin:3px 0 0;color:#172033;font-size:14px;font-weight:800}
.product-filter-form{padding:15px 18px 18px;gap:12px}
.product-filter-field label{margin-bottom:6px;color:#687386;font-size:9px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
.product-filter-field input,.product-filter-field select{height:40px;border-color:#e0e5ed;border-radius:7px;color:#344054;font-size:11px}
.product-filter-submit .admin-button{min-height:40px;border-radius:7px;font-size:10px}
.product-results-summary{color:#8a93a4;font-size:10px}
.product-table thead{background:#fbfcfe}
.product-table th{padding:11px 13px;border-bottom:1px solid #edf0f5;color:#7c8596;font-size:8px;font-weight:800;letter-spacing:.07em;text-transform:uppercase}
.product-table td{padding:12px 13px;border-bottom:1px solid #f0f2f6;color:#3f4858;font-size:10px}
.product-table-image{width:48px;height:56px;border-radius:8px}
.product-name{color:#172033;font-size:11px;font-weight:800}
.product-table-meta{font-size:9px}
.product-price strong,.product-inventory>strong{font-size:11px}
.product-category-chip,.product-category-more,.product-variant-count,.product-status{min-height:23px;padding:4px 7px;font-size:9px}
.product-action-button{width:30px;height:30px;border-radius:7px;font-size:10px}
.product-pagination{padding:14px 18px}
.product-page-button{min-width:30px;height:30px;border-radius:7px;font-size:10px}
.product-page-current{border-color:#635bff;background:#635bff}
.product-empty-state{padding:55px 20px}
.product-empty-icon{background:#eef0ff;color:#635bff}

/* Feedback popup */
.product-feedback-popup{position:fixed;inset:0;z-index:10050;display:flex;align-items:center;justify-content:center;padding:20px}
.product-feedback-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)}
.product-feedback-dialog{position:relative;z-index:2;width:100%;max-width:430px;padding:30px;border:1px solid #e6eaf1;border-radius:14px;background:#fff;box-shadow:0 24px 80px rgba(15,23,42,.24);text-align:center}
.product-feedback-close{position:absolute;top:12px;right:12px;width:32px;height:32px;border:0;border-radius:7px;background:#f3f5f8;color:#687386;cursor:pointer}
.product-feedback-icon{display:flex;align-items:center;justify-content:center;width:58px;height:58px;margin:0 auto 15px;border-radius:50%;font-size:21px}
.product-feedback-popup.is-success .product-feedback-icon{background:#eaf8f1;color:#13875b}
.product-feedback-popup.is-error .product-feedback-icon{background:#fff0f0;color:#d14343}
.product-feedback-popup.is-warning .product-feedback-icon{background:#fff4df;color:#d88716}
.product-feedback-dialog h3{margin:5px 0 8px;color:#172033;font-size:18px;font-weight:800}
.product-feedback-dialog p{margin:0;color:#687386;font-size:11px;line-height:1.65}
.product-feedback-errors{margin:14px 0 0;padding:12px 16px;border:1px solid #fecaca;border-radius:9px;background:#fff7f7;color:#b42318;font-size:10px;line-height:1.6;list-style-position:inside;text-align:left}
.product-feedback-ok{min-width:100px;margin-top:20px;justify-content:center;border-color:#635bff!important;background:#635bff!important}
body.product-feedback-open{overflow:hidden}

</style>

@endpush


@push('page-scripts')

<script>
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const modal =
        document.getElementById(
            'productDeleteModal'
        );

    const productName =
        document.getElementById(
            'productDeleteName'
        );

    const confirmButton =
        document.getElementById(
            'confirmProductDelete'
        );

    let activeDeleteForm = null;


    /*
    |--------------------------------------------------------------------------
    | Open Delete Modal
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '[data-product-delete]'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    const formId =
                        button.dataset
                            .deleteForm;

                    activeDeleteForm =
                        document.getElementById(
                            formId
                        );

                    if (!activeDeleteForm) {
                        return;
                    }

                    productName.textContent =
                        button.dataset
                            .productName ||
                        'this product';

                    modal.classList.add(
                        'is-open'
                    );

                    modal.setAttribute(
                        'aria-hidden',
                        'false'
                    );

                    document.body.style
                        .overflow = 'hidden';
                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Close Delete Modal
    |--------------------------------------------------------------------------
    */

    function closeDeleteModal() {

        modal.classList.remove(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.style
            .overflow = '';

        activeDeleteForm = null;
    }


    document
        .querySelectorAll(
            '[data-close-product-modal]'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                closeDeleteModal
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Escape Key
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                modal.classList.contains(
                    'is-open'
                )
            ) {
                closeDeleteModal();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Confirm Delete
    |--------------------------------------------------------------------------
    */

    confirmButton.addEventListener(
        'click',
        function () {

            if (!activeDeleteForm) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent double click
            |--------------------------------------------------------------------------
            */

            confirmButton.disabled = true;

            confirmButton.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';

            activeDeleteForm.submit();
        }
    );

});

</script>


<script>
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    const feedback = document.getElementById('productFeedbackPopup');
    if (!feedback) return;

    document.body.classList.add('product-feedback-open');

    function closeFeedback() {
        feedback.remove();
        document.body.classList.remove('product-feedback-open');
    }

    feedback.querySelectorAll('[data-product-feedback-close]').forEach(function (button) {
        button.addEventListener('click', closeFeedback);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && document.body.contains(feedback)) {
            closeFeedback();
        }
    });
});
</script>


<style>
/* ============================================================
   ARIZONA ADMIN — PRODUCT DROPDOWN UI
   Applies to Index, Create and Edit
============================================================ */
.product-admin-select,
.product-filter-field select,
.product-form-panel select,
.product-form-modal select {
    width:100%;
    min-height:40px;
    padding:0 38px 0 11px!important;
    border:1px solid #e0e5ed!important;
    border-radius:7px!important;
    background-color:#fff!important;
    background-image:
        linear-gradient(45deg,transparent 50%,#7b8497 50%),
        linear-gradient(135deg,#7b8497 50%,transparent 50%)!important;
    background-position:
        calc(100% - 16px) 50%,
        calc(100% - 11px) 50%!important;
    background-size:5px 5px,5px 5px!important;
    background-repeat:no-repeat!important;
    color:#344054!important;
    font-size:11px!important;
    font-weight:600!important;
    appearance:none!important;
    -webkit-appearance:none!important;
    -moz-appearance:none!important;
    cursor:pointer;
    transition:border-color .18s ease,box-shadow .18s ease,background-color .18s ease
}
.product-admin-select:hover,
.product-filter-field select:hover,
.product-form-panel select:hover,
.product-form-modal select:hover {
    border-color:#c9ced8!important;
    background-color:#fbfcff!important
}
.product-admin-select:focus,
.product-filter-field select:focus,
.product-form-panel select:focus,
.product-form-modal select:focus {
    border-color:#635bff!important;
    background-color:#fff!important;
    box-shadow:0 0 0 3px rgba(99,91,255,.10)!important;
    outline:none!important
}
.product-admin-select:disabled,
.product-filter-field select:disabled,
.product-form-panel select:disabled,
.product-form-modal select:disabled {
    background-color:#f5f6f8!important;
    color:#98a2b3!important;
    cursor:not-allowed;
    opacity:1
}
.product-admin-select option,
.product-filter-field select option,
.product-form-panel select option,
.product-form-modal select option {
    background:#fff!important;
    color:#344054!important;
    font-size:11px!important
}

/* Multiple-value dropdown/list controls */
.product-form-panel select[multiple] {
    min-height:132px!important;
    padding:6px!important;
    background-image:none!important;
    overflow:auto
}
.product-form-panel select[multiple] option {
    margin:2px 0;
    padding:8px 9px;
    border-radius:6px;
    cursor:pointer
}
.product-form-panel select[multiple] option:checked {
    background:#eeedff linear-gradient(0deg,#eeedff,#eeedff)!important;
    color:#5149d8!important;
    font-weight:700!important
}
</style>


<style>
/* ============================================================
   ARIZONA PRODUCT INDEX — CUSTOM FILTER SELECT OPTIONS UI V1
============================================================ */
.az-index-select{position:relative;width:100%}
.az-index-select-native{position:absolute!important;width:1px!important;height:1px!important;margin:-1px!important;padding:0!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important;opacity:0!important;pointer-events:none!important}
.az-index-select-button{display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;min-height:40px;padding:0 11px;border:1px solid #e0e5ed;border-radius:7px;background:#fff;color:#344054;font-size:11px;font-weight:600;text-align:left;cursor:pointer;transition:.18s ease}
.az-index-select-button:hover{border-color:#c9ced8;background:#fbfcff}
.az-index-select.is-open .az-index-select-button,.az-index-select-button:focus{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.10);outline:none}
.az-index-select-chevron{color:#7b8497;font-size:9px;transition:transform .18s ease}
.az-index-select.is-open .az-index-select-chevron{transform:rotate(180deg)}
.az-index-select-menu{position:absolute;z-index:12000;top:calc(100% + 6px);left:0;display:none;width:100%;max-height:240px;overflow:auto;padding:5px;border:1px solid #e0e5ed;border-radius:9px;background:#fff;box-shadow:0 14px 38px rgba(15,23,42,.16)}
.az-index-select.is-open .az-index-select-menu{display:block}
.az-index-select-option{display:flex;align-items:center;justify-content:space-between;width:100%;min-height:34px;padding:7px 9px;border:0;border-radius:6px;background:transparent;color:#344054;font-size:11px;font-weight:600;text-align:left;cursor:pointer}
.az-index-select-option:hover,.az-index-select-option.is-focused{background:#f5f4ff;color:#5149d8}
.az-index-select-option.is-selected{background:#eeedff;color:#5149d8;font-weight:800}
.az-index-select-option.is-selected::after{content:"\2713";color:#635bff;font-size:11px;font-weight:900}
</style>

<script>
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    const selector = '.product-filter-field select:not([multiple])';

    function closeAll(except) {
        document.querySelectorAll('.az-index-select.is-open').forEach(function (wrapper) {
            if (wrapper !== except) {
                wrapper.classList.remove('is-open');
                const b = wrapper.querySelector('.az-index-select-button');
                if (b) b.setAttribute('aria-expanded','false');
            }
        });
    }

    document.querySelectorAll(selector).forEach(function (select) {
        if (select.dataset.azIndexSelectReady === '1') return;
        select.dataset.azIndexSelectReady = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'az-index-select';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'az-index-select-button';
        button.setAttribute('aria-haspopup','listbox');
        button.setAttribute('aria-expanded','false');

        const value = document.createElement('span');
        const icon = document.createElement('i');
        icon.className = 'fa-solid fa-chevron-down az-index-select-chevron';
        button.append(value, icon);

        const menu = document.createElement('div');
        menu.className = 'az-index-select-menu';
        menu.setAttribute('role','listbox');

        select.parentNode.insertBefore(wrapper, select);
        wrapper.append(select, button, menu);
        select.classList.add('az-index-select-native');

        let focusIndex = -1;

        function render() {
            menu.innerHTML = '';
            Array.from(select.options).forEach(function (option) {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'az-index-select-option';
                item.dataset.value = option.value;
                item.textContent = option.textContent.trim();
                item.disabled = option.disabled;
                item.setAttribute('role','option');

                if (option.value === select.value) {
                    item.classList.add('is-selected');
                    item.setAttribute('aria-selected','true');
                } else {
                    item.setAttribute('aria-selected','false');
                }

                item.addEventListener('click', function () {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change',{bubbles:true}));
                    render();
                    closeAll();
                    button.focus();
                });
                menu.appendChild(item);
            });

            const selected = select.options[select.selectedIndex];
            value.textContent = selected ? selected.textContent.trim() : 'Select';
        }

        function options() {
            return Array.from(menu.querySelectorAll('.az-index-select-option:not(:disabled)'));
        }

        function paintFocus() {
            const items = options();
            items.forEach(x => x.classList.remove('is-focused'));
            if (items[focusIndex]) {
                items[focusIndex].classList.add('is-focused');
                items[focusIndex].scrollIntoView({block:'nearest'});
            }
        }

        function open() {
            closeAll(wrapper);
            wrapper.classList.add('is-open');
            button.setAttribute('aria-expanded','true');
            const items = options();
            focusIndex = items.findIndex(x => x.dataset.value === String(select.value));
            if (focusIndex < 0 && items.length) focusIndex = 0;
            paintFocus();
        }

        button.addEventListener('click', function () {
            if (wrapper.classList.contains('is-open')) {
                closeAll();
            } else {
                open();
            }
        });

        button.addEventListener('keydown', function (e) {
            const items = options();
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!wrapper.classList.contains('is-open')) { open(); return; }
                if (!items.length) return;
                focusIndex += e.key === 'ArrowDown' ? 1 : -1;
                if (focusIndex >= items.length) focusIndex = 0;
                if (focusIndex < 0) focusIndex = items.length - 1;
                paintFocus();
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (!wrapper.classList.contains('is-open')) { open(); return; }
                if (items[focusIndex]) items[focusIndex].click();
            } else if (e.key === 'Escape') {
                closeAll();
            }
        });

        select.addEventListener('change', render);
        render();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.az-index-select')) closeAll();
    });
});
</script>

@endpush