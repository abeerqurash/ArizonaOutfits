@extends('layouts.app')

@section('title', $product->meta_title ?: $product->title)

@section(
    'meta_description',
    $product->meta_description
        ?: strip_tags($product->short_description ?: '')
)

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Image URL helper
    |--------------------------------------------------------------------------
    */

    $getProductImageUrl = function ($path) {
        if (empty($path)) {
            return asset('asset/images/no-image.jpg');
        }

        $path = ltrim($path, '/');

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
        ) {
            return $path;
        }

        if (
            str_starts_with($path, 'storage/')
            || str_starts_with($path, 'asset/')
            || str_starts_with($path, 'assets/')
            || str_starts_with($path, 'images/')
            || str_starts_with($path, 'uploads/')
        ) {
            return asset($path);
        }

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset('storage/' . $path);
    };

    /*
    |--------------------------------------------------------------------------
    | Product collections
    |--------------------------------------------------------------------------
    */

    $productCategories = $product->categories ?: collect();
    $productTags = $product->tags ?: collect();
    $productImages = $product->images ?: collect();
    $productReviews = $product->reviews ?: collect();
    $productOptions = $product->options ?: collect();
    $productVariants = $product->variants ?: collect();

    $productOptionValues = $product->optionValues ?: collect();

    $groupedOptionValues = $productOptionValues->groupBy(
        'product_option_id'
    );

    /*
    |--------------------------------------------------------------------------
    | Featured image
    |--------------------------------------------------------------------------
    */

    $featuredImageUrl = $getProductImageUrl(
        $product->featured_image
    );

    /*
    |--------------------------------------------------------------------------
    | Review details
    |--------------------------------------------------------------------------
    */

    $reviewCount = $productReviews->count();

    if ($reviewCount > 0) {
        $averageRating = round(
            (float) $productReviews->avg('rating'),
            1
        );
    } else {
        $averageRating = (float) (
            $product->average_rating ?: 0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Default product price
    |--------------------------------------------------------------------------
    */

    $defaultRegularPrice = (float) (
        $product->regular_price ?: 0
    );

    $defaultSalePrice = $product->sale_price !== null
        ? (float) $product->sale_price
        : null;

    $defaultStock = (int) (
        $product->stock ?: 0
    );

    $defaultSku = $product->sku ?: 'N/A';

    /*
    |--------------------------------------------------------------------------
    | Variant data for JavaScript
    |--------------------------------------------------------------------------
    */

    $variantsData = [];

    foreach ($productVariants as $variant) {
        $variantOptions = $variant->options;

        if (is_string($variantOptions)) {
            $decodedVariantOptions = json_decode(
                $variantOptions,
                true
            );

            if (
                json_last_error() === JSON_ERROR_NONE
                && is_array($decodedVariantOptions)
            ) {
                $variantOptions = $decodedVariantOptions;
            } else {
                $variantOptions = [];
            }
        }

        if (!is_array($variantOptions)) {
            $variantOptions = [];
        }

        $cleanVariantOptions = [];

        foreach ($variantOptions as $variantOption) {
            if (!is_array($variantOption)) {
                continue;
            }

            $cleanVariantOptions[] = [
                'option_id' => isset(
                    $variantOption['option_id']
                )
                    ? (string) $variantOption['option_id']
                    : '',

                'option_name' => isset(
                    $variantOption['option_name']
                )
                    ? (string) $variantOption['option_name']
                    : '',

                'value_id' => isset(
                    $variantOption['value_id']
                )
                    ? (string) $variantOption['value_id']
                    : '',

                'value_label' => isset(
                    $variantOption['value_label']
                )
                    ? (string) $variantOption['value_label']
                    : '',
            ];
        }

        $variantRegularPrice =
            $variant->regular_price !== null
                ? (float) $variant->regular_price
                : $defaultRegularPrice;

        $variantSalePrice =
            $variant->sale_price !== null
                ? (float) $variant->sale_price
                : null;

        $variantImageUrl = null;

        if (!empty($variant->image)) {
            $variantImageUrl = $getProductImageUrl(
                $variant->image
            );
        }

        $variantsData[] = [
            'id' => (string) $variant->id,

            'sku' => $variant->sku
                ?: $product->sku
                ?: 'N/A',

            'regular_price' => $variantRegularPrice,

            'sale_price' => $variantSalePrice,

            'stock' => (int) (
                $variant->stock ?: 0
            ),

            'image' => $variantImageUrl,

            'options' => $cleanVariantOptions,
        ];
    }

    $hasVariants = count($variantsData) > 0;

    /*
    |--------------------------------------------------------------------------
    | Discount percentage
    |--------------------------------------------------------------------------
    */

    $discountPercent = null;

    if (
        $defaultSalePrice !== null
        && $defaultRegularPrice > 0
        && $defaultSalePrice < $defaultRegularPrice
    ) {
        $discountPercent = round(
            (
                (
                    $defaultRegularPrice
                    - $defaultSalePrice
                )
                / $defaultRegularPrice
            ) * 100
        );
    }
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
<section class="single-product-page">

    <div class="container">

        {{-- Breadcrumbs --}}
        <ul class="bread-crumbs list-style-none fs-12 text-uppercase letter-space-4px mb-10px d-flex gap-10px">

            <li>
                <a href="{{ route('home-page') }}" class="text-decoration-none text-color-dark">
                    Home
                </a>
            </li>

            <li>
                <a href="{{ route('products.index') }}" class="text-decoration-none text-color-dark">
                    Products
                </a>
            </li>

            @if ($productCategories->isNotEmpty())

                <li>
                    <a
                        href="{{ route(
                            'products.category',
                            $productCategories->first()->slug
                        ) }}" class="text-decoration-none text-color-dark"
                    >
                        {{ $productCategories->first()->title }}
                    </a>
                </li>

            @endif

            <li>
                {{ $product->title }}
            </li>

        </ul>

        <div class="single-product-main">

            {{-- Product images --}}
            <div class="single-product-images single-product-gallery product-gallery">

                <div class="single-featured-image-wrapper">

                    @if ($discountPercent !== null)

                        <div
                            id="product-discount-badge"
                            class="discount-badge fs-12 text-uppercase letter-space-4px"
                        >
                            -{{ $discountPercent }}%
                        </div>

                    @else

                        <div
                            id="product-discount-badge"
                            class="fs-12 text-uppercase letter-space-4px discount-badge"
                            style="display:none;"
                        ></div>

                    @endif

                    <img
                        id="main-product-image"
                        src="{{ $featuredImageUrl }}"
                        alt="{{ $product->title }}"
                        class="single-featured-image single-product-main-image product-main-image"
                    >

                </div>

                <div class="single-gallery">

                    <button
                        type="button"
                        class="single-gallery-item single-gallery-thumbnail product-gallery-thumbnail active"
                        data-image="{{ $featuredImageUrl }}"
                    >
                        <img
                            src="{{ $featuredImageUrl }}"
                            alt="{{ $product->title }}"
                        >
                    </button>

                    @foreach ($productImages as $image)

                        @php
                            $galleryImageUrl =
                                $getProductImageUrl(
                                    $image->image
                                );
                        @endphp

                        <button
                            type="button"
                            class="single-gallery-item single-gallery-thumbnail product-gallery-thumbnail"
                            data-image="{{ $galleryImageUrl }}"
                        >
                            <img
                                src="{{ $galleryImageUrl }}"
                                alt="{{ $product->title }}"
                            >
                        </button>

                    @endforeach

                    @foreach ($productVariants as $variant)

                        @if (!empty($variant->image))

                            @php
                                $variantGalleryImageUrl =
                                    $getProductImageUrl(
                                        $variant->image
                                    );
                            @endphp

                            <button
                                type="button"
                                class="single-gallery-item single-gallery-thumbnail product-gallery-thumbnail"
                                data-image="{{ $variantGalleryImageUrl }}"
                            >
                                <img
                                    src="{{ $variantGalleryImageUrl }}"
                                    alt="{{ $product->title }} variant"
                                >
                            </button>

                        @endif

                    @endforeach

                </div>

            </div>

            {{-- Product information --}}
            <div class="single-product-info">

                <div class="product-status-row">


                    <span
                        id="product-stock-badge"
                        class="fs-12 text-uppercase letter-space-4px product-stock-badge {{
                            $defaultStock > 0
                                ? 'in-stock'
                                : 'out-of-stock'
                        }}"
                    >
                        {{ $defaultStock > 0
                            ? 'In stock'
                            : 'Out of stock' }}
                    </span>

                </div>

                <h1 class="fs-48 text-color-dark mb-10px">
                    {{ $product->title }}
                </h1>

                {{-- Rating --}}
                <div class="single-product-rating">

                    <div class="rating-stars fs-16 text-uppercase letter-space-4px">

                        @for ($star = 1; $star <= 5; $star++)

                            <span
                                class="{{
                                    $star <= round($averageRating)
                                        ? 'filled'
                                        : ''
                                }} fs-16 text-uppercase letter-space-4px"
                            >
                                ★
                            </span>

                        @endfor

                    </div>

                    <span class="fs-12 text-uppercase letter-space-4px">
                        {{ number_format(
                            $averageRating,
                            1
                        ) }}

                        ({{ $reviewCount }}
                        {{ $reviewCount === 1
                            ? 'review'
                            : 'reviews' }})
                    </span>

                </div>

                {{-- Price --}}
                <div class="product-price">

                    <span
                        id="product-regular-price"
                        class="regular-price fs-16 text-uppercase letter-space-4px"
                        style="{{
                            $defaultSalePrice !== null
                            && $defaultSalePrice
                                < $defaultRegularPrice
                                ? ''
                                : 'display:none;'
                        }}"
                    >
                        ${{ number_format(
                            $defaultRegularPrice,
                            2
                        ) }}
                    </span>

                    <span
                        id="product-sale-price"
                        class="sale-price fs-16 text-uppercase letter-space-4px"
                    >
                        ${{ number_format(
                            $defaultSalePrice !== null
                            && $defaultSalePrice
                                < $defaultRegularPrice
                                ? $defaultSalePrice
                                : $defaultRegularPrice,
                            2
                        ) }}
                    </span>

                </div>

                {{-- Short description --}}
                @if (!empty($product->short_description))

                    <div class="single-product-short-description text-color-body fs-16">
                        {!! nl2br(
                            e($product->short_description)
                        ) !!}
                    </div>

                @endif

                {{-- Add to cart and variant selection --}}
                <form
                    action="{{ route('cart.add') }}"
                    method="POST"
                    id="add-to-cart-form"
                    class="product-form single-product-form"
                    data-product-form
                    data-currency-symbol="$"
                >
                    @csrf

                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" id="selected-variant-id" value="">

                {{-- Product option selectors --}}
                @if (
                    $productOptions->isNotEmpty()
                    && $productOptionValues->isNotEmpty()
                )

                    <div class="single-product-options">

                        @foreach ($productOptions as $option)

                            @php
                                $optionValues =
                                    $groupedOptionValues->get(
                                        $option->id,
                                        collect()
                                    );
                            @endphp

                            @if ($optionValues->isNotEmpty())

                                <div
                                    class="single-product-option product-option-group"
                                    data-option-id="{{ $option->id }}"
                                >

                                    <div class="product-option-heading">

                                        <span class="product-option-name fs-16 text-uppercase letter-space-4px">
                                            {{ $option->name }}
                                        </span>

                                        <span
                                            class="selected-option-value fs-16 text-uppercase letter-space-4px"
                                            id="selected-option-value-{{ $option->id }}"
                                            data-option-id="{{ $option->id }}"
                                            data-selected-option="{{ $option->id }}"
                                        >
                                            Choose {{ $option->name }}
                                        </span>

                                    </div>

                                    {{--
                                        The hidden select keeps the existing
                                        variant-matching JavaScript working.
                                    --}}
                                    <select
                                        id="product-option-{{ $option->id }}"
                                        name="product_options[{{ $option->id }}]"
                                        class="product-option-select hidden-product-option-select"
                                        data-option-id="{{ $option->id }}"
                                        aria-label="Choose {{ $option->name }}"
                                    >
                                        <option value="">
                                            Choose {{ $option->name }}
                                        </option>

                                        @foreach ($optionValues as $value)

                                            @php
                                                $optionValueLabel =
                                                    $value->label
                                                    ?: $value->value;
                                            @endphp

                                            <option
                                                value="{{ $value->id }}"
                                                data-label="{{ $optionValueLabel }}"
                                            >
                                                {{ $optionValueLabel }}
                                            </option>

                                        @endforeach

                                    </select>

                                    <div
                                        class="option-value-buttons product-option-values"
                                        role="group"
                                        aria-label="{{ $option->name }}"
                                    >

                                        @foreach ($optionValues as $value)

                                            @php
                                                $buttonValueLabel =
                                                    $value->label
                                                    ?: $value->value;

                                                $buttonColorCode =
                                                    $value->color_code
                                                    ?? null;
                                            @endphp

                                            <button
                                                type="button"
                                                class="option-value-button product-option-value fs-16 text-uppercase letter-space-4px"
                                                data-option-id="{{ $option->id }}"
                                                data-value-id="{{ $value->id }}"
                                                data-value-label="{{ $buttonValueLabel }}"
                                                data-label="{{ $buttonValueLabel }}"
                                                aria-pressed="false"
                                                aria-label="Select {{ $buttonValueLabel }}"
                                            >
                                                @if (!empty($buttonColorCode))

                                                    <span
                                                        class="option-color-circle product-option-color"
                                                        style="background-color: {{ $buttonColorCode }};"
                                                        aria-hidden="true"
                                                    ></span>

                                                @endif

                                                <span class="product-option-label">
                                                    {{ $buttonValueLabel }}
                                                </span>

                                            </button>

                                        @endforeach

                                    </div>

                                    <div
                                        class="product-option-error"
                                        id="product-option-error-{{ $option->id }}"
                                        data-option-error="{{ $option->id }}"
                                        aria-live="polite"
                                    ></div>

                                </div>

                            @endif

                        @endforeach

                    </div>

                @endif

                @if ($hasVariants)

                    <div
                        id="variant-message"
                        class="variant-message"
                    >
                        Select all available options.
                    </div>

                @endif

