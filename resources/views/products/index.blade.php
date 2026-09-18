@extends('layouts.app')

@section('title', 'Products')

@section(
'meta_description',
'Browse products, compare prices, explore categories and find the right product for your needs.'
)

@section('content')

@php
/*
|--------------------------------------------------------------------------
| Normalize filter values
|--------------------------------------------------------------------------
*/

$selectedSearch = is_string(request('search'))
? request('search')
: '';

$selectedCategories = array_values(
array_filter(
is_array(request('categories'))
? request('categories')
: []
)
);

$selectedMinPrice = is_scalar(request('min_price'))
? (string) request('min_price')
: '';

$selectedMaxPrice = is_scalar(request('max_price'))
? (string) request('max_price')
: '';

$selectedRatings = array_values(
array_filter(
is_array(request('ratings'))
? request('ratings')
: []
)
);

$selectedAvailability = array_values(
array_filter(
is_array(request('availability'))
? request('availability')
: []
)
);

$selectedOffers = array_values(
array_filter(
is_array(request('offers'))
? request('offers')
: []
)
);

$selectedOptions = request('options', []);

if (!is_array($selectedOptions)) {
$selectedOptions = [];
}

$selectedSort = is_string(request('sort'))
? request('sort')
: 'newest';

$minimumAvailablePrice =
isset($priceRange['min'])
&& is_numeric($priceRange['min'])
? $priceRange['min']
: 0;

$maximumAvailablePrice =
isset($priceRange['max'])
&& is_numeric($priceRange['max'])
? $priceRange['max']
: 0;

$sortLabels = [
'newest' => 'Latest',
'price_low' => 'Price Low to High',
'price_high' => 'Price High to Low',
'popular' => 'Popularity',
'best_selling' => 'Best Sellers',
'rating' => 'Highest Rated',
'discount' => 'Biggest Discount',
];

$sortLabel = $sortLabels[$selectedSort] ?? 'Latest';

$availabilityOptions = [
'in_stock' => 'In stock',
'out_of_stock' => 'Out of stock',
];

$offerOptions = [
'on_sale' => 'On sale',
];

$ratingOptions = [
'5' => '5 stars',
'4' => '4 stars and above',
'3' => '3 stars and above',
'2' => '2 stars and above',
'1' => '1 star and above',
];

