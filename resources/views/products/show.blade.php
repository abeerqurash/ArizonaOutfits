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
$productReviews = $product->approvedReviews ?: collect();
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
    $discountPercent=round(
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
                    <ul class="bread-crumbs list-style-none fs-12 text-uppercase letter-space-4px mb-10px d-flex">

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
                        ) }}" class="text-decoration-none text-color-dark">
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
                                    class="discount-badge fs-12 text-uppercase letter-space-4px">
                                    -{{ $discountPercent }}%
                                </div>

                                @else

                                <div
                                    id="product-discount-badge"
                                    class="fs-12 text-uppercase letter-space-4px discount-badge"
                                    style="display:none;"></div>

                                @endif

                                <img
                                    id="main-product-image"
                                    src="{{ $featuredImageUrl }}"
                                    alt="{{ $product->title }}"
                                    class="single-featured-image single-product-main-image product-main-image">

                            </div>

                            <div class="single-gallery">

                                <button
                                    type="button"
                                    class="single-gallery-item single-gallery-thumbnail product-gallery-thumbnail active"
                                    data-image="{{ $featuredImageUrl }}">
                                    <img
                                        src="{{ $featuredImageUrl }}"
                                        alt="{{ $product->title }}">
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
                                    data-image="{{ $galleryImageUrl }}">
                                    <img
                                        src="{{ $galleryImageUrl }}"
                                        alt="{{ $product->title }}">
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
                                    data-image="{{ $variantGalleryImageUrl }}">
                                    <img
                                        src="{{ $variantGalleryImageUrl }}"
                                        alt="{{ $product->title }} variant">
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
        }}">
                                    {{ $defaultStock > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>

                                @if($discountPercent)
                                <span class="product-save-badge fs-12 text-uppercase letter-space-4px">
                                    Save {{ $discountPercent }}%
                                </span>
                                @endif

                            </div>

                            <h1 class="fs-48 text-color-dark mb-10px text-capitalize">
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
                                }} fs-16 text-uppercase letter-space-4px">
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
                        }}">
                                    ${{ number_format(
                            $defaultRegularPrice,
                            2
                        ) }}
                                </span>

                                <span
                                    id="product-sale-price"
                                    class="sale-price fs-16 text-uppercase letter-space-4px">
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
                                data-currency-symbol="$">
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
                                        data-option-id="{{ $option->id }}">

                                        <div class="product-option-heading">

                                            <span class="product-option-name fs-16 text-uppercase letter-space-4px">
                                                {{ $option->name }}
                                            </span>

                                            <span
                                                class="selected-option-value fs-16 text-uppercase letter-space-4px"
                                                id="selected-option-value-{{ $option->id }}"
                                                data-option-id="{{ $option->id }}"
                                                data-selected-option="{{ $option->id }}">
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
                                            required>
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
                                                data-label="{{ $optionValueLabel }}">
                                                {{ $optionValueLabel }}
                                            </option>

                                            @endforeach

                                        </select>

                                        <div
                                            class="option-value-buttons product-option-values"
                                            role="group"
                                            aria-label="{{ $option->name }}">

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
                                                class="option-value-button product-option-value btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                                data-option-id="{{ $option->id }}"
                                                data-value-id="{{ $value->id }}"
                                                data-value-label="{{ $buttonValueLabel }}"
                                                data-label="{{ $buttonValueLabel }}"
                                                aria-pressed="false"
                                                aria-label="Select {{ $buttonValueLabel }}">
                                                @if (!empty($buttonColorCode))

                                                <span
                                                    class="option-color-circle product-option-color"
                                                    style="background-color: {{ $buttonColorCode }};"
                                                    aria-hidden="true"></span>

                                                @endif

                                                <span class="product-option-label">
                                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">{{ $buttonValueLabel }}</div>
                                                    
                                                </span>

                                            </button>

                                            @endforeach

                                        </div>

                                        <div
                                            class="product-option-error"
                                            id="product-option-error-{{ $option->id }}"
                                            data-option-error="{{ $option->id }}"
                                            aria-live="polite"></div>

                                    </div>

                                    @endif

                                    @endforeach

                                </div>

                                @endif

                                @if ($hasVariants)

                                <div
                                    id="variant-message"
                                    class="variant-message">
                                    Select all available options.
                                </div>

                                @endif

                                <div class="quantity-and-cart">

                                    <div
                                        class="quantity-box product-quantity"
                                        data-quantity-wrapper>
                                        <button
                                            type="button"
                                            id="quantity-minus"
                                            class="quantity-button quantity-minus btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                            data-quantity-minus>
                                            <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                            −