<div class="quantity-and-cart ">

                        <div class="quantity-box product-quantity" data-quantity-wrapper>

                            <button
                                type="button"
                                id="quantity-minus"
                                class="quantity-button quantity-minus"
                                data-quantity-minus
                            >
                                −
                            </button>

                            <input class="fs-16 text-uppercase letter-space-4px"
                                type="number"
                                name="quantity"
                                id="product-quantity"
                                value="1"
                                min="1"
                                max="{{ max(
                                    1,
                                    $defaultStock
                                ) }}"
                            >

                            <button
                                type="button"
                                id="quantity-plus"
                                class="quantity-button quantity-plus"
                                data-quantity-plus
                            >
                                +
                            </button>

                        </div>

                        <button
                            type="submit"
                            id="add-to-cart-button"
                            class="add-to-cart-button fs-16 text-uppercase letter-space-4px"
                            data-add-to-cart
                            {{ !$hasVariants
                                && $defaultStock < 1
                                    ? 'disabled'
                                    : '' }}
                        >
                            {{ !$hasVariants
                                && $defaultStock < 1
                                    ? 'Out of Stock'
                                    : (
                                        $hasVariants
                                            ? 'Select Options'
                                            : 'Add To Cart'
                                    ) }}
                        </button>

                    </div>

                    <script type="application/json" data-product-variants>
                        @json($variantsData)
                    </script>

                </form>

                <div class="product-action-buttons">

                    {{-- Favorite --}}
                    <form
                        action="{{ route('favorite.toggle') }}"
                        method="POST"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="product_id"
                            value="{{ $product->id }}"
                        >

                        <button
                            type="submit"
                            class="favorite-button fs-16 text-uppercase letter-space-4px"
                        >
                            Add To Favorite
                        </button>

                    </form>

                    {{-- Buy now --}}
                    <form
                        action="{{ route('cart.add') }}"
                        method="POST"
                        id="buy-now-form"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="product_id"
                            value="{{ $product->id }}"
                        >

                        <input
                            type="hidden"
                            name="variant_id"
                            id="buy-now-variant-id"
                            value=""
                        >

                        <input
                            type="hidden"
                            name="quantity"
                            id="buy-now-quantity"
                            value="1"
                        >

                        <input
                            type="hidden"
                            name="buy_now"
                            value="1"
                        >

                        <button
                            type="submit"
                            id="buy-now-button"
                            class="buy-now-button fs-16 text-uppercase letter-space-4px"
                            {{ !$hasVariants
                                && $defaultStock < 1
                                    ? 'disabled'
                                    : '' }}
                        >
                            Buy Now
                        </button>

                    </form>

                </div>

                {{-- Product metadata --}}
                <div class="product-meta fs-14 text-uppercase letter-space-4px flex-wrap d-flex gap-10px align-items-center ">

                    <p>
                        <strong>SKU:</strong>

                        <span id="product-sku">
                            {{ $defaultSku }}
                        </span>
                    </p>

                    <div class="product-stock-wrapper">

    <span class="stock-label">
        <strong>Stock:</strong>
    </span>

    <span
        id="product-stock"
        class="stock-count"
    >
        {{ $defaultStock }}
    </span>

    <span class="stock-text">
        available
    </span>