$hasFilters =
$selectedSearch !== ''
|| !empty($selectedCategories)
|| $selectedMinPrice !== ''
|| $selectedMaxPrice !== ''
|| !empty($selectedRatings)
|| !empty($selectedAvailability)
|| !empty($selectedOffers)
|| !empty($selectedOptions)
|| $selectedSort !== 'newest';
@endphp

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
                            <a
                                href="{{ route('home-page') }}"
                                class="text-decoration-none text-color-dark">
                                Home
                            </a>
                        </li>

                        <li aria-current="page">
                            Products
                        </li>

                    </ul>

                    {{-- ============================================================
     SHOP BY CATEGORY
============================================================ --}}

                    @if ($categories->isNotEmpty())

                    <section class="shop-category-section">

                        <div class="shop-category-heading">

                            <div>
                                <span class="shop-category-eyebrow">
                                    Explore Collections
                                </span>

                                <h2 class="shop-category-title">
                                    Shop by Category
                                </h2>
                            </div>

                            @if (!empty($selectedCategories))

                            <a
                                href="{{ route('products.index') }}"
                                class="shop-category-view-all">
                                View All Products

                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                            @endif

                        </div>


                        <div class="shop-category-grid">

                            @foreach ($categories as $category)

                            @php

                            /*
                            |--------------------------------------------------------------------------
                            | Category Image
                            |--------------------------------------------------------------------------
                            */

                            $categoryImage = null;

                            if (!empty($category->featured_image)) {

                            $categoryImage = asset(
                            'storage/' .
                            ltrim(
                            $category->featured_image,
                            '/'
                            )
                            );

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Is Category Selected?
                            |--------------------------------------------------------------------------
                            */

                            $categorySelected = in_array(
                            $category->slug,
                            $selectedCategories,
                            true
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Product Count
                            |--------------------------------------------------------------------------
                            |
                            | Count active products directly attached to this category.
                            |
                            */

                            $categoryProductCount =
                            (int) $category->active_products_count;

                            @endphp


                            <a
                                href="{{ route('products.index', [
                    'categories' => [
                        $category->slug
                    ]
                ]) }}"
                                class="shop-category-card {{ $categorySelected ? 'is-active' : '' }}"
                                aria-label="Shop {{ $category->title }}">

                                <div class="shop-category-image-wrapper">

                                    @if ($categoryImage)

                                    <img
                                        src="{{ $categoryImage }}"
                                        alt="{{ $category->title }}"
                                        class="shop-category-image"
                                        loading="lazy"
                                        width="600"
                                        height="750">

                                    @else

                                    <div class="shop-category-image-placeholder">

                                        <i class="fa-regular fa-image"></i>

                                    </div>

                                    @endif


                                    <div class="shop-category-overlay"></div>


                                    @if ($categorySelected)

                                    <span class="shop-category-selected-badge">

                                        <i class="fa-solid fa-check"></i>

                                        Selected

                                    </span>

                                    @endif


                                    <div class="shop-category-content">

                                        <span class="shop-category-count">

                                            {{ number_format($categoryProductCount) }}

                                            {{ $categoryProductCount === 1
                                ? 'Product'
                                : 'Products'
                            }}

                                        </span>


                                        <h3 class="shop-category-name">
                                            {{ $category->title }}
                                        </h3>


                                        <span class="shop-category-link">

                                            Shop Now

                                            <i class="fa-solid fa-arrow-right"></i>

                                        </span>

                                    </div>

                                </div>

                            </a>

                            @endforeach

                        </div>

                    </section>

                    @endif

                    <div class="shop-layout">

                        <aside class="shop-sidebar">


                            <form
                                method="GET"
                                action="{{ route('products.index') }}"
                                class="shop-filter-form"
                                id="shop-filter-form">
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Search products"
                                    value="{{ request('search') }}"
                                    class="input-type-field mb-10px fs-12">

                                {{-- Categories --}}
                                <div
                                    class="filter-multi-select mb-20px"
                                    data-multi-select>
                                    <h4 class="fs-24 text-color-dark mb-10px text-capitalize">
                                        Categories
                                    </h4>

                                    <button
                                        type="button"
                                        class="filter-select-trigger input-type-field fs-12 text-uppercase letter-space-3px justify-content-between d-flex"
                                        data-multi-select-trigger
                                        aria-expanded="false">
                                        <span data-multi-select-label>
                                            @if (count($selectedCategories))
                                            {{ count($selectedCategories) }}
                                            {{ count($selectedCategories) === 1 ? 'category' : 'categories' }}
                                            selected
                                            @else
                                            Select categories
                                            @endif
                                        </span>

                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>

                                    <div
                                        class="filter-select-dropdown"
                                        data-multi-select-dropdown>
                                        <div class="filter-select-search-wrapper">
                                            <i class="fa-solid fa-magnifying-glass"></i>

                                            <input
                                                type="text"
                                                class="filter-select-search fs-12"
                                                placeholder="Search categories"
                                                autocomplete="off"
                                                data-multi-select-search>
                                        </div>

                                        <div class="filter-select-options">

                                            @foreach ($categories as $category)

                                            <label
                                                class="filter-select-option"
                                                data-search-text="{{ strtolower($category->title) }}">
                                                <input
                                                    type="checkbox"
                                                    name="categories[]"
                                                    value="{{ $category->slug }}"
                                                    @checked(
                                                    in_array(
                                                    $category->slug,
                                                $selectedCategories,
                                                true
                                                )
                                                )
                                                data-multi-select-checkbox
                                                >

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    {{ $category->title }}
                                                </span>
                                            </label>

                                            @foreach ($category->children as $child)

                                            <label
                                                class="filter-select-option filter-child-option"
                                                data-search-text="{{ strtolower($child->title) }}">
                                                <input
                                                    type="checkbox"
                                                    name="categories[]"
                                                    value="{{ $child->slug }}"
                                                    @checked(
                                                    in_array(
                                                    $child->slug,
                                                $selectedCategories,
                                                true
                                                )
                                                )
                                                data-multi-select-checkbox
                                                >

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    {{ $child->title }}
                                                </span>
                                            </label>

                                            @endforeach

                                            @endforeach

                                            <div
                                                class="filter-no-results"
                                                data-multi-select-empty
                                                hidden>
                                                No categories found
                                            </div>
                                        </div>

                                        <div class="filter-select-actions">
                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-select-all>
                                                Select all
                                            </button>

                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-clear-all>
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Variants --}}
                                {{-- Dynamic variant options: Color, Size, Material, etc. --}}
                                @foreach ($filterOptions as $option)

                                @php
                                $selectedOptionValues = array_map(
                                'strval',
                                (array) ($selectedOptions[$option->id] ?? [])
                                );
                                @endphp

                                <div
                                    class="filter-multi-select mb-20px"
                                    data-multi-select>
                                    <h4 class="fs-24 text-color-dark mb-10px text-capitalize">
                                        {{ $option->name }}
                                    </h4>

                                    <button
                                        type="button"
                                        class="filter-select-trigger input-type-field fs-12 text-uppercase letter-space-3px justify-content-between d-flex"
                                        data-multi-select-trigger
                                        aria-expanded="false">
                                        <span
                                            data-multi-select-label
                                            data-default-label="Select {{ strtolower($option->name) }}">
                                            @if (count($selectedOptionValues))
                                            {{ count($selectedOptionValues) }}
                                            selected
                                            @else
                                            Select {{ strtolower($option->name) }}
                                            @endif
                                        </span>

                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>

                                    <div
                                        class="filter-select-dropdown"
                                        data-multi-select-dropdown>
                                        <div class="filter-select-search-wrapper">
                                            <i class="fa-solid fa-magnifying-glass"></i>

                                            <input
                                                type="text"
                                                class="filter-select-search fs-12"
                                                placeholder="Search {{ strtolower($option->name) }}"
                                                autocomplete="off"
                                                data-multi-select-search>
                                        </div>

                                        <div class="filter-select-options">

                                            @forelse ($option->values as $value)

                                            @php
                                            $valueLabel =
                                            $value->value
                                            ?? $value->name
                                            ?? $value->title
                                            ?? 'Value ' . $value->id;
                                            @endphp

                                            <label
                                                class="filter-select-option"
                                                data-search-text="{{ strtolower($valueLabel) }}">
                                                <input
                                                    type="checkbox"
                                                    name="options[{{ $option->id }}][]"
                                                    value="{{ $value->id }}"
                                                    @checked(
                                                    in_array(
                                                    (string) $value->id,
                                                $selectedOptionValues,
                                                true
                                                )
                                                )
                                                data-multi-select-checkbox
                                                >

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    {{ $valueLabel }}
                                                </span>
                                            </label>

                                            @empty

                                            <div class="filter-no-results">
                                                No {{ strtolower($option->name) }} values available
                                            </div>

                                            @endforelse

                                            <div
                                                class="filter-no-results"
                                                data-multi-select-empty
                                                hidden>
                                                No matching values found
                                            </div>
                                        </div>

                                        <div class="filter-select-actions">
                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-select-all>
                                                Select all
                                            </button>

                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-clear-all>
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                @endforeach

                                {{-- Customer rating --}}
                                <div
                                    class="filter-multi-select mb-20px"
                                    data-multi-select>
                                    <h4 class="fs-24 text-color-dark mb-10px text-capitalize">
                                        Customer rating
                                    </h4>

                                    <button
                                        type="button"
                                        class="filter-select-trigger input-type-field fs-12 text-uppercase letter-space-3px justify-content-between d-flex"
                                        data-multi-select-trigger
                                        aria-expanded="false">
                                        <span data-multi-select-label>
                                            @if (count($selectedRatings))
                                            {{ count($selectedRatings) }}
                                            {{ count($selectedRatings) === 1 ? 'rating' : 'ratings' }}
                                            selected
                                            @else
                                            Select customer rating
                                            @endif
                                        </span>

                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>

                                    <div
                                        class="filter-select-dropdown"
                                        data-multi-select-dropdown>
                                        <div class="filter-select-search-wrapper">
                                            <i class="fa-solid fa-magnifying-glass"></i>

                                            <input
                                                type="text"
                                                class="filter-select-search fs-12"
                                                placeholder="Search ratings"
                                                autocomplete="off"
                                                data-multi-select-search>
                                        </div>

                                        <div class="filter-select-options">

                                            @php
                                            $selectedRatingValues = array_map(
                                            'strval',
                                            $selectedRatings
                                            );
                                            @endphp

                                            @foreach ($ratingOptions as $value => $label)

                                            <label
                                                class="filter-select-option"
                                                data-search-text="{{ strtolower($label) }}">
                                                <input
                                                    type="checkbox"
                                                    name="ratings[]"
                                                    value="{{ $value }}"
                                                    @checked(
                                                    in_array(
                                                    (string) $value,
                                                    $selectedRatingValues,
                                                    true
                                                    )
                                                    )
                                                    data-multi-select-checkbox>

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    <span class="filter-rating-stars">
                                                        @for ($star = 1; $star <= 5; $star++)
                                                            <i
                                                            class="{{
                                    $star <= (int) $value
                                        ? 'fa-solid'
                                        : 'fa-regular'
                                }} fa-star"></i>
                                                            @endfor
                                                    </span>

                                                    {{ $label }}
                                                </span>
                                            </label>

                                            @endforeach

                                            <div
                                                class="filter-no-results"
                                                data-multi-select-empty
                                                hidden>
                                                No ratings found
                                            </div>
                                        </div>

                                        <div class="filter-select-actions">
                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-select-all>
                                                Select all
                                            </button>

                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-clear-all>
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Availability --}}
                                <div
                                    class="filter-multi-select mb-20px"
                                    data-multi-select>
                                    <h4 class="fs-24 text-color-dark mb-10px text-capitalize">
                                        Availability
                                    </h4>

                                    <button
                                        type="button"
                                        class="filter-select-trigger input-type-field fs-12 text-uppercase letter-space-3px justify-content-between d-flex"
                                        data-multi-select-trigger
                                        aria-expanded="false">
                                        <span data-multi-select-label>
                                            @if (count($selectedAvailability))
                                            {{ count($selectedAvailability) }}
                                            selected
                                            @else
                                            Select availability
                                            @endif
                                        </span>

                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>

                                    <div
                                        class="filter-select-dropdown"
                                        data-multi-select-dropdown>
                                        <div class="filter-select-search-wrapper">
                                            <i class="fa-solid fa-magnifying-glass"></i>

                                            <input
                                                type="text"
                                                class="filter-select-search fs-12"
                                                placeholder="Search availability"
                                                autocomplete="off"
                                                data-multi-select-search>
                                        </div>

                                        <div class="filter-select-options">

                                            @foreach ($availabilityOptions as $value => $label)

                                            <label
                                                class="filter-select-option"
                                                data-search-text="{{ strtolower($label) }}">
                                                <input
                                                    type="checkbox"
                                                    name="availability[]"
                                                    value="{{ $value }}"
                                                    @checked(
                                                    in_array(
                                                    $value,
                                                    $selectedAvailability,
                                                    true
                                                    )
                                                    )
                                                    data-multi-select-checkbox>

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    {{ $label }}
                                                </span>
                                            </label>

                                            @endforeach

                                            <div
                                                class="filter-no-results"
                                                data-multi-select-empty
                                                hidden>
                                                No availability found
                                            </div>
                                        </div>

                                        <div class="filter-select-actions">
                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-select-all>
                                                Select all
                                            </button>

                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-clear-all>
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Offers --}}
                                <div
                                    class="filter-multi-select mb-20px"
                                    data-multi-select>
                                    <h4 class="fs-24 text-color-dark mb-10px text-capitalize">
                                        Offers
                                    </h4>

                                    <button
                                        type="button"
                                        class="filter-select-trigger input-type-field fs-12 text-uppercase letter-space-3px justify-content-between d-flex"
                                        data-multi-select-trigger
                                        aria-expanded="false">
                                        <span data-multi-select-label>
                                            @if (count($selectedOffers))
                                            {{ count($selectedOffers) }}
                                            {{ count($selectedOffers) === 1 ? 'offer' : 'offers' }}
                                            selected
                                            @else
                                            Select offers
                                            @endif
                                        </span>

                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>

                                    <div
                                        class="filter-select-dropdown"
                                        data-multi-select-dropdown>
                                        <div class="filter-select-search-wrapper">
                                            <i class="fa-solid fa-magnifying-glass"></i>

                                            <input
                                                type="text"
                                                class="filter-select-search fs-12"
                                                placeholder="Search offers"
                                                autocomplete="off"
                                                data-multi-select-search>
                                        </div>

                                        <div class="filter-select-options">

                                            @foreach ($offerOptions as $value => $label)

                                            <label
                                                class="filter-select-option"
                                                data-search-text="{{ strtolower($label) }}">
                                                <input
                                                    type="checkbox"
                                                    name="offers[]"
                                                    value="{{ $value }}"
                                                    @checked(
                                                    in_array(
                                                    $value,
                                                    $selectedOffers,
                                                    true
                                                    )
                                                    )
                                                    data-multi-select-checkbox>

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    {{ $label }}
                                                </span>
                                            </label>

                                            @endforeach

                                            <div
                                                class="filter-no-results"
                                                data-multi-select-empty
                                                hidden>
                                                No offers found
                                            </div>
                                        </div>

                                        <div class="filter-select-actions">
                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-select-all>
                                                Select all
                                            </button>

                                            <button
                                                type="button"
                                                class="filter-select-action fs-12 text-uppercase letter-space-4px text-decoration-none"
                                                data-clear-all>
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Price remains unchanged --}}
                                <h4 class="fs-24 text-color-dark mb-10px text-capitalize">
                                    Price
                                </h4>

                                <div class="d-flex gap-10px mb-10px">
                                    <input
                                        type="number"
                                        name="min_price"
                                        placeholder="Min"
                                        value="{{ request('min_price') }}"
                                        min="0"
                                        step="0.01"
                                        class="input-type-field fs-12">

                                    <input
                                        type="number"
                                        name="max_price"
                                        placeholder="Max"
                                        value="{{ request('max_price') }}"
                                        min="0"
                                        step="0.01"
                                        class="input-type-field fs-12">
                                </div>

                                @if (request('sort'))
                                <input
                                    type="hidden"
                                    name="sort"
                                    value="{{ request('sort') }}">
                                @endif

                                <div class="d-flex gap-10px">
                                    <button
                                        type="submit"
                                        class="filter-btn btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Filter</div>

                                    </button>

                                    @if (
                                    request()->hasAny([
                                    'search',
                                    'categories',
                                    'variants',
                                    'ratings',
                                    'availability',
                                    'offers',
                                    'min_price',
                                    'max_price',
                                    'sort',
                                    ])
                                    )
                                    <a
                                        href="{{ route('products.index') }}"
                                        class="filter-btn text-decoration-none btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Reset</div>
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
                                    class="custom-sort-form">

                                    {{--
                                    |--------------------------------------------------------------------------
                                    | Preserve scalar filters safely
                                    |--------------------------------------------------------------------------
                                    |
                                    | options is excluded here because it is a nested array and must be
                                    | rendered separately below.
                                    |
                                    --}}

                                    @foreach (
                                    request()->except([
                                    'sort',
                                    'page',
                                    'options',
                                    ]) as $key => $value
                                    )

                                    @if (
                                    is_string($key)
                                    && is_scalar($value)
                                    )

                                    <input
                                        type="hidden"
                                        name="{{ $key }}"
                                        value="{{ (string) $value }}">

                                    @endif

                                    @endforeach

                                    {{--
                                    |--------------------------------------------------------------------------
                                    | Preserve nested option filters safely
                                    |--------------------------------------------------------------------------
                                    --}}

                                    @foreach ($selectedOptions as $optionId => $optionValues)

                                    @php
                                    if (!is_array($optionValues)) {
                                    $optionValues = [
                                    $optionValues,
                                    ];
                                    }
                                    @endphp

                                    @foreach ($optionValues as $optionValue)

                                    @if (
                                    is_scalar($optionId)
                                    && is_scalar($optionValue)
                                    )

                                    <input
                                        type="hidden"
                                        name="options[{{ (string) $optionId }}][]"
                                        value="{{ (string) $optionValue }}">

                                    @endif

                                    @endforeach

                                    @endforeach

                                    <input
                                        type="hidden"
                                        name="sort"
                                        id="sort-value"
                                        value="{{ $selectedSort }}">

                                    <div class="custom-sort-dropdown">

                                        <button
                                            type="button"
                                            class="sort-trigger"
                                            id="sort-trigger"
                                            aria-expanded="false"
                                            aria-controls="sort-options">
                                            <span
                                                id="sort-label"
                                                class="sort-label">
                                                {{ $sortLabel }}
                                            </span>

                                            <i
                                                class="fa-solid fa-chevron-down"
                                                aria-hidden="true"></i>
                                        </button>

                                        <div
                                            class="sort-options"
                                            id="sort-options"
                                            role="listbox">

                                            @foreach ($sortLabels as $sortValue => $label)

                                            <div
                                                class="sort-option {{ $selectedSort === $sortValue ? 'active' : '' }}"
                                                data-value="{{ $sortValue }}"
                                                role="option"
                                                aria-selected="{{ $selectedSort === $sortValue ? 'true' : 'false' }}"
                                                tabindex="0">
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

                                @include(
                                'products.partials.product-card',
                                [
                                'product' => $product,
                                ]
                                )

                                @endforeach

                            </div>

                            @else

                            <div class="empty-products">

                                <h2 class="fs-24 text-color-dark mb-10px">
                                    No products found
                                </h2>

                                <p class="text-color-body fs-16">
                                    Try changing your search, category, variant,
                                    rating or price filters.
                                </p>

                            </div>

                            @endif

                            @if ($products->hasPages())

                            <div class="shop-pagination">
                                {{ $products->withQueryString()->links() }}
                            </div>

                            @endif

                        </div>

                    </div>

                </div>

            </section>

        </div>
    </div>

