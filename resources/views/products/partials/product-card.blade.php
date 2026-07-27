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
| Product relationships
|--------------------------------------------------------------------------
|
| These relationships must be defined before they are used below.
|
*/

$productVariants = $product->variants ?? collect();
$productOptions = $product->options ?? collect();
$productOptionValues = $product->optionValues ?? collect();

/*
|--------------------------------------------------------------------------
| Check whether product requires option selection
|--------------------------------------------------------------------------
|
| If a product has variants, options or option values, the Quick View
| popup will open so the customer can select the required combination.
|
*/

$requiresSelection =
    $productVariants->isNotEmpty()
    || $productOptions->isNotEmpty()
    || $productOptionValues->isNotEmpty();

/*
|--------------------------------------------------------------------------
| Product URLs
|--------------------------------------------------------------------------
*/

$productUrl = route(
    'products.show',
    $product->slug
);

$quickViewUrl = route(
    'products.quick-view',
    $product->id
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

if ($product->images) {
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

foreach ($productVariants as $variant) {
    if (empty($variant->image)) {
        continue;
    }

    $variantImageUrl = $getCardImageUrl(
        $variant->image
    );

    $alreadyExists = $galleryImages->contains(
        function ($image) use ($variantImageUrl) {
            return $image['url'] === $variantImageUrl;
        }
    );

    if (!$alreadyExists) {
        $galleryImages->push([
            'url' => $variantImageUrl,
            'alt' => $product->title,
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Remove main image from gallery if duplicated
|--------------------------------------------------------------------------
*/

$galleryImages = $galleryImages
    ->reject(function ($image) use ($mainImageUrl) {
        return $image['url'] === $mainImageUrl;
    })
    ->values();

$galleryCount = $galleryImages->count();

$visibleGalleryImages = $galleryImages->take(4);

$remainingGalleryCount = max(
    0,
    $galleryCount - 4
);

$fifthGalleryImage = $remainingGalleryCount > 0
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

$salePrice = $product->sale_price !== null
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
            ($regularPrice - $salePrice)
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

$isOutOfStock = $stock < 1;

/*
|--------------------------------------------------------------------------
| Product title
|--------------------------------------------------------------------------
*/

$shortProductTitle = \Illuminate\Support\Str::words(
    $product->title,
    15,
    '...'
);

/*
|--------------------------------------------------------------------------
| Product rating
|--------------------------------------------------------------------------
*/

$rating = round(
    $product->approved_reviews_avg_rating ?? 0,
    1
);

$reviewCount = (int) (
    $product->approved_reviews_count ?? 0
);

$fullStars = floor($rating);

$hasHalfStar =
    ($rating - $fullStars) >= 0.5;
@endphp

<div class="post-and-categories">
    <div class="post-cards-parent">
        <div class="parent-wrapper">

            <article
                class="card-parent product-card"
                data-product-id="{{ $product->id }}">

                <div class="card-image">

                    <div
                        class="background-image"
                        style="background-image: url('{{ $mainImageUrl }}');">

                        <div class="image-overlay"></div>

                        @if ($discountPercentage !== null)
                            <div
                                class="card-discount-badge fs-12 text-uppercase letter-space-4px">
                                -{{ $discountPercentage }}%
                            </div>
                        @endif

                        <div class="post-link product-link">
                            <a
                                href="{{ $productUrl }}"
                                class="moving-circle"
                                aria-label="View {{ $product->title }}">
                                View Product
                            </a>
                        </div>

                    </div>

                </div>

                <div class="card-information">

                    @if ($galleryCount > 0)
                        <div class="d-flex gallery-images">

                            @foreach ($visibleGalleryImages as $galleryImage)
                                <button
                                    type="button"
                                    class="gallery-image product-card-gallery-image"
                                    data-image="{{ $galleryImage['url'] }}"
                                    aria-label="Show {{ $product->title }} image">

                                    <img
                                        src="{{ $galleryImage['url'] }}"
                                        loading="lazy"
                                        decoding="async"
                                        alt="{{ $galleryImage['alt'] }}">
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
                                    aria-label="Show {{ $remainingGalleryCount }} more images">

                                    <img
                                        src="{{ $fifthGalleryImage['url'] }}"
                                        loading="lazy"
                                        decoding="async"
                                        alt="{{ $product->title }}">

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
                                class="product-title fs-24 text-color-dark">

                                <h3
                                    class="heading fs-18 text-color-dark text-capitalize">
                                    {{ $shortProductTitle }}
                                </h3>
                            </a>

                            <div
                                class="product-price-wrapper justify-content-between">

                                <div
                                    class="d-flex gap-10px align-items-center">

                                    @if ($hasDiscount)
                                        <span
                                            class="product-regular-price fs-14 text-color-body">
                                            ${{ number_format($regularPrice, 2) }}
                                        </span>

                                        <span
                                            class="product-sale-price fs-16 text-color-dark">
                                            ${{ number_format($salePrice, 2) }}
                                        </span>
                                    @else
                                        <span
                                            class="product-sale-price fs-16 text-color-dark">
                                            ${{ number_format($regularPrice, 2) }}
                                        </span>
                                    @endif

                                </div>

                                <div
                                    class="product-rating d-flex align-items-center gap-5px">

                                    <div class="stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $fullStars)
                                                <i class="fa-solid fa-star"></i>
                                            @elseif (
                                                $i === $fullStars + 1
                                                && $hasHalfStar
                                            )
                                                <i
                                                    class="fa-solid fa-star-half-stroke"></i>
                                            @else
                                                <i class="fa-regular fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>

                                    <span class="rating-text">
                                        {{ number_format($rating, 1) }}

                                        ({{ $reviewCount }}
                                        {{ \Illuminate\Support\Str::plural('review', $reviewCount) }})
                                    </span>

                                </div>

                            </div>

                            @if ($discountPercentage !== null)
                                <div
                                    class="discount-percentage fs-12 text-uppercase letter-space-4px">
                                    Save {{ $discountPercentage }}%
                                </div>
                            @endif

                            <div
                                class="product-card-stock fs-12 text-uppercase letter-space-4px {{ $stock > 0 ? 'in-stock' : 'out-of-stock' }}">

                                @if ($stock > 0)
                                    {{ $stock }} available
                                @else
                                    Out of stock
                                @endif

                            </div>

                        </div>

                        <div class="product-btns">

                            <div
                                class="add-to-cart-view-now-btn d-flex gap-10px mb-10px">

                                {{-- Quick View button --}}
                                <button
                                    type="button"
                                    class="view-now open-product-popup btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                    data-popup-url="{{ $quickViewUrl }}"
                                    aria-label="Quick view {{ $product->title }}">

                                    <div
                                        class="button-text text-uppercase letter-space-3px">
                                        View Now
                                    </div>
                                </button>

                                {{-- Add To Cart --}}
                                @if ($requiresSelection)

                                    <button
                                        type="button"
                                        class="view-now open-product-popup btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                        data-popup-url="{{ $quickViewUrl }}"
                                        aria-label="Select options for {{ $product->title }}"
                                        {{ $isOutOfStock ? 'disabled' : '' }}>

                                        <div
                                            class="button-text text-uppercase letter-space-3px">
                                            Add To Cart
                                        </div>
                                    </button>

                                @else

                                    <form
                                        action="{{ route('cart.add') }}"
                                        method="POST"
                                        class="product-card-action-form w-100">

                                        @csrf

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="{{ $product->id }}">

                                        <input
                                            type="hidden"
                                            name="quantity"
                                            value="1">

                                        <button
                                            type="submit"
                                            class="view-now btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                            aria-label="Add {{ $product->title }} to cart"
                                            {{ $isOutOfStock ? 'disabled' : '' }}>

                                            <div
                                                class="button-text text-uppercase letter-space-3px">
                                                Add To Cart
                                            </div>
                                        </button>

                                    </form>

                                @endif

                            </div>

                            <div
                                class="add-to-cart-view-now-btn d-flex gap-10px">

                                @auth
                                    @php
                                        $isFavorite =
                                            \App\Models\Favorite::query()
                                                ->where(
                                                    'user_id',
                                                    auth()->id()
                                                )
                                                ->where(
                                                    'product_id',
                                                    $product->id
                                                )
                                                ->exists();
                                    @endphp

                                    <form
                                        action="{{ route('favorite.toggle') }}"
                                        method="POST"
                                        class="product-card-action-form w-100">

                                        @csrf

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="{{ $product->id }}">

                                        <button
                                            type="submit"
                                            class="favorite-button btn-style-2 fs-12 text-color-white justify-self-start w-100 {{ $isFavorite ? 'active' : '' }}"
                                            aria-label="{{ $isFavorite
                                                ? 'Remove from Favorites'
                                                : 'Add to Favorites' }}">

                                            <div
                                                class="button-text text-uppercase letter-space-3px">
                                                {{ $isFavorite
                                                    ? 'Remove from Favorites'
                                                    : 'Add to Favorites' }}
                                            </div>
                                        </button>

                                    </form>
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="favorite-button btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                        aria-label="Login to add {{ $product->title }} to favorites">

                                        <div
                                            class="button-text text-uppercase letter-space-3px">
                                            Login To Add Favorite
                                        </div>
                                    </a>
                                @endauth

                                {{-- Buy Now --}}
                                @if ($requiresSelection)

                                    <button
                                        type="button"
                                        class="view-now open-product-popup btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                        data-popup-url="{{ $quickViewUrl }}"
                                        aria-label="Select options and buy {{ $product->title }}"
                                        {{ $isOutOfStock ? 'disabled' : '' }}>

                                        <div
                                            class="button-text text-uppercase letter-space-3px">
                                            Buy Now
                                        </div>
                                    </button>

                                @else

                                    <form
                                        method="POST"
                                        action="{{ route('cart.add') }}"
                                        class="product-card-action-form w-100">

                                        @csrf

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="{{ $product->id }}">

                                        <input
                                            type="hidden"
                                            name="quantity"
                                            value="1">

                                        <input
                                            type="hidden"
                                            name="buy_now"
                                            value="1">

                                        <button
                                            type="submit"
                                            class="view-now btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                            aria-label="Buy {{ $product->title }} now"
                                            {{ $isOutOfStock ? 'disabled' : '' }}>

                                            <div
                                                class="button-text text-uppercase letter-space-3px">
                                                Buy Now
                                            </div>
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </div>

                    </div>

                    <div class="post-card-circle"></div>

                </div>

            </article>

        </div>
    </div>
</div>