</div>

                    <p>
                        <strong>Status:</strong>

                        {{ ucfirst(
                            $product->status ?: 'inactive'
                        ) }}
                    </p>

                    @if ($productCategories->isNotEmpty())

                        <p>
                            <strong>Categories:</strong>

                            @foreach (
                                $productCategories
                                as $category
                            )

                                <a
                                    href="{{ route(
                                        'products.category',
                                        $category->slug
                                    ) }}"
                                class="text-decoration-none text-color-dark">
                                    {{ $category->title }}
                                </a>

                                @if (!$loop->last)
                                    ,
                                @endif

                            @endforeach
                        </p>

                    @endif

                    @if ($productTags->isNotEmpty())

                        <p>
                            <strong>Tags:</strong>

                            @foreach (
                                $productTags
                                as $tag
                            )

                                {{ $tag->title }}

                                @if (!$loop->last)
                                    ,
                                @endif

                            @endforeach
                        </p>

                    @endif

                    <p>
                        <strong>Views:</strong>

                        {{ number_format(
                            (int) (
                                $product->views_count ?: 0
                            )
                        ) }}
                    </p>

                    <p>
                        <strong>Favorites:</strong>

                        {{ number_format(
                            (int) (
                                $product->favorites_count ?: 0
                            )
                        ) }}
                    </p>

                    <p>
                        <strong>Purchases:</strong>

                        {{ number_format(
                            (int) (
                                $product->purchase_count ?: 0
                            )
                        ) }}
                    </p>

                </div>

            </div>

        </div>

        {{-- Product details --}}
        <div class="product-tabs">
            <div class="tabs">
  <button class="tab-btn fs-14 text-uppercase letter-space-4px active" data-tab="description"> Description</button>
  <button class="tab-btn fs-14 text-uppercase letter-space-4px " data-tab="additional-information">Additional Information</button>
