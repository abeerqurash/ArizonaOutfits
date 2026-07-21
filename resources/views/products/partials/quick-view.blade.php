@php
    $getPopupImageUrl = function ($path) {
        if (empty($path)) {
            return asset(
                'asset/images/no-image.jpg'
            );
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

    $mainPopupImage =
        $getPopupImageUrl(
            $product->featured_image
        );

    $popupImages = collect();

    $popupImages->push($mainPopupImage);

    foreach ($product->images as $image) {
        if (!empty($image->image)) {
            $imageUrl = $getPopupImageUrl(
                $image->image
            );

            if (!$popupImages->contains($imageUrl)) {
                $popupImages->push($imageUrl);
            }
        }
    }

    foreach ($product->variants as $variant) {
        if (!empty($variant->image)) {
            $variantImageUrl =
                $getPopupImageUrl(
                    $variant->image
                );

            if (
                !$popupImages->contains(
                    $variantImageUrl
                )
            ) {
                $popupImages->push(
                    $variantImageUrl
                );
            }
        }
    }

    $regularPrice = (float) (
        $product->regular_price ?: 0
    );

    $salePrice =
        $product->sale_price !== null
            ? (float) $product->sale_price
            : null;

    $hasSale =
        $salePrice !== null
        && $salePrice < $regularPrice;

    $stock = (int) (
        $product->stock ?: 0
    );

    $groupedOptionValues =
        $product->optionValues->groupBy(
            'product_option_id'
        );
@endphp

<div class="quick-view-product">

    <div class="quick-view-images">

        <div class="quick-view-main-image-wrapper">

            <img
                id="quick-view-main-image"
                src="{{ $mainPopupImage }}"
                alt="{{ $product->title }}"
                class="quick-view-main-image"
            >

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
                    >
                        <img
                            src="{{ $popupImage }}"
                            loading="lazy"
                            decoding="async"
                            alt="{{ $product->title }}"
                        >
                    </button>

                @endforeach

            </div>

        @endif

    </div>

    <div class="quick-view-information">

        <h2
            id="quick-view-product-title"
            class="quick-view-title"
        >
            {{ $product->title }}
        </h2>

        <div class="quick-view-price">

            @if ($hasSale)

                <del>
                    ${{ number_format(
                        $regularPrice,
                        2
                    ) }}
                </del>

                <strong>
                    ${{ number_format(
                        $salePrice,
                        2
                    ) }}
                </strong>

            @else

                <strong>
                    ${{ number_format(
                        $regularPrice,
                        2
                    ) }}
                </strong>

            @endif

        </div>

        <div
            class="quick-view-stock {{
                $stock > 0
                    ? 'in-stock'
                    : 'out-of-stock'
            }}"
        >
            @if ($stock > 0)

                {{ $stock }} available in stock

            @else

                Out of stock

            @endif
        </div>

        @if (!empty($product->short_description))

            <div class="quick-view-short-description">
                {!! nl2br(
                    e($product->short_description)
                ) !!}
            </div>

        @endif

        @if (
            $product->options->isNotEmpty()
            && $product->optionValues->isNotEmpty()
        )

            <div class="quick-view-options">

                @foreach ($product->options as $option)

                    @php
                        $optionValues =
                            $groupedOptionValues->get(
                                $option->id,
                                collect()
                            );
                    @endphp

                    @if ($optionValues->isNotEmpty())

                        <div class="quick-view-option-group">

                            <strong>
                                {{ $option->name }}
                            </strong>

                            <div class="quick-view-option-values">

                                @foreach (
                                    $optionValues
                                    as $optionValue
                                )

                                    @php
                                        $valueLabel =
                                            $optionValue->label
                                            ?: $optionValue->value;
                                    @endphp

                                    <button
                                        type="button"
                                        class="quick-view-option-value"
                                        data-option-id="{{ $option->id }}"
                                        data-value-id="{{ $optionValue->id }}"
                                    >

                                        @if (
                                            !empty(
                                                $optionValue->color_code
                                            )
                                        )

                                            <span
                                                class="quick-view-option-color"
                                                style="background-color: {{ $optionValue->color_code }};"
                                            ></span>

                                        @endif

                                        {{ $valueLabel }}

                                    </button>

                                @endforeach

                            </div>

                        </div>

                    @endif

                @endforeach

            </div>

        @endif

        <form
            action="{{ route('cart.add') }}"
            method="POST"
            class="quick-view-cart-form"
        >
            @csrf

            <input
                type="hidden"
                name="product_id"
                value="{{ $product->id }}"
            >

            <input
                type="hidden"
                name="quantity"
                value="1"
            >

            <button
                type="submit"
                class="quick-view-cart-button"
                {{ $stock < 1
                    ? 'disabled'
                    : '' }}
            >
                {{ $stock > 0
                    ? 'Add To Cart'
                    : 'Out of Stock' }}
            </button>

        </form>

        <a
            href="{{ route(
                'products.show',
                $product->slug
            ) }}"
            class="quick-view-details-link"
        >
            View Complete Product Details
        </a>

        @if (!empty($product->long_description))

            <div class="quick-view-description">

                <h3>
                    Product Details
                </h3>

                <div>
                    {!! $product->long_description !!}
                </div>

            </div>

        @endif

        @if (!empty($product->additional_info))

            <div class="quick-view-description">

                <h3>
                    Additional Information
                </h3>

                <div>
                    {!! $product->additional_info !!}
                </div>

            </div>

        @endif

    </div>

</div>