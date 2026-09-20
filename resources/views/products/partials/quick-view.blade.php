@php
/*
|--------------------------------------------------------------------------
| Image URL helper
|--------------------------------------------------------------------------
*/

$getPopupImageUrl = function ($path) {
if (empty($path)) {
return asset('asset/images/no-image.jpg');
}

$path = str_replace('\\', '/', $path);
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
| Product images
|--------------------------------------------------------------------------
*/

$mainPopupImage = $getPopupImageUrl(
$product->featured_image
);

$popupImages = collect([$mainPopupImage]);

foreach ($product->images as $image) {
if (empty($image->image)) {
continue;
}

$imageUrl = $getPopupImageUrl(
$image->image
);

if (!$popupImages->contains($imageUrl)) {
$popupImages->push($imageUrl);
}
}

foreach ($product->variants as $variant) {
if (empty($variant->image)) {
continue;
}

$variantImageUrl = $getPopupImageUrl(
$variant->image
);

if (!$popupImages->contains($variantImageUrl)) {
$popupImages->push($variantImageUrl);
}
}

/*
|--------------------------------------------------------------------------
| Product pricing and stock
|--------------------------------------------------------------------------
*/

$regularPrice = (float) (
$product->regular_price ?: 0
);

$salePrice = $product->sale_price !== null
? (float) $product->sale_price
: null;

$hasSale =
$salePrice !== null
&& $salePrice < $regularPrice;

    $stock=(int) (
    $product->stock ?: 0
    );

    /*
    |--------------------------------------------------------------------------
    | Product options
    |--------------------------------------------------------------------------
    */

    $productOptions = $product->options ?: collect();
    $productOptionValues = $product->optionValues ?: collect();
    $productVariants = $product->variants ?: collect();

    $groupedOptionValues = $productOptionValues->groupBy(
    'product_option_id'
    );

    /*
    |--------------------------------------------------------------------------
    | Prepare variants for JavaScript
    |--------------------------------------------------------------------------
    */

    $variantsData = [];

    foreach ($productVariants as $variant) {
    $variantOptions = $variant->options;

    /*
    * ProductVariant already casts options as an array, but this
    * protects older records containing JSON strings.
    */
    if (is_string($variantOptions)) {
    $decodedOptions = json_decode(
    $variantOptions,
    true
    );

    if (
    json_last_error() === JSON_ERROR_NONE
    && is_array($decodedOptions)
    ) {
    $variantOptions = $decodedOptions;
    } else {
    $variantOptions = [];
    }
    }

    if (!is_array($variantOptions)) {
    $variantOptions = [];
    }

    $cleanVariantOptions = [];

    /*
    * Supported structure:
    *
    * [
    * [
    * "option_id" => 1,
    * "option_name" => "Color",
    * "value_id" => 5,
    * "value_label" => "Black"
    * ]
    * ]
    */
    foreach ($variantOptions as $key => $variantOption) {
    if (is_array($variantOption)) {
    $optionId =
    $variantOption['option_id']
    ?? $variantOption['product_option_id']
    ?? null;

    $valueId =
    $variantOption['value_id']
    ?? $variantOption['option_value_id']
    ?? $variantOption['product_option_value_id']
    ?? null;

    if (
    $optionId !== null
    && $valueId !== null
    ) {
    $cleanVariantOptions[] = [
    'option_id' => (string) $optionId,

    'option_name' => (string) (
    $variantOption['option_name']
    ?? ''
    ),

    'value_id' => (string) $valueId,

    'value_label' => (string) (
    $variantOption['value_label']
    ?? $variantOption['label']
    ?? ''
    ),
    ];
    }

    continue;
    }

    /*
    * Supported associative structure:
    *
    * {
    * "1": "5",
    * "2": "9"
    * }
    */
    if (
    $key !== ''
    && $variantOption !== ''
    && $variantOption !== null
    ) {
    $cleanVariantOptions[] = [
    'option_id' => (string) $key,
    'option_name' => '',
    'value_id' => (string) $variantOption,
    'value_label' => '',
    ];
    }
    }

    $variantRegularPrice =
    $variant->regular_price !== null
    ? (float) $variant->regular_price
    : $regularPrice;

    $variantSalePrice =
    $variant->sale_price !== null
    ? (float) $variant->sale_price
    : null;

    $variantImageUrl = null;

    if (!empty($variant->image)) {
    $variantImageUrl = $getPopupImageUrl(
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
    | Correct aggregate availability
    |--------------------------------------------------------------------------
    | A variable product is available when at least one real variant has stock.
    | Do not use only the parent product stock for variable products.
    */
    $productAvailable = $hasVariants
        ? collect($variantsData)->contains(
            fn ($variant) => (int) ($variant['stock'] ?? 0) > 0
        )
        : $stock > 0;

    $singleVariantUnavailable =
        $hasVariants
        && count($variantsData) === 1
        && (int) ($variantsData[0]['stock'] ?? 0) < 1;
    @endphp


<style>
/*
|--------------------------------------------------------------------------
| Quick View — product-show aligned layout
|--------------------------------------------------------------------------
| Desktop/laptop: gallery remains stationary and only product information
| scrolls. Mobile/tablet stacks naturally and the popup becomes one scroll
| surface so no content is trapped.
*/
.product-quick-view-dialog {
    overflow: hidden;
}

.product-quick-view-content {
    height: 100%;
}

.quick-view-product {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(360px, .92fr);
    height: min(82vh, 760px);
    max-height: calc(100vh - 50px);
    overflow: hidden;
    background: #fff;
}

.quick-view-images {
    min-width: 0;
    height: 100%;
    overflow: hidden;
    align-self: start;
}

.quick-view-main-image-wrapper {
    overflow: hidden;
}

.quick-view-main-image {
    display: block;
    width: 100%;
    height: min(58vh, 540px);
    object-fit: cover;
}

.quick-view-gallery {
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: thin;
}

.quick-view-information {
    min-width: 0;
    height: 100%;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-width: thin;
}

.quick-view-purchase-actions {
    display: grid;
    gap: 10px;
}

.quick-view-purchase-actions .add-to-cart-button,
.quick-view-purchase-actions .buy-now-button {
    width: 100%;
}

@media (max-width: 900px) {
    .product-quick-view-dialog {
        overflow-y: auto;
    }

    .quick-view-product {
        display: block;
        height: auto;
        max-height: none;
        overflow: visible;
    }

    .quick-view-images,
    .quick-view-information {
        height: auto;
        overflow: visible;
    }

    .quick-view-main-image {
        height: auto;
        max-height: none;
        object-fit: contain;
    }
}
</style>

    <div
        class="quick-view-product"
        data-product-container>
        <div class="quick-view-images">
            @php
            $regularPrice = $product->regular_price;
            $salePrice = $product->sale_price;

            if (
            $product->variants->isNotEmpty()
            && $product->variants->first()->regular_price
            ) {
            $regularPrice = $product->variants->min('regular_price');
            $salePrice = $product->variants->min('sale_price');
            }

            $hasDiscount = $salePrice
            && $regularPrice
            && $salePrice < $regularPrice;

                $discountPercentage=$hasDiscount
                ? round((($regularPrice - $salePrice) / $regularPrice) * 100)
                : 0;
                $inStock = $productAvailable;
                @endphp
                <div class="quick-view-main-image-wrapper">
                    @if($hasDiscount)
                    <div class="card-discount-badge-quick-view fs-12 text-uppercase letter-space-4px">
                        -{{ $discountPercentage }}%
                    </div>
                    @endif

                    <img
                        id="quick-view-main-image"
                        src="{{ $mainPopupImage }}"
                        alt="{{ $product->title }}"
                        class="quick-view-main-image">

                </div>

                @if ($popupImages->count() > 1)

                <div class="quick-view-gallery">

                    @foreach ($popupImages as $popupImage)

                    <button
                        type="button"
                        class="quick-view-gallery-item {{
                            $loop->first
                                ? 'active'
                                : ''
                        }}"
                        data-popup-image="{{ $popupImage }}"
                        aria-label="View product image">
                        <img
                            src="{{ $popupImage }}"
                            loading="lazy"
                            decoding="async"
                            alt="{{ $product->title }}">
                    </button>

                    @endforeach

                </div>

                @endif

        </div>

        <div class="quick-view-information">


            <div class="product-status-row">

                <span
                    id="product-stock-badge"
                    class="fs-12 text-uppercase letter-space-4px product-stock-badge {{ $inStock ? 'in-stock-quick-view' : 'out-of-stock-quick-view' }}">
                    {{ $inStock ? 'In Stock' : 'Out of Stock' }}
                </span>

                @if($hasDiscount)
                <span class="product-save-badge-quick-view fs-12 text-uppercase letter-space-4px">
                    Save {{ $discountPercentage }}%
                </span>
                @endif

            </div>


            <h2
                id="quick-view-product-title"
                class="quick-view-title fs-24 text-color-dark text-capitalize">
                {{ $product->title }}
            </h2>

            <div
                class="quick-view-price fs-16 text-uppercase letter-space-4px">
                @if ($hasSale)

                <del
                    class="product-regular-price"
                    data-regular-price>
                    ${{ number_format($regularPrice, 2) }}
                </del>

                <strong
                    class="current-product-price product-sale-price"
                    data-product-price>
                    ${{ number_format($salePrice, 2) }}
                </strong>

                @else

                <del
                    class="product-regular-price d-none"
                    data-regular-price
                    hidden>
                    ${{ number_format($regularPrice, 2) }}
                </del>

                <strong
                    class="current-product-price product-sale-price"
                    data-product-price>
                    ${{ number_format($regularPrice, 2) }}
                </strong>

                @endif
            </div>

            <div
                class="quick-view-stock product-stock-message fs-12 text-uppercase letter-space-4px {{
                    $productAvailable ? 'in-stock' : 'out-of-stock'
                }}"
                data-stock-message
                aria-live="polite">
                @if (!$productAvailable)
                    Out of Stock
                @elseif ($hasVariants)
                    Select all available options
                @else
                    {{ $stock }} available in stock
                @endif
            </div>

            @if (!empty($product->short_description))

            <div
                class="quick-view-short-description text-color-body fs-16">
                {!! nl2br(
                e($product->short_description)
                ) !!}
            </div>

            @endif

            {{--
        |--------------------------------------------------------------------------
        | Everything submitted to cart must remain inside this form
        |--------------------------------------------------------------------------
        --}}

            <form
                action="{{ route('cart.add') }}"
                method="POST"
                class="quick-view-cart-form product-form"
                data-product-form
                data-currency-symbol="$">
                @csrf

                <input
                    type="hidden"
                    name="product_id"
                    value="{{ $product->id }}">

                <input
                    type="hidden"
                    name="variant_id"
                    value=""
                    data-selected-variant>

                @if (
                $productOptions->isNotEmpty()
                && $productOptionValues->isNotEmpty()
                )

                <div class="quick-view-options mb-20px">

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
                        class="quick-view-option-group product-option-group mb-20px"
                        data-option-id="{{ $option->id }}">
                        <div class="quick-view-option-heading justify-content-between d-flex mb-10px">

                            <strong
                                class="fs-16 text-uppercase letter-space-4px">
                                {{ $option->name }}
                            </strong>

                            <span
                                class="selected-option-value fs-16 text-uppercase letter-space-4px"
                                data-option-id="{{ $option->id }}"
                                data-selected-option="{{ $option->id }}">
                                Choose {{ $option->name }}
                            </span>

                        </div>

                        {{--
                                    This select is essential because it sends
                                    product_options to Laravel.
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

                            @foreach ($optionValues as $optionValue)

                            @php
                            $valueLabel =
                            $optionValue->label
                            ?: $optionValue->value;
                            @endphp

                            <option
                                value="{{ $optionValue->id }}"
                                data-label="{{ $valueLabel }}">
                                {{ $valueLabel }}
                            </option>

                            @endforeach

                        </select>

                        <div
                            class="quick-view-option-values product-option-values "
                            role="group"
                            aria-label="{{ $option->name }}">
                            @foreach ($optionValues as $optionValue)

                            @php
                            $valueLabel =
                            $optionValue->label
                            ?: $optionValue->value;
                            @endphp

                            <button
                                type="button"
                                class="quick-view-option-value option-value-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                data-option-id="{{ $option->id }}"
                                data-value-id="{{ $optionValue->id }}"
                                data-label="{{ $valueLabel }}"
                                aria-pressed="false">
                                @if (
                                !empty(
                                $optionValue->color_code
                                )
                                )

                                <span
                                    class="quick-view-option-color"
                                    style="background-color: {{ $optionValue->color_code }};"
                                    aria-hidden="true"></span>

                                @endif

                                <span>
                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">{{ $valueLabel }}</div>

                                </span>

                            </button>

                            @endforeach
                        </div>

                        <div
                            class="product-option-error"
                            data-option-error="{{ $option->id }}"
                            aria-live="polite"
                            hidden></div>

                    </div>

                    @endif

                    @endforeach

                </div>

                @endif

                @if ($hasVariants)

                <div
                    class="variant-message"
                    data-variant-message
                    aria-live="polite">
                    @if ($singleVariantUnavailable)
                        This product is currently out of stock and cannot be purchased.
                    @else
                        Select all available options.
                    @endif
                </div>

                @endif

                <div class="quantity-and-cart quick-view-purchase-actions">

                    <div
                        class="quantity-box product-quantity"
                        data-quantity-wrapper>
                        <button
                            type="button"
                            class="quantity-button quantity-minus btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                            data-quantity-minus
                            aria-label="Decrease quantity">
                            <div class="button-text text-uppercase letter-space-3px">−</div>
                        </button>

                        <input
                            class="fs-16 text-uppercase letter-space-4px"
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            inputmode="numeric"
                            aria-label="Product quantity">

                        <button
                            type="button"
                            class="quantity-button quantity-plus btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                            data-quantity-plus
                            aria-label="Increase quantity">
                            <div class="button-text text-uppercase letter-space-3px">+</div>
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="quick-view-cart-button add-to-cart-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                        data-add-to-cart
                        data-ready-text="Add To Cart"
                        @disabled(
                            !$productAvailable
                            || $hasVariants
                            || (!$hasVariants && $stock < 1)
                        )>
                        <div class="button-text text-uppercase letter-space-3px">
                            @if (!$productAvailable)
                                Out of Stock
                            @elseif ($hasVariants)
                                Select Options
                            @else
                                Add To Cart
                            @endif
                        </div>
                    </button>

                    <button
                        type="submit"
                        name="buy_now"
                        value="1"
                        class="buy-now-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                        data-buy-now
                        data-ready-text="Buy Now"
                        @disabled(
                            !$productAvailable
                            || $hasVariants
                            || (!$hasVariants && $stock < 1)
                        )>
                        <div class="button-text text-uppercase letter-space-3px">
                            @if (!$productAvailable)
                                Out of Stock
                            @elseif ($hasVariants)
                                Select Options
                            @else
                                Buy Now
                            @endif
                        </div>
                    </button>

                </div>

                <script
                    type="application/json"
                    data-product-variants>
                    @json($variantsData)
                </script>

            </form>

            <a
                href="{{ route(
                'products.show',
                $product->slug
            ) }}"
                class="quick-view-details-link fs-12 text-uppercase letter-space-4px">
                View Complete Product Details
            </a>

            @if (!empty($product->long_description))

            <div class="quick-view-description">

                <h3 class="fs-24 text-capitalize">
                    Product Details
                </h3>

                <div class="text-color-body fs-16">
                    {!! $product->long_description !!}
                </div>

            </div>

            @endif

            @if (!empty($product->additional_info))

            <div class="quick-view-description">

                <h3 class="fs-24 text-capitalize">
                    Additional Information
                </h3>

                <div class="text-color-body fs-16">
                    {!! $product->additional_info !!}
                </div>

            </div>

            @endif

        </div>
    </div>

   