</div>

<div class="tab-content active" id="description">
   <section class="product-tab-section">

                <h2 class="title fs-24 text-capitalize mb-10px">
                    Description
                </h2>

                @if (!empty($product->long_description))

                    <div class="text-color-body fs-16 product-description-content">
                        {!! $product->long_description !!}
                    </div>

                @else

                    <p class="text-color-body fs-16">
                        No description is available.
                    </p>

                @endif

            </section>
</div>

<div class="tab-content" id="additional-information">
   <section class="product-tab-section">

                <h2 class="title fs-24 text-capitalize mb-10px">
                    Additional Information
                </h2>

                @if (!empty($product->additional_info))

                    <div class="product-additional-content text-color-body fs-16 mb-10px">
                        {!! $product->additional_info !!}
                    </div>

                @else

                    <p class="text-color-body fs-16 mb-10px">
                        No additional information is available.
                    </p>

                @endif

                
            {{-- Attribute details table --}}
            @if (
                $productOptions->isNotEmpty()
                && $productOptionValues->isNotEmpty()
            )

                <section class="product-tab-section">

                    <h2 class="title fs-24 text-capitalize mb-10px">
                        Product Attributes
                    </h2>

                    <div class="product-table-wrapper">

                        <table class="product-details-table">

                            <tbody>

                                @foreach (
                                    $productOptions
                                    as $option
                                )

                                    @php
                                        $attributeValues =
                                            $groupedOptionValues
                                                ->get(
                                                    $option->id,
                                                    collect()
                                                );
                                    @endphp

                                    @if (
                                        $attributeValues->isNotEmpty()
                                    )

                                        <tr>

                                            <th class="fs-14 text-uppercase letter-space-4px">
                                                {{ $option->name }}
                                            </th>

                                            <td class="fs-14 text-uppercase letter-space-4px">

                                                @foreach (
                                                    $attributeValues
                                                    as $attributeValue
                                                )

                                                    {{ $attributeValue->label
                                                        ?: $attributeValue->value }}

                                                    @if (!$loop->last)
                                                        ,
                                                    @endif

                                                @endforeach

                                            </td>

                                        </tr>

                                    @endif

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </section>

            @endif

            {{-- Variants details table --}}
            @if ($productVariants->isNotEmpty())

                <section class="product-tab-section">

                    <h2>
                        Product Variants
                    </h2>

                    <div class="product-table-wrapper">

                        <table class="product-details-table">

                            <thead>
                                <tr>
                                    <th>Variant</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach (
                                    $productVariants
                                    as $variant
                                )

                                    @php
                                        $tableVariantOptions =
                                            $variant->options;

                                        if (
                                            is_string(
                                                $tableVariantOptions
                                            )
                                        ) {
                                            $decodedTableOptions =
                                                json_decode(
                                                    $tableVariantOptions,
                                                    true
                                                );

                                            if (
                                                json_last_error()
                                                    === JSON_ERROR_NONE
                                                && is_array(
                                                    $decodedTableOptions
                                                )
                                            ) {
                                                $tableVariantOptions =
                                                    $decodedTableOptions;
                                            } else {
                                                $tableVariantOptions = [];
                                            }
                                        }

                                        if (
                                            !is_array(
                                                $tableVariantOptions
                                            )
                                        ) {
                                            $tableVariantOptions = [];
                                        }

                                        $tableRegularPrice =
                                            $variant->regular_price
                                                !== null
                                                ? (float)
                                                    $variant->regular_price
                                                : $defaultRegularPrice;

                                        $tableSalePrice =
                                            $variant->sale_price
                                                !== null
                                                ? (float)
                                                    $variant->sale_price
                                                : null;
                                    @endphp

                                    <tr>

                                        <td>

                                            @if (
                                                count(
                                                    $tableVariantOptions
                                                ) > 0
                                            )

                                                @foreach (
                                                    $tableVariantOptions
                                                    as $tableOption
                                                )

                                                    {{ isset(
                                                        $tableOption[
                                                            'option_name'
                                                        ]
                                                    )
                                                        ? $tableOption[
                                                            'option_name'
                                                        ]
                                                        : 'Option' }}

                                                    :

                                                    {{ isset(
                                                        $tableOption[
                                                            'value_label'
                                                        ]
                                                    )
                                                        ? $tableOption[
                                                            'value_label'
                                                        ]
                                                        : 'Value' }}

                                                    @if (!$loop->last)
                                                        /
                                                    @endif

                                                @endforeach

                                            @else

                                                Default variant

                                            @endif

                                        </td>

                                        <td>
                                            {{ $variant->sku
                                                ?: $defaultSku }}
                                        </td>

                                        <td>

                                            @if (
                                                $tableSalePrice !== null
                                                && $tableSalePrice
                                                    < $tableRegularPrice
                                            )

                                                <del>
                                                    ${{ number_format(
                                                        $tableRegularPrice,
                                                        2
                                                    ) }}
                                                </del>

                                                <strong>
                                                    ${{ number_format(
                                                        $tableSalePrice,
                                                        2
                                                    ) }}
                                                </strong>

                                            @else

                                                <strong>
                                                    ${{ number_format(
                                                        $tableRegularPrice,
                                                        2
                                                    ) }}
                                                </strong>

                                            @endif

                                        </td>

                                        <td>

                                            @if (
                                                (int) $variant->stock > 0
                                            )

                                                {{ (int)
                                                    $variant->stock }}
                                                available

                                            @else

                                                Out of stock

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </section>

            @endif

            </section>
