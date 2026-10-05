<?php
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

    $discountPercentage=null;

    if ($hasDiscount) {
    $discountPercentage=round(
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

    $stock = app(\App\Services\StoreSettingsService::class)->available((int)$product->stock);

    $isOutOfStock = $productVariants->isNotEmpty()
        ? !$productVariants->contains(
            fn ($variant) => app(\App\Services\StoreSettingsService::class)->available((int)$variant->stock) > 0
        )
        : $stock < 1;

        /*
        |--------------------------------------------------------------------------
        | Product title
        |--------------------------------------------------------------------------
        */

        $shortProductTitle=\Illuminate\Support\Str::words(
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
        ?>

        <div class="post-and-categories">
            <div class="post-cards-parent">
                <div class="parent-wrapper">

                    <article
                        class="card-parent product-card"
                        data-product-id="<?php echo e($product->id); ?>">

                        <div class="card-image">

                            <div
                                class="background-image"
                                style="background-image: url('<?php echo e($mainImageUrl); ?>');">

                                <div class="image-overlay"></div>

                                <?php if($discountPercentage !== null): ?>
                                <div
                                    class="card-discount-badge fs-12 text-uppercase letter-space-4px">
                                    -<?php echo e($discountPercentage); ?>%
                                </div>
                                <?php endif; ?>
                                <?php if($product->is_featured): ?>
                                <div
                                    class="product-featured-badge fs-12 text-uppercase letter-space-4px">
                                    Featured
                                </div>
                                <?php endif; ?>

                                <div class="post-link product-link">
                                    <a
                                        href="<?php echo e($productUrl); ?>"
                                        class="moving-circle"
                                        aria-label="View <?php echo e($product->title); ?>">
                                        View Product
                                    </a>
                                </div>

                            </div>

                        </div>

                        <div class="card-information">

                            <?php if($galleryCount > 0): ?>
                            <div class="d-flex gallery-images">

                                <?php $__currentLoopData = $visibleGalleryImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $galleryImage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    type="button"
                                    class="gallery-image product-card-gallery-image"
                                    data-image="<?php echo e($galleryImage['url']); ?>"
                                    aria-label="Show <?php echo e($product->title); ?> image">

                                    <img
                                        src="<?php echo e($galleryImage['url']); ?>"
                                        loading="lazy"
                                        decoding="async"
                                        alt="<?php echo e($galleryImage['alt']); ?>">
                                </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if(
                                $remainingGalleryCount > 0
                                && $fifthGalleryImage
                                ): ?>
                                <button
                                    type="button"
                                    class="gallery-image product-card-gallery-image gallery-more-image"
                                    data-image="<?php echo e($fifthGalleryImage['url']); ?>"
                                    aria-label="Show <?php echo e($remainingGalleryCount); ?> more images">

                                    <img
                                        src="<?php echo e($fifthGalleryImage['url']); ?>"
                                        loading="lazy"
                                        decoding="async"
                                        alt="<?php echo e($product->title); ?>">

                                    <span class="gallery-more-overlay">
                                        +<?php echo e($remainingGalleryCount); ?>

                                    </span>
                                </button>
                                <?php endif; ?>

                            </div>
                            <?php endif; ?>

                            <div class="post-card-description">

                                <div class="card-heading-description">

                                    <a
                                        href="<?php echo e($productUrl); ?>"
                                        class="product-title fs-24 text-color-dark">

                                        <h3
                                            class="heading fs-18 text-color-dark text-capitalize">
                                            <?php echo e($shortProductTitle); ?>

                                        </h3>
                                    </a>

                                    <div
                                        class="product-price-wrapper justify-content-between">

                                        <div
                                            class="d-flex gap-10px align-items-center">

                                            <?php if($hasDiscount): ?>
                                            <span
                                                class="product-regular-price fs-14 text-color-body">
                                                <?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?><?php echo e(number_format($regularPrice, 2)); ?>

                                            </span>

                                            <span
                                                class="product-sale-price fs-16 text-color-dark">
                                                <?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?><?php echo e(number_format($salePrice, 2)); ?>

                                            </span>
                                            <?php else: ?>
                                            <span
                                                class="product-sale-price fs-16 text-color-dark">
                                                <?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?><?php echo e(number_format($regularPrice, 2)); ?>

                                            </span>
                                            <?php endif; ?>

                                        </div>

                                        <div
                                            class="product-rating d-flex align-items-center gap-5px">

                                            <div class="stars">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <?php if($i <=$fullStars): ?>
                                                    <i class="fa-solid fa-star"></i>
                                                    <?php elseif(
                                                    $i === $fullStars + 1
                                                    && $hasHalfStar
                                                    ): ?>
                                                    <i
                                                        class="fa-solid fa-star-half-stroke"></i>
                                                    <?php else: ?>
                                                    <i class="fa-regular fa-star"></i>
                                                    <?php endif; ?>
                                                    <?php endfor; ?>
                                            </div>

                                            <span class="rating-text">
                                                <?php echo e(number_format($rating, 1)); ?>


                                                (<?php echo e($reviewCount); ?>

                                                <?php echo e(\Illuminate\Support\Str::plural('review', $reviewCount)); ?>)
                                            </span>

                                        </div>

                                    </div>

                                    <?php if($discountPercentage !== null): ?>
                                    <div
                                        class="discount-percentage fs-12 text-uppercase letter-space-4px">
                                        Save <?php echo e($discountPercentage); ?>%
                                    </div>
                                    <?php endif; ?>

                                    <div
                                        class="product-card-stock fs-12 text-uppercase letter-space-4px <?php echo e($isOutOfStock ? 'out-of-stock' : 'in-stock'); ?>">
                                        <?php echo e($isOutOfStock ? 'Out of Stock' : 'Available'); ?>

                                    </div>

                                </div>

                                <div class="product-btns">

                                    <div
                                        class="add-to-cart-view-now-btn d-flex gap-10px mb-10px">

                                        
                                        <button
                                            type="button"
                                            class="view-now open-product-popup btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                            data-popup-url="<?php echo e($quickViewUrl); ?>"
                                            data-quick-view-action="view"
                                            aria-label="Quick view <?php echo e($product->title); ?>">

                                            <div
                                                class="button-text text-uppercase letter-space-3px">
                                                View Now
                                            </div>
                                        </button>

                                        
                                        <?php if($requiresSelection): ?>

                                        <button
                                            type="button"
                                            class="view-now open-product-popup btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                            data-popup-url="<?php echo e($quickViewUrl); ?>"
                                            data-quick-view-action="add-to-cart"
                                            aria-label="Select options for <?php echo e($product->title); ?>"
                                            <?php echo e($isOutOfStock ? 'disabled' : ''); ?>>

                                            <div
                                                class="button-text text-uppercase letter-space-3px">
                                                Add To Cart
                                            </div>
                                        </button>

                                        <?php else: ?>

                                        <form
                                            action="<?php echo e(route('cart.add')); ?>"
                                            method="POST"
                                            class="product-card-action-form w-100">

                                            <?php echo csrf_field(); ?>

                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?php echo e($product->id); ?>">

                                            <input
                                                type="hidden"
                                                name="quantity"
                                                value="1">

                                            <button
                                                type="submit"
                                                class="view-now btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                                data-add-to-cart
                                                data-ready-text="Add To Cart"
                                                aria-label="Add <?php echo e($product->title); ?> to cart"
                                                <?php echo e($isOutOfStock ? 'disabled' : ''); ?>>

                                                <div
                                                    class="button-text text-uppercase letter-space-3px">
                                                    Add To Cart
                                                </div>
                                            </button>

                                        </form>

                                        <?php endif; ?>

                                    </div>

                                    <div
                                        class="add-to-cart-view-now-btn d-flex gap-10px">

                                        <?php if(auth()->guard()->check()): ?>
                                        <?php
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
                                        ?>

                                        <form
                                            action="<?php echo e(route('favorite.toggle')); ?>"
                                            method="POST"
                                            class="product-card-action-form w-100">

                                            <?php echo csrf_field(); ?>

                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?php echo e($product->id); ?>">

                                            <button
                                                type="submit"
                                                class="favorite-button btn-style-2 fs-12 text-color-white justify-self-start w-100 <?php echo e($isFavorite ? 'active' : ''); ?>"
                                                aria-label="<?php echo e($isFavorite
                                                ? 'Remove from Favorites'
                                                : 'Add to Favorites'); ?>">

                                                <div
                                                    class="button-text text-uppercase letter-space-3px">
                                                    <?php echo e($isFavorite
                                                    ? 'Remove from Favorites'
                                                    : 'Add to Favorites'); ?>

                                                </div>
                                            </button>

                                        </form>
                                        <?php else: ?>
                                        <a
                                            href="<?php echo e(route('login')); ?>"
                                            class="favorite-button btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                            aria-label="Login to add <?php echo e($product->title); ?> to favorites">

                                            <div
                                                class="button-text text-uppercase letter-space-3px">
                                                Login To Add Favorite
                                            </div>
                                        </a>
                                        <?php endif; ?>

                                        
                                        <?php if($requiresSelection): ?>

                                        <button
                                            type="button"
                                            class="view-now open-product-popup btn-style-2 fs-12 text-color-white justify-self-start w-100"
                                            data-popup-url="<?php echo e($quickViewUrl); ?>"
                                            data-quick-view-action="buy-now"
                                            aria-label="Select options and buy <?php echo e($product->title); ?>"
                                            <?php echo e($isOutOfStock ? 'disabled' : ''); ?>>

                                            <div
                                                class="button-text text-uppercase letter-space-3px">
                                                Buy Now
                                            </div>
                                        </button>

                                        <?php else: ?>

                                        <form
                                            method="POST"
                                            action="<?php echo e(route('cart.add')); ?>"
                                            class="product-card-action-form w-100">

                                            <?php echo csrf_field(); ?>

                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?php echo e($product->id); ?>">

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
                                                data-buy-now
                                                data-ready-text="Buy Now"
                                                aria-label="Buy <?php echo e($product->title); ?> now"
                                                <?php echo e($isOutOfStock ? 'disabled' : ''); ?>>

                                                <div
                                                    class="button-text text-uppercase letter-space-3px">
                                                    Buy Now
                                                </div>
                                            </button>

                                        </form>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                            <div class="post-card-circle"></div>

                        </div>

                    </article>

                </div>
            </div>
        </div>