</div>
                                        </button>

                                        <input
                                            class="fs-16 text-uppercase letter-space-4px"
                                            type="number"
                                            name="quantity"
                                            id="product-quantity"
                                            value="1"
                                            min="1"
                                            max="{{ max(1, $defaultStock) }}">

                                        <button
                                            type="button"
                                            id="quantity-plus"
                                            class="quantity-button quantity-plus btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                            data-quantity-plus>
                                            <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                            +
                                            </div>
                                        </button>
                                    </div>

                                    <button
                                        type="submit"
                                        class="add-to-cart-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                        data-add-to-cart
                                        data-ready-text="Add To Cart"
                                        @disabled(
                                        $hasVariants
                                        || (!$hasVariants && $defaultStock < 1)
                                        )>
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">@if ($hasVariants)
                                            Select Options
                                            @elseif ($defaultStock < 1)
                                                Out of Stock
                                                @else
                                                Add To Cart
                                                @endif</div>

                                    </button>

                                    <button
                                        type="submit"
                                        name="buy_now"
                                        value="1"
                                        class="buy-now-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                        data-buy-now
                                        data-ready-text="Buy Now"
                                        @disabled(
                                        $hasVariants
                                        || (!$hasVariants && $defaultStock < 1)
                                        )>
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                            @if ($hasVariants)
                                            Select Options
                                            @elseif ($defaultStock < 1)
                                                Out of Stock
                                                @else
                                                Buy Now
                                                @endif
                                                </div>
                                    </button>

                                </div>

                                <script type="application/json" data-product-variants>
                                    @json($variantsData)
                                </script>

                            </form>

                            <div class="product-action-buttons">

                                @auth

                                @php
                                $isFavorite = \App\Models\Favorite::where('user_id', auth()->id())
                                ->where('product_id', $product->id)
                                ->exists();
                                @endphp

                                <form action="{{ route('favorite.toggle') }}" method="POST">
                                    @csrf

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="{{ $product->id }}">

                                    <button
                                        type="submit"
                                        class="favorite-btn btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer {{ $isFavorite ? 'active' : '' }}"><div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                        {{ $isFavorite ? 'Remove from Favorites' : 'Add to Favorites' }} </div>
                                    </button>
                                </form>

                                @else

                                <a
                                    href="{{ route('login') }}"
                                    class="favorite-btn fs-16 text-uppercase letter-space-4px btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                    Login to Add to Favorites
</div>
                                </a>

                                @endauth

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
                                        class="stock-count">
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

                                <!-- <p>
                                    <strong>Purchases:</strong>

                                    {{ number_format(
                            (int) (
                                $product->purchase_count ?: 0
                            )
                        ) }}
                                </p> -->

                            </div>

                        </div>

                    </div>

                    {{-- Product details --}}
                    <div class="product-tabs">
                        <div class="tabs">
                            <button class="tab-btn btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer active" data-tab="description"> <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                    Description
</div></button>
                            <button class="tab-btn btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer " data-tab="additional-information"><div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                    Additional Information