</div>
        </div>

           

           


            {{-- Reviews --}}
            <section class="product-tab-section">

                <div class="reviews-heading">

                    <h2 class="title fs-24 text-capitalize mb-10px">
                        Customer Reviews
                    </h2>

                    <div class="review-summary">
                        <strong class="fs-16 text-uppercase letter-space-4px">
                            {{ number_format(
                                $averageRating,
                                1
                            ) }}/5
                        </strong>

                        <span class="fs-16 text-uppercase letter-space-4px">
                            {{ $reviewCount }}
                            {{ $reviewCount === 1
                                ? 'review'
                                : 'reviews' }}
                        </span>
                    </div>

                </div>

                @forelse (
                    $productReviews
                    as $review
                )

                    <article class="single-product-review">

                        <div class="review-header">

                            <div>
                                <strong class=" fs-16 text-uppercase letter-space-4px">
                                    {{ $review->name
                                        ?: 'Customer' }}
                                </strong>

                                @if (
                                    !empty($review->created_at)
                                )

                                    <span class="review-date  fs-16 text-uppercase letter-space-4px">
                                        {{ $review->created_at
                                            ->format(
                                                'F j, Y'
                                            ) }}
                                    </span>

                                @endif
                            </div>

                            <div class="rating-stars">

                                @for (
                                    $reviewStar = 1;
                                    $reviewStar <= 5;
                                    $reviewStar++
                                )

                                    <span
                                        class="{{
                                            $reviewStar
                                                <= (int)
                                                    $review->rating
                                                ? 'filled'
                                                : ''
                                        }}  fs-16 text-uppercase letter-space-4px"
                                    >
                                        ★
                                    </span>

                                @endfor

                            </div>

                        </div>

                        @if (!empty($review->title))

                            <h3 class="title fs-24 text-capitalize mb-10px">
                                {{ $review->title }}
                            </h3>

                        @endif

                        <p class="text-color-body fs-16">
                            {{ $review->review }}
                        </p>

                    </article>

                @empty

                    <p class="text-color-body fs-16">
                        There are no reviews for this product yet.
                    </p>

                @endforelse

            </section>

        </div>

        {{-- Related products --}}
        @if (
            isset($relatedProducts)
            && $relatedProducts->isNotEmpty()
        )

            <section class="related-products-section">

                <h2>
                    Related Products
                </h2>

                <div class="products-grid">

                    @foreach (
                        $relatedProducts
                        as $relatedProduct
                    )

                        @include(
                            'products.partials.product-card',
                            [
                                'product' =>
                                    $relatedProduct,
                            ]
                        )

                    @endforeach

                </div>

            </section>

        @endif

    </div>