</div>

<div
    id="product-quick-view-modal"
    class="product-quick-view-modal"
    aria-hidden="true">

    <div
        class="product-quick-view-backdrop"
        data-close-product-popup></div>

    <div
        class="product-quick-view-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quick-view-product-title">

        <button
            type="button"
            class="product-popup-close  fs-12 text-color-white justify-self-start"
            data-close-product-popup
            aria-label="Close product popup">
            <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                x

            </div>
        </button>

        <div
            id="product-quick-view-content"
            class="product-quick-view-content ">
            <div class="product-popup-loader fs-16 text-uppercase letter-space-4px">
                Loading product...
            </div>
        </div>

    </div>

</div>

<style>
    /* ============================================================
   SHOP BY CATEGORY
============================================================ */

    .shop-category-section {
        margin: 30px 0 50px;
    }


    /* ------------------------------------------------------------
   Heading
------------------------------------------------------------ */

    .shop-category-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }


    .shop-category-eyebrow {
        display: block;
        margin-bottom: 7px;

        font-size: 11px;
        font-weight: 600;

        text-transform: uppercase;
        letter-spacing: 3px;

        color: #777;
    }


    .shop-category-title {
        margin: 0;

        font-size: clamp(26px, 3vw, 38px);
        line-height: 1.15;

        color: #111;
    }


    .shop-category-view-all {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding-bottom: 3px;

        color: #111;

        font-size: 11px;
        font-weight: 600;

        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 2px;

        border-bottom: 1px solid #111;

        transition:
            opacity 0.25s ease,
            gap 0.25s ease;
    }


    .shop-category-view-all:hover {
        opacity: 0.65;
        gap: 12px;
    }


    /* ------------------------------------------------------------
   Grid
------------------------------------------------------------ */

    .shop-category-grid {
        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 18px;
    }


    /* ------------------------------------------------------------
   Card
------------------------------------------------------------ */

    .shop-category-card {
        position: relative;

        display: block;

        overflow: hidden;

        color: inherit;
        text-decoration: none;

        background: #f2f2f2;
    }


    .shop-category-image-wrapper {
        position: relative;

        width: 100%;
        aspect-ratio: 4 / 5;

        overflow: hidden;

        background: #f2f2f2;
    }


    /* ------------------------------------------------------------
   Image
------------------------------------------------------------ */

    .shop-category-image {
        display: block;

        width: 100%;
        height: 100%;

        object-fit: cover;

        transition:
            transform 0.6s cubic-bezier(0.22,
                1,
                0.36,
                1);
    }


    .shop-category-card:hover .shop-category-image {
        transform: scale(1.055);
    }


    /* ------------------------------------------------------------
   Missing image
------------------------------------------------------------ */

    .shop-category-image-placeholder {
        display: flex;

        width: 100%;
        height: 100%;

        align-items: center;
        justify-content: center;

        background:
            linear-gradient(135deg,
                #f5f5f5,
                #e8e8e8);

        color: #999;

        font-size: 40px;
    }


    /* ------------------------------------------------------------
   Overlay
------------------------------------------------------------ */

    .shop-category-overlay {
        position: absolute;
        inset: 0;

        background:
            linear-gradient(to bottom,
                rgba(0, 0, 0, 0.02) 30%,
                rgba(0, 0, 0, 0.72) 100%);

        transition:
            background 0.3s ease;
    }


    .shop-category-card:hover .shop-category-overlay {
        background:
            linear-gradient(to bottom,
                rgba(0, 0, 0, 0.05) 20%,
                rgba(0, 0, 0, 0.82) 100%);
    }


    /* ------------------------------------------------------------
   Content
------------------------------------------------------------ */

    .shop-category-content {
        position: absolute;

        left: 22px;
        right: 22px;
        bottom: 22px;

        z-index: 2;

        color: #fff;
    }


    .shop-category-count {
        display: block;

        margin-bottom: 6px;

        font-size: 10px;
        font-weight: 500;

        text-transform: uppercase;
        letter-spacing: 2px;

        opacity: 0.82;
    }


    .shop-category-name {
        margin: 0 0 12px;

        color: #fff;

        font-size: clamp(20px, 2vw, 28px);
        line-height: 1.15;

        text-transform: capitalize;
    }


    .shop-category-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        font-size: 10px;
        font-weight: 600;

        text-transform: uppercase;
        letter-spacing: 2px;

        transition: gap 0.25s ease;
    }


    .shop-category-card:hover .shop-category-link {
        gap: 13px;
    }


    /* ------------------------------------------------------------
   Selected category
------------------------------------------------------------ */

    .shop-category-card.is-active {
        outline: 2px solid #111;
        outline-offset: 3px;
    }


    .shop-category-selected-badge {
        position: absolute;

        top: 15px;
        right: 15px;

        z-index: 3;

        display: inline-flex;
        align-items: center;
        gap: 6px;

        padding: 8px 11px;

        background: #fff;
        color: #111;

        font-size: 9px;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: 1.5px;
    }


    /* ============================================================
   RESPONSIVE
============================================================ */

    @media (max-width: 1100px) {

        .shop-category-grid {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }

    }


    @media (max-width: 767px) {

        .shop-category-section {
            margin-top: 20px;
            margin-bottom: 35px;
        }


        .shop-category-heading {
            align-items: flex-start;
            flex-direction: column;
        }


        .shop-category-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 10px;
        }


        .shop-category-content {
            left: 14px;
            right: 14px;
            bottom: 15px;
        }


        .shop-category-name {
            margin-bottom: 8px;

            font-size: 18px;
        }


        .shop-category-count {
            font-size: 8px;
            letter-spacing: 1.4px;
        }


        .shop-category-link {
            font-size: 8px;
            letter-spacing: 1.4px;
        }

    }


    @media (max-width: 420px) {

        .shop-category-content {
            left: 11px;
            right: 11px;
            bottom: 12px;
        }


        .shop-category-name {
            font-size: 16px;
        }


        .shop-category-link {
            gap: 5px;
        }

    }
</style>

@endsection