</div></button>
                        </div>

                        <div class="tab-content active" id="description">
                            <section class="product-tab-section">

                                <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
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

                                <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
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

                                    <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
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
                    {{-- Reviews --}}
                    <section
                        class="product-tab-section product-reviews-section"
                        id="customer-reviews">
                        <div class="reviews-heading">

                            <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
                                Customer Reviews
                            </h2>

                            <div class="review-summary">

                                <strong class="fs-16 text-uppercase letter-space-4px">
                                    {{ number_format($averageRating, 1) }}/5
                                </strong>

                                <span class="fs-16 text-uppercase letter-space-4px">
                                    {{ $reviewCount }}

                                    {{ $reviewCount === 1
                    ? 'review'
                    : 'reviews' }}
                                </span>

                            </div>

                        </div>

                        @if (session('review_success'))

                        <div
                            class="review-alert review-alert-success"
                            role="alert">
                            {{ session('review_success') }}
                        </div>

                        @endif

                        @if (session('review_error'))

                        <div
                            class="review-alert review-alert-error"
                            role="alert">
                            {{ session('review_error') }}
                        </div>

                        @endif

                        @if ($errors->any())

                        <div
                            class="review-alert review-alert-error"
                            role="alert">
                            <strong>
                                Please correct the following:
                            </strong>

                            <ul>
                                @foreach ($errors->all() as $error)

                                <li>
                                    {{ $error }}
                                </li>

                                @endforeach
                            </ul>
                        </div>

                        @endif

                        <div class="reviews-layout">

                            {{-- Approved review list --}}
                            <div class="reviews-list">

                                @forelse ($productReviews as $review)

                                <article class="single-product-review">

                                    <div class="review-header">

                                        <div>

                                            <strong class="fs-16 text-uppercase letter-space-4px">
                                                {{ $review->name ?: 'Customer' }}
                                            </strong>

                                            @if (!empty($review->created_at))

                                            <span class="review-date fs-16 text-uppercase letter-space-4px">
                                                {{ $review->created_at->format(
                                        'F j, Y'
                                    ) }}
                                            </span>

                                            @endif

                                        </div>

                                        <div
                                            class="rating-stars"
                                            aria-label="{{ $review->rating }} out of 5 stars">
                                            @for (
                                            $reviewStar = 1;
                                            $reviewStar <= 5;
                                                $reviewStar++
                                                )

                                                <span
                                                class="{{
                                        $reviewStar <= (int) $review->rating
                                            ? 'filled'
                                            : ''
                                    }} fs-16 text-uppercase letter-space-4px">
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
                                    There are no approved reviews for this product yet.
                                    Be the first to submit one.
                                </p>

                                @endforelse

                            </div>

                            {{-- Review submission form --}}
                            <div class="product-review-form-wrapper">

                                <h3 class="title fs-24 text-capitalize mb-10px">
                                    Write a Review
                                </h3>

                                <p class="text-color-body fs-16 mb-10px">
                                    Your review will appear after it has been approved.
                                </p>

                                <form
                                    action="{{ route(
                    'reviews.store',
                    $product->id
                ) }}"
                                    method="POST"
                                    class="product-review-form">
                                    @csrf

                                    @auth

                                    <div class="review-user-information">

                                        <p class="text-color-body fs-16">
                                            Reviewing as
                                            <strong>
                                                {{ auth()->user()->name }}
                                            </strong>
                                        </p>

                                        <p class="text-color-body fs-16">
                                            {{ auth()->user()->email }}
                                        </p>

                                    </div>

                                    @else

                                    <div class="review-form-row">

                                        <div class="review-form-group">

                                            <label
                                                for="review-name"
                                                class="fs-14 text-uppercase letter-space-4px">
                                                Name
                                            </label>

                                            <input
                                                type="text"
                                                id="review-name"
                                                name="name"
                                                value="{{ old('name') }}"
                                                maxlength="255"
                                                autocomplete="name"
                                                required>

                                            @error('name')

                                            <span class="review-field-error">
                                                {{ $message }}
                                            </span>

                                            @enderror

                                        </div>

                                        <div class="review-form-group">

                                            <label
                                                for="review-email"
                                                class="fs-14 text-uppercase letter-space-4px">
                                                Email
                                            </label>

                                            <input
                                                type="email"
                                                id="review-email"
                                                name="email"
                                                value="{{ old('email') }}"
                                                maxlength="255"
                                                autocomplete="email"
                                                required>

                                            @error('email')

                                            <span class="review-field-error">
                                                {{ $message }}
                                            </span>

                                            @enderror

                                        </div>

                                    </div>

                                    @endauth

                                    <div class="review-form-group">

                                        <label
                                            for="review-rating"
                                            class="fs-14 text-uppercase letter-space-4px">
                                            Rating
                                        </label>

                                        <div class="review-custom-select">

                                            <input
                                                type="hidden"
                                                name="rating"
                                                id="review-rating"
                                                value="{{ old('rating') }}"
                                                required>

                                            <div class="review-select">

                                                <button
                                                    type="button"
                                                    class="review-select-trigger">
                                                    <span id="selected-rating-text" class="fs-14 text-uppercase letter-space-4px">
                                                        @switch(old('rating'))
                                                        @case(5) 5 - Excellent @break
                                                        @case(4) 4 - Very Good @break
                                                        @case(3) 3 - Good @break
                                                        @case(2) 2 - Fair @break
                                                        @case(1) 1 - Poor @break
                                                        @default Choose a rating
                                                        @endswitch
                                                    </span>

                                                    <i class="fa-solid fa-chevron-down"></i>
                                                </button>

                                                <div class="review-select-options">

                                                    <div class="review-option fs-14 text-uppercase letter-space-4px" data-value="5">
                                                        5 - Excellent
                                                    </div>

                                                    <div class="review-option fs-14 text-uppercase letter-space-4px" data-value="4">
                                                        4 - Very Good
                                                    </div>

                                                    <div class="review-option fs-14 text-uppercase letter-space-4px" data-value="3">
                                                        3 - Good
                                                    </div>

                                                    <div class="review-option fs-14 text-uppercase letter-space-4px" data-value="2">
                                                        2 - Fair
                                                    </div>

                                                    <div class="review-option fs-14 text-uppercase letter-space-4px" data-value="1">
                                                        1 - Poor
                                                    </div>

                                                </div>

                                            </div>

                                            @error('rating')
                                            <span class="review-field-error fs-14 text-uppercase letter-space-4px">
                                                {{ $message }}
                                            </span>
                                            @enderror

                                        </div>

                                        @error('rating')

                                        <span class="review-field-error fs-14 text-uppercase letter-space-4px">
                                            {{ $message }}
                                        </span>

                                        @enderror

                                    </div>

                                    <div class="review-form-group">

                                        <label
                                            for="review-title"
                                            class="fs-14 text-uppercase letter-space-4px">
                                            Review Title
                                        </label>

                                        <input
                                            type="text"
                                            id="review-title"
                                            class="fs-14 text-color-body"
                                            name="title"
                                            value="{{ old('title') }}"
                                            maxlength="255"
                                            placeholder="Summarise your experience">

                                        @error('title')

                                        <span class="review-field-error fs-14 text-uppercase letter-space-4px">
                                            {{ $message }}
                                        </span>

                                        @enderror

                                    </div>

                                    <div class="review-form-group">

                                        <label
                                            for="review-message"
                                            class="fs-14 text-uppercase letter-space-4px">
                                            Your Review
                                        </label>

                                        <textarea
                                            id="review-message"
                                            name="review"
                                            rows="6"
                                            class="fs-14 text-color-body"
                                            minlength="10"
                                            maxlength="5000"
                                            placeholder="Tell us about your experience with this product"
                                            required>{{ old('review') }}</textarea>

                                        @error('review')

                                        <span class="review-field-error">
                                            {{ $message }}
                                        </span>

                                        @enderror

                                    </div>

                                    <button
                                        type="submit"
                                        class="review-submit-button btn-style-2 fs-16 text-uppercase letter-space-4px">
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(-0.518229px, 0.97526px, 0px) scale(1.12);">
                                    Submit Review
</div>
                                    </button>

                                </form>

                            </div>

                        </div>

                    </section>

                </div>

                {{-- Related products --}}
                @if (
                isset($relatedProducts)
                && $relatedProducts->isNotEmpty()
                )

                <section class="related-products-section">

                    <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
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
    </div>
    </div>
    </div>


    @endsection