</section>
        </div></div></div>
<style>
.single-product-main {
    display: grid;
    grid-template-columns:
        minmax(0, 1.1fr)
        minmax(320px, 0.9fr);
    gap: 50px;
    align-items: start;
}

.single-featured-image-wrapper {
    position: relative;
    overflow: hidden;
    background: #f7f7f7;
}

.single-featured-image {
    display: block;
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
}

.discount-badge {
    position: absolute;
    z-index: 2;
    top: 15px;
    left: 15px;
    padding: 8px 12px;
    color: #fff;
    background: #d90000;
    border-radius: 5px;
    font-weight: 700;
}

.single-gallery {
    display: grid;
    grid-template-columns: repeat(
        5,
        minmax(0, 1fr)
    );
    gap: 10px;
    margin-top: 15px;
}

.single-gallery-item {
    padding: 0;
    overflow: hidden;
    background: transparent;
    border: 2px solid transparent;
    cursor: pointer;
}

.single-gallery-item.active {
    border-color: #111;
}

.single-gallery-item img {
    display: block;
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
}

.product-status-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.product-status,
.product-stock-badge {
    display: inline-flex;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.product-status.active,
.product-stock-badge.in-stock {
    color: #146c43;
    background: #d1e7dd;
}

.product-status.inactive,
.product-stock-badge.out-of-stock {
    color: #842029;
    background: #f8d7da;
}

.single-product-rating {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 15px;
}

.rating-stars {
    display: inline-flex;
    gap: 2px;
}

.rating-stars span {
    color: #ccc;
}

.rating-stars span.filled {
    color: #f0ad00;
}

.product-price {
    display: flex;
    gap: 12px;
    align-items: center;
    margin: 20px 0;
}

.product-price .regular-price {
    color: #777;
    text-decoration: line-through;
}

.single-product-short-description {
    line-height: 1.7;
    margin-bottom: 25px;
}

.single-product-options {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin: 25px 0;
}

.single-product-option,
.product-option-group {
    width: 100%;
    margin: 0;
}

.product-option-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 12px;
}

