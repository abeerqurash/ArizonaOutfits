@php
    /*
    |--------------------------------------------------------------------------
    | Image URL helper
    |--------------------------------------------------------------------------
    */

    $getCardImageUrl = function ($path) {
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
    | Product URL
    |--------------------------------------------------------------------------
    */

    $productUrl = route(
        'products.show',
        $product->slug
    );

    /*
    |--------------------------------------------------------------------------
    | Main product image
    |--------------------------------------------------------------------------
    */

    $mainImageUrl = $getCardImageUrl(
        $product->featured_image
    );

    /*
    |--------------------------------------------------------------------------
    | Product gallery images
    |--------------------------------------------------------------------------
    */

    $galleryImages = collect();

    if (
        isset($product->images)
        && $product->images
    ) {
        foreach ($product->images as $productImage) {
            if (!empty($productImage->image)) {
                $galleryImages->push([
                    'url' => $getCardImageUrl(
                        $productImage->image
                    ),
                    'alt' => $product->title,
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Include variant images
    |--------------------------------------------------------------------------
    */

    if (
        isset($product->variants)
        && $product->variants
    ) {
        foreach ($product->variants as $variant) {
            if (!empty($variant->image)) {
                $variantImageUrl =
                    $getCardImageUrl(
                        $variant->image
                    );

                $alreadyExists =
                    $galleryImages->contains(
                        function ($image) use (
                            $variantImageUrl
                        ) {
                            return $image['url']
                                === $variantImageUrl;
                        }
                    );

                if (!$alreadyExists) {
                    $galleryImages->push([
                        'url' => $variantImageUrl,
                        'alt' => $product->title,
                    ]);
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Remove main image from gallery if duplicated
    |--------------------------------------------------------------------------
    */

    $galleryImages = $galleryImages
        ->reject(function ($image) use (
            $mainImageUrl
        ) {
            return $image['url']
                === $mainImageUrl;
        })
        ->values();

    $galleryCount = $galleryImages->count();

    $visibleGalleryImages =
        $galleryImages->take(4);

    $remainingGalleryCount =
        max(0, $galleryCount - 4);

    $fifthGalleryImage =
        $remainingGalleryCount > 0
            ? $galleryImages->get(4)
            : null;

    /*
    |--------------------------------------------------------------------------
    | Product prices
    |--------------------------------------------------------------------------
    */

    $regularPrice = (float) (
        $product->regular_price ?: 0
    );

    $salePrice =
        $product->sale_price !== null
            ? (float) $product->sale_price
            : null;

    $hasDiscount =
        $salePrice !== null
        && $regularPrice > 0
        && $salePrice < $regularPrice;

    $discountPercentage = null;

    if ($hasDiscount) {
        $discountPercentage = round(
            (
                (
                    $regularPrice
                    - $salePrice
                )
                / $regularPrice
            ) * 100
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Stock
    |--------------------------------------------------------------------------
    */

    $stock = (int) (
        $product->stock ?: 0
    );

    /*
    |--------------------------------------------------------------------------
    | Product title
    |--------------------------------------------------------------------------
    */

    $shortProductTitle =
        \Illuminate\Support\Str::words(
            $product->title,
            15,
            '...'
        );
@endphp

<div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">

<article
    class="card-parent product-card"
    data-product-id="{{ $product->id }}"
>

    <div class="card-image">

        <div
            class="background-image"
            style="background-image: url('{{ $mainImageUrl }}');"
        >

            <div class="image-overlay"></div>

            @if ($discountPercentage !== null)

                <div class="card-discount-badge">
                    -{{ $discountPercentage }}%
                </div>

            @endif

            <div class="post-link product-link">

                <a
                    href="{{ $productUrl }}"
                    class="moving-circle"
                    aria-label="View {{ $product->title }}"
                >
                    View Product
                </a>

            </div>

        </div>

    </div>

    <div class="card-information">

        @if ($galleryCount > 0)

            <div class="d-flex gallery-images">

                @foreach (
                    $visibleGalleryImages
                    as $galleryImage
                )

                    <button
                        type="button"
                        class="gallery-image product-card-gallery-image"
                        data-image="{{ $galleryImage['url'] }}"
                        aria-label="Show {{ $product->title }} image"
                    >
                        <img
                            src="{{ $galleryImage['url'] }}"
                            loading="lazy"
                            decoding="async"
                            alt="{{ $galleryImage['alt'] }}"
                        >
                    </button>

                @endforeach

                @if (
                    $remainingGalleryCount > 0
                    && $fifthGalleryImage
                )

                    <button
                        type="button"
                        class="gallery-image product-card-gallery-image gallery-more-image"
                        data-image="{{ $fifthGalleryImage['url'] }}"
                        aria-label="Show {{ $remainingGalleryCount }} more images"
                    >

                        <img
                            src="{{ $fifthGalleryImage['url'] }}"
                            loading="lazy"
                            decoding="async"
                            alt="{{ $product->title }}"
                        >

                        <span class="gallery-more-overlay">
                            +{{ $remainingGalleryCount }}
                        </span>

                    </button>

                @endif

            </div>

        @endif

        <div class="post-card-description">

            <div class="card-heading-description">

                <a
                    href="{{ $productUrl }}"
                    class="product-title"
                >
                    <h3 class="heading fs-18 text-color-dark">
                        {{ $shortProductTitle }}
                    </h3>
                </a>

                <div class="product-price-wrapper">

                    @if ($hasDiscount)

                        <span class="product-regular-price fs-14 text-color-body">
                            ${{ number_format(
                                $regularPrice,
                                2
                            ) }}
                        </span>

                        <span class="product-sale-price fs-16 text-color-dark">
                            ${{ number_format(
                                $salePrice,
                                2
                            ) }}
                        </span>

                    @else

                        <span class="product-sale-price fs-16 text-color-dark">
                            ${{ number_format(
                                $regularPrice,
                                2
                            ) }}
                        </span>

                    @endif

                </div>

                @if ($discountPercentage !== null)

                    <div class="discount-percentage">
                        Save {{ $discountPercentage }}%
                    </div>

                @endif

                <div
                    class="product-card-stock {{
                        $stock > 0
                            ? 'in-stock'
                            : 'out-of-stock'
                    }}"
                >
                    @if ($stock > 0)

                        {{ $stock }} available

                    @else

                        Out of stock

                    @endif
                </div>

            </div>

            <div class="product-btns">

                <div class="add-to-cart-view-now-btn d-flex gap-10px mb-10px">

                    <button
                        type="button"
                        class="view-now open-product-popup"
                        data-popup-url="{{ route(
                            'products.quick-view',
                            $product->id
                        ) }}"
                        aria-label="View {{ $product->title }}"
                    >
                        View Now
                    </button>

                    <form
                        action="{{ route('cart.add') }}"
                        method="POST"
                        class="product-card-action-form"
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
                            class="view-now"
                            aria-label="Add {{ $product->title }} to cart"
                            {{ $stock < 1
                                ? 'disabled'
                                : '' }}
                        >
                            Add To Cart
                        </button>

                    </form>

                </div>

                <div class="add-to-cart-view-now-btn d-flex gap-10px">

                    <form
                        action="{{ route('favorite.toggle') }}"
                        method="POST"
                        class="product-card-action-form"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="product_id"
                            value="{{ $product->id }}"
                        >

                        <button
                            type="submit"
                            class="view-now"
                            aria-label="Add {{ $product->title }} to favourite"
                        >
                            Add To Favourite
                        </button>

                    </form>

                    <form
                        action="{{ route('cart.add') }}"
                        method="POST"
                        class="product-card-action-form"
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

                        <input
                            type="hidden"
                            name="buy_now"
                            value="1"
                        >

                        <button
                            type="submit"
                            class="view-now"
                            aria-label="Buy {{ $product->title }} now"
                            {{ $stock < 1
                                ? 'disabled'
                                : '' }}
                        >
                            Buy Now
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="post-card-circle"></div>

    </div>

</article>

</div></div></div>