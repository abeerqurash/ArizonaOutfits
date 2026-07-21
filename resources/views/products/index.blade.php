@extends('layouts.app')

@section('title', 'Products')

@section(
    'meta_description',
    'Browse products, compare prices, explore categories and find the right product for your needs.'
)

@section('content')
<div class="page-wrapper">
    <div class="strip-wrapper">
        <div class="wrapper">
            <div class="strip-container">
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
            </div>
        </div>
    </div>

    <div class="services">
        <div class="service-wrapper">
            <section class="products-page">
                <div class="container">
                    <ul class="bread-crumbs list-style-none fs-12 text-uppercase letter-space-4px mb-10px d-flex gap-10px">
                        <li>
                            <a href="{{ route('home-page') }}" class="text-decoration-none text-color-dark">
                                Home
                            </a>
                        </li>
                        <li aria-current="page">Products</li>
                    </ul>

                    <div class="shop-layout">
                        <aside class="shop-sidebar">
                            <form method="GET" action="{{ route('products.index') }}" class="shop-filter-form">
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Search products"
                                    value="{{ request('search') }}"
                                    class="input-type-field mb-10px fs-12"
                                >

                                <h4 class="fs-24 text-color-dark mb-10px">Categories</h4>

                                <label class="fs-14 text-uppercase letter-space-4px mb-10px cursor-pointer d-flex align-items-center">
                                    <input
                                        type="radio"
                                        name="category"
                                        value=""
                                        class="input-radio"
                                        {{ request('category') ? '' : 'checked' }}
                                    >
                                    All products
                                </label>

                                @foreach ($categories as $cat)
                                    <label class="fs-14 text-uppercase letter-space-4px mb-10px cursor-pointer d-flex align-items-center">
                                        <input
                                            type="radio"
                                            name="category"
                                            value="{{ $cat->slug }}"
                                            class="input-radio"
                                            {{ request('category') === $cat->slug ? 'checked' : '' }}
                                        >
                                        {{ $cat->title }}
                                    </label>

                                    @foreach ($cat->children as $child)
                                        <label
                                            class="fs-14 text-uppercase letter-space-4px mb-10px cursor-pointer d-flex align-items-center"
                                            style="padding-left: 15px;"
                                        >
                                            <input
                                                type="radio"
                                                name="category"
                                                value="{{ $child->slug }}"
                                                class="input-radio"
                                                {{ request('category') === $child->slug ? 'checked' : '' }}
                                            >
                                            {{ $child->title }}
                                        </label>
                                    @endforeach
                                @endforeach

                                <h4 class="fs-24 text-color-dark mb-10px">Price</h4>

                                <div class="d-flex gap-10px mb-10px">
                                    <input
                                        type="number"
                                        name="min_price"
                                        placeholder="Min"
                                        value="{{ request('min_price') }}"
                                        min="0"
                                        step="0.01"
                                        class="input-type-field fs-12"
                                    >

                                    <input
                                        type="number"
                                        name="max_price"
                                        placeholder="Max"
                                        value="{{ request('max_price') }}"
                                        min="0"
                                        step="0.01"
                                        class="input-type-field fs-12"
                                    >
                                </div>

                                @if (request('sort'))
                                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                                @endif

                                <div class="d-flex gap-10px">
                                    <button type="submit" class="filter-btn">Filter</button>

                                    @if (request()->hasAny(['search', 'category', 'min_price', 'max_price', 'sort']))
                                        <a href="{{ route('products.index') }}" class="filter-btn text-decoration-none">
                                            Reset
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </aside>

                        <div class="shop-content">
                            <div class="shop-topbar d-flex justify-content-between">
                                <div class="fs-12 text-uppercase letter-space-4px">
                                    @if ($products->total() > 0)
                                        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
                                        of {{ $products->total() }} products
                                    @else
                                        No products found
                                    @endif
                                </div>

                                <form
                                    method="GET"
                                    action="{{ route('products.index') }}"
                                    id="sort-form"
                                    class="custom-sort-form"
                                >
                                    @foreach (request()->except(['sort', 'page']) as $key => $value)
                                        @if (is_array($value))
                                            @foreach ($value as $nestedValue)
                                                <input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">
                                            @endforeach
                                        @else
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endif
                                    @endforeach

                                    <input type="hidden" name="sort" id="sort-value" value="{{ request('sort') }}">

                                    @php
                                        $currentSort = request('sort');

                                        $sortLabel = match ($currentSort) {
                                            'price_low' => 'Price Low to High',
                                            'price_high' => 'Price High to Low',
                                            'popular' => 'Popularity',
                                            'rating' => 'Rating',
                                            default => 'Latest',
                                        };
                                    @endphp

                                    <div class="custom-sort-dropdown">
                                        <button
                                            type="button"
                                            class="sort-trigger"
                                            id="sort-trigger"
                                            aria-expanded="false"
                                            aria-controls="sort-options"
                                        >
                                            <span id="sort-label" class="sort-label">{{ $sortLabel }}</span>
                                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                        </button>

                                        <div class="sort-options" id="sort-options" role="listbox">
                                            @foreach ([
                                                '' => 'Latest',
                                                'price_low' => 'Price Low to High',
                                                'price_high' => 'Price High to Low',
                                                'popular' => 'Popularity',
                                                'rating' => 'Rating',
                                            ] as $sortValue => $label)
                                                <div
                                                    class="sort-option {{ request('sort', '') === $sortValue ? 'active' : '' }}"
                                                    data-value="{{ $sortValue }}"
                                                    role="option"
                                                    aria-selected="{{ request('sort', '') === $sortValue ? 'true' : 'false' }}"
                                                    tabindex="0"
                                                >
                                                    {{ $label }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </form>
                            </div>

                            @if ($products->isNotEmpty())
                                <div class="products-grid">
                                    @foreach ($products as $product)
                                        @include('products.partials.product-card', ['product' => $product])
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-products">
                                    <h2 class="fs-24 text-color-dark mb-10px">No products found</h2>
                                    <p class="text-color-body fs-16">
                                        Try changing your search, category or price filters.
                                    </p>
                                </div>
                            @endif

                            @if ($products->hasPages())
                                <div class="shop-pagination">
                                    {{ $products->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="product-quick-view-modal" class="product-quick-view-modal" aria-hidden="true">
    <div class="product-quick-view-backdrop" data-close-product-popup></div>

    <div
        class="product-quick-view-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quick-view-product-title"
    >
        <button
            type="button"
            class="product-popup-close"
            data-close-product-popup
            aria-label="Close product popup"
        >
            ×
        </button>

        <div id="product-quick-view-content" class="product-quick-view-content">
            <div class="product-popup-loader">Loading product...</div>
        </div>
    </div>
</div>
@endsection