.product-option-name {
    color: var(--dark);
    font-weight: 700;
}

.selected-option-value {
    color: var(--body);
    font-size: 13px;
    text-align: right;
}

.hidden-product-option-select {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.product-meta p, .product-meta .product-stock-wrapper {
    border: 1px solid var(--dark-outline);
    padding: 12px 20px;
    margin: 0 !important;
}

.option-value-buttons,
.product-option-values {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 0;
}

.option-value-button,
.product-option-value, .tab-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: auto;
    min-width: 52px;
    min-height: 48px;
    margin: 0;
    padding: 11px 16px;
    color: var(--dark);
    background-color: #fff;
    border: 1px solid var(--dark-outline);
    border-radius: 2px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition:
        color .25s ease,
        background-color .25s ease,
        border-color .25s ease,
        box-shadow .25s ease,
        transform .25s ease;
}

.option-value-button:hover,
.product-option-value:hover, .add-to-cart-button:hover, .quantity-button:hover, .buy-now-button:hover, .favorite-button:hover, .tab-btn.active  {
    color: #fff;
    background-color: var(--dark);
    border-color: var(--dark);
    transform: translateY(-2px);
}



.option-value-button.active,
.product-option-value.active,
.option-value-button[aria-pressed="true"],
.product-option-value[aria-pressed="true"] {
    color: #fff;
    background-color: var(--dark);
    border-color: var(--dark);
    box-shadow: 0 0 0 2px rgba(0, 0, 0, .12);
}

.option-value-button:focus-visible,
.product-option-value:focus-visible {
    outline: 2px solid var(--dark);
    outline-offset: 3px;
}

.option-value-button.disabled,
.product-option-value.disabled,
.option-value-button:disabled,
.product-option-value:disabled {
    color: #999;
    background-color: #f2f2f2;
    border-color: #ddd;
    text-decoration: line-through;
    cursor: not-allowed;
    opacity: .6;
    transform: none;
}

.option-color-circle,
.product-option-color {
    display: inline-block;
    flex: 0 0 auto;
    width: 20px;
    height: 20px;
    border: 1px solid rgba(0, 0, 0, .3);
    border-radius: 50%;
}

.option-value-button.active .option-color-circle,
.product-option-value.active .product-option-color {
    border-color: rgba(255, 255, 255, .8);
    box-shadow: 0 0 0 1px rgba(0, 0, 0, .2);
}

.product-option-label {
    white-space: nowrap;
}

.product-option-error {
    display: none;
    margin-top: 8px;
    color: #b42318;
    font-size: 13px;
}

.product-option-error.show {
    display: block;
}

.variant-message {
    padding: 11px 13px;
    margin-bottom: 15px;
    color: #555;
    background: #f5f5f5;
    border-radius: 5px;
}

.variant-message.success {
    color: #146c43;
    background: #d1e7dd;
}

.variant-message.error {
    color: #842029;
    background: #f8d7da;
}

.quantity-and-cart {
    display: flex;
    gap: 12px;
    align-items: stretch;
    flex-direction: column;
}

.quantity-box {
        position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: auto;
    min-width: 52px;
    min-height: 48px;
    margin: 0;
    padding: 11px 16px;
    color: var(--dark);
    background-color: #fff;
    border: 1px solid var(--dark-outline);
    border-radius: 2px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition: color .25s ease, background-color .25s ease, border-color .25s ease, box-shadow .25s ease, transform .25s ease;
}

.quantity-box input {
    width: 60px;
    text-align: center;
    border: 0;
    outline: 0;
}

.quantity-button {
    cursor: pointer;
        position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: auto;
    min-width: 52px;
    min-height: 48px;
    margin: 0;
    padding: 11px 16px;
    color: var(--dark);
    background-color: #ffffff14;
    border: 1px solid var(--dark-outline);
    border-radius: 2px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition: color .25s ease, background-color .25s ease, border-color .25s ease, box-shadow .25s ease, transform .25s ease;
}

.add-to-cart-button,
.favorite-button,
.buy-now-button {
    padding: 12px 20px;
    border: 0;
    border-radius: 5px;
    cursor: pointer;
}

.add-to-cart-button {
    flex: 1;
        position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: auto;
    min-width: 52px;
    min-height: 48px;
    margin: 0;
    padding: 11px 16px;
    color: var(--dark);
    background-color: #fff;
    border: 1px solid var(--dark-outline);
    border-radius: 2px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition: color .25s ease, background-color .25s ease, border-color .25s ease, box-shadow .25s ease, transform .25s ease;
}

.favorite-button {
        flex: 1;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    min-width: 52px;
    min-height: 48px;
    margin: 0;
    padding: 11px 16px;
    color: var(--dark);
    background-color: #fff;
    border: 1px solid var(--dark-outline);
    border-radius: 2px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition: color .25s ease, background-color .25s ease, border-color .25s ease, box-shadow .25s ease, transform .25s ease;
}

.buy-now-button {
        flex: 1;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    min-width: 52px;
    min-height: 48px;
    margin: 0;
    padding: 11px 16px;
    color: var(--dark);
    background-color: #fff;
    border: 1px solid var(--dark-outline);
    border-radius: 2px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition: color .25s ease, background-color .25s ease, border-color .25s ease, box-shadow .25s ease, transform .25s ease;
}

.add-to-cart-button:disabled,
.buy-now-button:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.product-action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 12px;
}

.product-meta {
    padding-top: 20px;
    margin-top: 25px;
    border-top: 1px solid #ddd;
}

.product-meta p {
    margin: 8px 0;
}

.product-tabs {
    margin: 60px 0;
}


.product-table-wrapper {
    overflow-x: auto;
}

.product-details-table {
    width: 100%;
    border-collapse: collapse;
}

.product-details-table th,
.product-details-table td {
    padding: 13px;
    text-align: left;
    vertical-align: top;
    border: 1px solid #ddd;
}

.product-details-table th {
    background: #f7f7f7;
}

.reviews-heading,
.review-header {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: center;
}

.review-summary {
    display: flex;
    gap: 8px;
    align-items: center;
}

.single-product-review {
    padding: 20px 0;
    border-top: 1px solid #ddd;
}

.review-date {
    display: block;
    margin-top: 4px;
    color: #777;
    font-size: 13px;
}

.related-products-section {
    margin-top: 50px;
}

.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}




.tab-content {
  display: none;
  padding: 20px;
  border: 1px solid #ddd;
}

.tab-content.active {
  display: block;
}

@media (max-width: 850px) {
    .single-product-main {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .single-gallery {
        grid-template-columns: repeat(
            4,
            minmax(0, 1fr)
        );
    }

    .product-option-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
    }

    .selected-option-value {
        text-align: left;
    }

    .option-value-buttons,
    .product-option-values {
        gap: 8px;
    }

    .option-value-button,
    .product-option-value {
        min-height: 44px;
        padding: 10px 13px;
    }

    .quantity-and-cart {
        flex-direction: column;
    }

    .quantity-box {
        width: 100%;
    }

    .quantity-box input {
        flex: 1;
    }

    .product-action-buttons {
        grid-template-columns: 1fr;
    }

    .reviews-heading,
    .review-header {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>


@endsection