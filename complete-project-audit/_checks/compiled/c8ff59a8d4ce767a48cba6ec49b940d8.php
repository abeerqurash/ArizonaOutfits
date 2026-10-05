

<?php $__env->startSection('title', $product->meta_title ?: $product->title); ?>

<?php $__env->startSection(
'meta_description',
$product->meta_description
?: strip_tags($product->short_description ?: '')
); ?>

<?php $__env->startSection('content'); ?>

<?php
/*
|--------------------------------------------------------------------------
| Image URL helper
|--------------------------------------------------------------------------
*/

$getProductImageUrl = fn($path)=>app(\App\Services\PublicMediaService::class)->url($path);

/*
|--------------------------------------------------------------------------
| Product collections
|--------------------------------------------------------------------------
*/

$productCategories = $product->categories ?: collect();
$productTags = $product->tags ?: collect();
$productImages = ($product->images ?: collect())->filter(fn($image)=>app(\App\Services\PublicMediaService::class)->exists($image->image));
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

$featuredImageUrl = app(\App\Services\PublicMediaService::class)->productImage($product);

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

$defaultStock = app(\App\Services\StoreSettingsService::class)->available((int)$product->stock);

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

'available' => app(\App\Services\StoreSettingsService::class)->available((int)$variant->stock) > 0,

'image' => $variantImageUrl,

'options' => $cleanVariantOptions,
];
}

$hasVariants = count($variantsData) > 0;

$productAvailable = $hasVariants
    ? collect($variantsData)->contains(
        fn ($variant) => !empty($variant['available'])
    )
    : $defaultStock > 0;

$singleVariantUnavailable =
    $hasVariants
    && count($variantsData) === 1
    && empty($variantsData[0]['available']);

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
    ?>
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

                    
                    <ul class="bread-crumbs list-style-none fs-12 text-uppercase letter-space-4px mb-10px d-flex">

                        <li>
                            <a href="<?php echo e(route('home-page')); ?>" class="text-decoration-none text-color-dark">
                                Home
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo e(route('products.index')); ?>" class="text-decoration-none text-color-dark">
                                Products
                            </a>
                        </li>

                        <?php if($productCategories->isNotEmpty()): ?>

                        <li>
                            <a
                                href="<?php echo e(route(
                            'products.category',
                            $productCategories->first()->slug
                        )); ?>" class="text-decoration-none text-color-dark">
                                <?php echo e($productCategories->first()->title); ?>

                            </a>
                        </li>

                        <?php endif; ?>

                        <li>
                            <?php echo e($product->title); ?>

                        </li>

                    </ul>

                    <div class="single-product-main">

                        
                        <div class="single-product-images single-product-gallery product-gallery">

                            <div class="single-featured-image-wrapper">

                                <?php if($discountPercent !== null): ?>

                                <div
                                    id="product-discount-badge"
                                    class="discount-badge fs-12 text-uppercase letter-space-4px">
                                    -<?php echo e($discountPercent); ?>%
                                </div>

                                <?php else: ?>

                                <div
                                    id="product-discount-badge"
                                    class="fs-12 text-uppercase letter-space-4px discount-badge"
                                    style="display:none;"></div>

                                <?php endif; ?>

                                <img
                                    id="main-product-image"
                                    src="<?php echo e($featuredImageUrl); ?>"
                                    alt="<?php echo e($product->title); ?>"
                                    class="single-featured-image single-product-main-image product-main-image">

                            </div>

                            <div class="single-gallery">

                                <button
                                    type="button"
                                    class="single-gallery-item single-gallery-thumbnail product-gallery-thumbnail active"
                                    data-image="<?php echo e($featuredImageUrl); ?>">
                                    <img
                                        src="<?php echo e($featuredImageUrl); ?>"
                                        alt="<?php echo e($product->title); ?>">
                                </button>

                                <?php $__currentLoopData = $productImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <?php
                                $galleryImageUrl =
                                $getProductImageUrl(
                                $image->image
                                );
                                ?>

                                <button
                                    type="button"
                                    class="single-gallery-item single-gallery-thumbnail product-gallery-thumbnail"
                                    data-image="<?php echo e($galleryImageUrl); ?>">
                                    <img
                                        src="<?php echo e($galleryImageUrl); ?>"
                                        alt="<?php echo e($product->title); ?>">
                                </button>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php $__currentLoopData = $productVariants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <?php if(!empty($variant->image)): ?>

                                <?php
                                $variantGalleryImageUrl =
                                $getProductImageUrl(
                                $variant->image
                                );
                                ?>

                                <button
                                    type="button"
                                    class="single-gallery-item single-gallery-thumbnail product-gallery-thumbnail"
                                    data-image="<?php echo e($variantGalleryImageUrl); ?>">
                                    <img
                                        src="<?php echo e($variantGalleryImageUrl); ?>"
                                        alt="<?php echo e($product->title); ?> variant">
                                </button>

                                <?php endif; ?>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>

                        </div>

                        
                        <div class="single-product-info">

                            <div class="product-status-row">

                                <span
                                    id="product-stock-badge"
                                    class="fs-12 text-uppercase letter-space-4px product-stock-badge <?php echo e($productAvailable
                ? 'in-stock'
                : 'out-of-stock'); ?>">
                                    <?php echo e($productAvailable ? 'In Stock' : 'Out of Stock'); ?>

                                </span>

                                <?php if($discountPercent): ?>
                                <span class="product-save-badge fs-12 text-uppercase letter-space-4px">
                                    Save <?php echo e($discountPercent); ?>%
                                </span>
                                <?php endif; ?>

                            </div>

                            <h1 class="fs-48 text-color-dark mb-10px text-capitalize">
                                <?php echo e($product->title); ?>

                            </h1>

                            
                            <div class="single-product-rating">

                                <div class="rating-stars fs-16 text-uppercase letter-space-4px">

                                    <?php for($star = 1; $star <= 5; $star++): ?>

                                        <span
                                        class="<?php echo e($star <= round($averageRating)
                                        ? 'filled'
                                        : ''); ?> fs-16 text-uppercase letter-space-4px">
                                        ★
                                        </span>

                                        <?php endfor; ?>

                                </div>

                                <span class="fs-12 text-uppercase letter-space-4px">
                                    <?php echo e(number_format(
                            $averageRating,
                            1
                        )); ?>


                                    (<?php echo e($reviewCount); ?>

                                    <?php echo e($reviewCount === 1
                            ? 'review'
                            : 'reviews'); ?>)
                                </span>

                            </div>

                            
                            <div class="product-price">

                                <span
                                    id="product-regular-price"
                                    class="regular-price fs-16 text-uppercase letter-space-4px"
                                    style="<?php echo e($defaultSalePrice !== null
                            && $defaultSalePrice
                                < $defaultRegularPrice
                                ? ''
                                : 'display:none;'); ?>">
                                    <?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?><?php echo e(number_format(
                            $defaultRegularPrice,
                            2
                        )); ?>

                                </span>

                                <span
                                    id="product-sale-price"
                                    class="sale-price fs-16 text-uppercase letter-space-4px">
                                    <?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?><?php echo e(number_format(
                            $defaultSalePrice !== null
                            && $defaultSalePrice
                                < $defaultRegularPrice
                                ? $defaultSalePrice
                                : $defaultRegularPrice,
                            2
                        )); ?>

                                </span>

                            </div>

                            
                            <?php if(!empty($product->short_description)): ?>

                            <div class="single-product-short-description text-color-body fs-16">
                                <?php echo nl2br(
                                e($product->short_description)
                                ); ?>

                            </div>

                            <?php endif; ?>

                            
                            <form
                                action="<?php echo e(route('cart.add')); ?>"
                                method="POST"
                                id="add-to-cart-form"
                                class="product-form single-product-form"
                                data-product-form
                                data-currency-symbol="<?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?>">
                                <?php echo csrf_field(); ?>

                                <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                                <input type="hidden" name="variant_id" id="selected-variant-id" value="">

                                
                                <?php if(
                                $productOptions->isNotEmpty()
                                && $productOptionValues->isNotEmpty()
                                ): ?>

                                <div class="single-product-options">

                                    <?php $__currentLoopData = $productOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <?php
                                    $optionValues =
                                    $groupedOptionValues->get(
                                    $option->id,
                                    collect()
                                    );
                                    ?>

                                    <?php if($optionValues->isNotEmpty()): ?>

                                    <div
                                        class="single-product-option product-option-group"
                                        data-option-id="<?php echo e($option->id); ?>">

                                        <div class="product-option-heading">

                                            <span class="product-option-name fs-16 text-uppercase letter-space-4px">
                                                <?php echo e($option->name); ?>

                                            </span>

                                            <span
                                                class="selected-option-value fs-16 text-uppercase letter-space-4px"
                                                id="selected-option-value-<?php echo e($option->id); ?>"
                                                data-option-id="<?php echo e($option->id); ?>"
                                                data-selected-option="<?php echo e($option->id); ?>">
                                                Choose <?php echo e($option->name); ?>

                                            </span>

                                        </div>

                                        
                                        <select
                                            id="product-option-<?php echo e($option->id); ?>"
                                            name="product_options[<?php echo e($option->id); ?>]"
                                            class="product-option-select hidden-product-option-select"
                                            data-option-id="<?php echo e($option->id); ?>"
                                            aria-label="Choose <?php echo e($option->name); ?>"
                                            required>
                                            <option value="">
                                                Choose <?php echo e($option->name); ?>

                                            </option>

                                            <?php $__currentLoopData = $optionValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <?php
                                            $optionValueLabel =
                                            $value->label
                                            ?: $value->value;
                                            ?>

                                            <option
                                                value="<?php echo e($value->id); ?>"
                                                data-label="<?php echo e($optionValueLabel); ?>">
                                                <?php echo e($optionValueLabel); ?>

                                            </option>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        </select>

                                        <div
                                            class="option-value-buttons product-option-values"
                                            role="group"
                                            aria-label="<?php echo e($option->name); ?>">

                                            <?php $__currentLoopData = $optionValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <?php
                                            $buttonValueLabel =
                                            $value->label
                                            ?: $value->value;

                                            $buttonColorCode =
                                            $value->color_code
                                            ?? null;
                                            ?>

                                            <button
                                                type="button"
                                                class="option-value-button product-option-value btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                                data-option-id="<?php echo e($option->id); ?>"
                                                data-value-id="<?php echo e($value->id); ?>"
                                                data-value-label="<?php echo e($buttonValueLabel); ?>"
                                                data-label="<?php echo e($buttonValueLabel); ?>"
                                                aria-pressed="false"
                                                aria-label="Select <?php echo e($buttonValueLabel); ?>">
                                                <?php if(!empty($buttonColorCode)): ?>

                                                <span
                                                    class="option-color-circle product-option-color"
                                                    style="background-color: <?php echo e($buttonColorCode); ?>;"
                                                    aria-hidden="true"></span>

                                                <?php endif; ?>

                                                <span class="product-option-label">
                                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);"><?php echo e($buttonValueLabel); ?></div>
                                                    
                                                </span>

                                            </button>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        </div>

                                        <div
                                            class="product-option-error"
                                            id="product-option-error-<?php echo e($option->id); ?>"
                                            data-option-error="<?php echo e($option->id); ?>"
                                            aria-live="polite"></div>

                                    </div>

                                    <?php endif; ?>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </div>

                                <?php endif; ?>

                                <?php if($hasVariants): ?>



                                <div
                                    id="variant-message"
                                    class="variant-message"
                                    data-variant-message
                                    aria-live="polite">
                                    <?php if($singleVariantUnavailable): ?>
                                        This product is currently out of stock and cannot be purchased.
                                    <?php else: ?>
                                        Select all available options.
                                    <?php endif; ?>
                                </div>

                                <?php endif; ?>

                                <div
                                    class="product-stock-message <?php echo e($productAvailable ? 'in-stock' : 'out-of-stock'); ?>"
                                    data-stock-message
                                    aria-live="polite"
                                    <?php echo e($productAvailable ? 'hidden' : ''); ?>>
                                    This product is currently out of stock and cannot be purchased.
                                </div>

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
                                            >

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
                                        <?php if(
                                        $hasVariants
                                        || (!$hasVariants && $defaultStock < 1)
                                        ): echo 'disabled'; endif; ?>>
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);"><?php if(!$productAvailable): ?>
                                            Out of Stock
                                            <?php else: ?>
                                            Add To Cart
                                            <?php endif; ?></div>

                                    </button>

                                    <button
                                        type="submit"
                                        name="buy_now"
                                        value="1"
                                        class="buy-now-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                        data-buy-now
                                        data-ready-text="Buy Now"
                                        <?php if(
                                        $hasVariants
                                        || (!$hasVariants && $defaultStock < 1)
                                        ): echo 'disabled'; endif; ?>>
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                            <?php if(!$productAvailable): ?>
                                            Out of Stock
                                            <?php else: ?>
                                            Buy Now
                                            <?php endif; ?>
                                                </div>
                                    </button>

                                </div>

                                <script type="application/json" data-product-variants>
                                    <?php echo json_encode($variantsData, 15, 512) ?>
                                </script>

                            </form>

                            <div class="product-action-buttons">

                                <?php if(auth()->guard()->check()): ?>

                                <?php
                                $isFavorite = \App\Models\Favorite::where('user_id', auth()->id())
                                ->where('product_id', $product->id)
                                ->exists();
                                ?>

                                <form action="<?php echo e(route('favorite.toggle')); ?>" method="POST">
                                    <?php echo csrf_field(); ?>

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?php echo e($product->id); ?>">

                                    <button
                                        type="submit"
                                        class="favorite-btn btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer <?php echo e($isFavorite ? 'active' : ''); ?>"><div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                        <?php echo e($isFavorite ? 'Remove from Favorites' : 'Add to Favorites'); ?> </div>
                                    </button>
                                </form>

                                <?php else: ?>

                                <a
                                    href="<?php echo e(route('login')); ?>"
                                    class="favorite-btn fs-16 text-uppercase letter-space-4px btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                    Login to Add to Favorites
</div>
                                </a>

                                <?php endif; ?>

                            </div>

                            
                            <div class="product-meta fs-14 text-uppercase letter-space-4px flex-wrap d-flex gap-10px align-items-center ">

                                <p>
                                    <strong>SKU:</strong>

                                    <span id="product-sku">
                                        <?php echo e($defaultSku); ?>

                                    </span>
                                </p>

                                <div class="product-stock-wrapper">
                                    <span class="stock-label">
                                        <strong>Availability:</strong>
                                    </span>

                                    <span
                                        id="product-stock"
                                        class="stock-count <?php echo e($productAvailable ? 'in-stock' : 'out-of-stock'); ?>"
                                        data-product-stock>
                                        <?php echo e($productAvailable ? 'Available' : 'Out of Stock'); ?>

                                    </span>
                                </div>

                                <p>
                                    <strong>Status:</strong>

                                    <?php echo e(ucfirst(
                            $product->status ?: 'inactive'
                        )); ?>

                                </p>

                                <?php if($productCategories->isNotEmpty()): ?>

                                <p>
                                    <strong>Categories:</strong>

                                    <?php $__currentLoopData = $productCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <a
                                        href="<?php echo e(route(
                                        'products.category',
                                        $category->slug
                                    )); ?>"
                                        class="text-decoration-none text-color-dark">
                                        <?php echo e($category->title); ?>

                                    </a>

                                    <?php if(!$loop->last): ?>
                                    ,
                                    <?php endif; ?>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </p>

                                <?php endif; ?>

                                <?php if($productTags->isNotEmpty()): ?>

                                <p>
                                    <strong>Tags:</strong>

                                    <?php $__currentLoopData = $productTags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <?php echo e($tag->title); ?>


                                    <?php if(!$loop->last): ?>
                                    ,
                                    <?php endif; ?>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </p>

                                <?php endif; ?>

                                <p>
                                    <strong>Views:</strong>

                                    <?php echo e(number_format(
                            (int) (
                                $product->views_count ?: 0
                            )
                        )); ?>

                                </p>

                                <p>
                                    <strong>Favorites:</strong>

                                    <?php echo e(number_format(
                            (int) (
                                $product->favorites_count ?: 0
                            )
                        )); ?>

                                </p>

                                <!-- <p>
                                    <strong>Purchases:</strong>

                                    <?php echo e(number_format(
                            (int) (
                                $product->purchase_count ?: 0
                            )
                        )); ?>

                                </p> -->

                            </div>

                        </div>

                    </div>

                    
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

                                <?php if(!empty($product->long_description)): ?>

                                <div class="text-color-body fs-16 product-description-content">
                                    <?php echo app(\App\Services\HtmlContentSanitizer::class)->clean($product->long_description); ?>

                                </div>

                                <?php else: ?>

                                <p class="text-color-body fs-16">
                                    No description is available.
                                </p>

                                <?php endif; ?>

                            </section>
                        </div>

                        <div class="tab-content" id="additional-information">
                            <section class="product-tab-section">

                                <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
                                    Additional Information
                                </h2>

                                <?php if(!empty($product->additional_info)): ?>

                                <div class="product-additional-content text-color-body fs-16 mb-10px">
                                    <?php echo app(\App\Services\HtmlContentSanitizer::class)->clean($product->additional_info); ?>

                                </div>

                                <?php else: ?>

                                <p class="text-color-body fs-16 mb-10px">
                                    No additional information is available.
                                </p>

                                <?php endif; ?>


                                
                                <?php if(
                                $productOptions->isNotEmpty()
                                && $productOptionValues->isNotEmpty()
                                ): ?>

                                <section class="product-tab-section">

                                    <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
                                        Product Attributes
                                    </h2>

                                    <div class="product-table-wrapper">

                                        <table class="product-details-table">

                                            <tbody>

                                                <?php $__currentLoopData = $productOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                <?php
                                                $attributeValues =
                                                $groupedOptionValues
                                                ->get(
                                                $option->id,
                                                collect()
                                                );
                                                ?>

                                                <?php if(
                                                $attributeValues->isNotEmpty()
                                                ): ?>

                                                <tr>

                                                    <th class="fs-14 text-uppercase letter-space-4px">
                                                        <?php echo e($option->name); ?>

                                                    </th>

                                                    <td class="fs-14 text-uppercase letter-space-4px">

                                                        <?php $__currentLoopData = $attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attributeValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                        <?php echo e($attributeValue->label
                                                        ?: $attributeValue->value); ?>


                                                        <?php if(!$loop->last): ?>
                                                        ,
                                                        <?php endif; ?>

                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                    </td>

                                                </tr>

                                                <?php endif; ?>

                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            </tbody>

                                        </table>

                                    </div>

                                </section>

                                <?php endif; ?>

                                
                                <?php if($productVariants->isNotEmpty()): ?>

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

                                                <?php $__currentLoopData = $productVariants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                <?php
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
                                                ?>

                                                <tr>

                                                    <td>

                                                        <?php if(
                                                        count(
                                                        $tableVariantOptions
                                                        ) > 0
                                                        ): ?>

                                                        <?php $__currentLoopData = $tableVariantOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tableOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                        <?php echo e(isset(
                                                        $tableOption[
                                                            'option_name'
                                                        ]
                                                    )
                                                        ? $tableOption[
                                                            'option_name'
                                                        ]
                                                        : 'Option'); ?>


                                                        :

                                                        <?php echo e(isset(
                                                        $tableOption[
                                                            'value_label'
                                                        ]
                                                    )
                                                        ? $tableOption[
                                                            'value_label'
                                                        ]
                                                        : 'Value'); ?>


                                                        <?php if(!$loop->last): ?>
                                                        /
                                                        <?php endif; ?>

                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                        <?php else: ?>

                                                        Default variant

                                                        <?php endif; ?>

                                                    </td>

                                                    <td>
                                                        <?php echo e($variant->sku
                                                ?: $defaultSku); ?>

                                                    </td>

                                                    <td>

                                                        <?php if(
                                                        $tableSalePrice !== null
                                                        && $tableSalePrice
                                                        < $tableRegularPrice
                                                            ): ?>

                                                            <del>
                                                            <?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?><?php echo e(number_format(
                                                        $tableRegularPrice,
                                                        2
                                                    )); ?>

                                                            </del>

                                                            <strong>
                                                                <?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?><?php echo e(number_format(
                                                        $tableSalePrice,
                                                        2
                                                    )); ?>

                                                            </strong>

                                                            <?php else: ?>

                                                            <strong>
                                                                <?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?><?php echo e(number_format(
                                                        $tableRegularPrice,
                                                        2
                                                    )); ?>

                                                            </strong>

                                                            <?php endif; ?>

                                                    </td>

                                                    <td>

                                                        <?php if(
                                                        app(\App\Services\StoreSettingsService::class)->available((int)$variant->stock) > 0
                                                        ): ?>

                                                        <?php echo e((int)
                                                    $variant->stock); ?>

                                                        available

                                                        <?php else: ?>

                                                        Out of stock

                                                        <?php endif; ?>

                                                    </td>

                                                </tr>

                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            </tbody>

                                        </table>

                                    </div>

                                </section>

                                <?php endif; ?>

                            </section>
                        </div>
                    </div>






                    
                    
                    <section
                        class="product-tab-section product-reviews-section"
                        id="customer-reviews">
                        <div class="reviews-heading">

                            <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
                                Customer Reviews
                            </h2>

                            <div class="review-summary">

                                <strong class="fs-16 text-uppercase letter-space-4px">
                                    <?php echo e(number_format($averageRating, 1)); ?>/5
                                </strong>

                                <span class="fs-16 text-uppercase letter-space-4px">
                                    <?php echo e($reviewCount); ?>


                                    <?php echo e($reviewCount === 1
                    ? 'review'
                    : 'reviews'); ?>

                                </span>

                            </div>

                        </div>

                        <?php if(session('review_success')): ?>

                        <div
                            class="review-alert review-alert-success"
                            role="alert">
                            <?php echo e(session('review_success')); ?>

                        </div>

                        <?php endif; ?>

                        <?php if(session('review_error')): ?>

                        <div
                            class="review-alert review-alert-error"
                            role="alert">
                            <?php echo e(session('review_error')); ?>

                        </div>

                        <?php endif; ?>

                        <?php if($errors->any()): ?>

                        <div
                            class="review-alert review-alert-error"
                            role="alert">
                            <strong>
                                Please correct the following:
                            </strong>

                            <ul>
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <li>
                                    <?php echo e($error); ?>

                                </li>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>

                        <?php endif; ?>

                        <div class="reviews-layout">

                            
                            <div class="reviews-list">

                                <?php $__empty_1 = true; $__currentLoopData = $productReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                <article class="single-product-review">

                                    <div class="review-header">

                                        <div>

                                            <strong class="fs-16 text-uppercase letter-space-4px">
                                                <?php echo e($review->name ?: 'Customer'); ?>

                                            </strong>

                                            <?php if(!empty($review->created_at)): ?>

                                            <span class="review-date fs-16 text-uppercase letter-space-4px">
                                                <?php echo e($review->created_at->format(
                                        'F j, Y'
                                    )); ?>

                                            </span>

                                            <?php endif; ?>

                                        </div>

                                        <div
                                            class="rating-stars"
                                            aria-label="<?php echo e($review->rating); ?> out of 5 stars">
                                            <?php for(
                                            $reviewStar = 1;
                                            $reviewStar <= 5;
                                                $reviewStar++
                                                ): ?>

                                                <span
                                                class="<?php echo e($reviewStar <= (int) $review->rating
                                            ? 'filled'
                                            : ''); ?> fs-16 text-uppercase letter-space-4px">
                                                ★
                                                </span>

                                                <?php endfor; ?>
                                        </div>

                                    </div>

                                    <?php if(!empty($review->title)): ?>

                                    <h3 class="title fs-24 text-capitalize mb-10px">
                                        <?php echo e($review->title); ?>

                                    </h3>

                                    <?php endif; ?>

                                    <p class="text-color-body fs-16">
                                        <?php echo e($review->review); ?>

                                    </p>

                                </article>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                <p class="text-color-body fs-16">
                                    There are no approved reviews for this product yet.
                                    Be the first to submit one.
                                </p>

                                <?php endif; ?>

                            </div>

                            
                            <div class="product-review-form-wrapper">

                                <h3 class="title fs-24 text-capitalize mb-10px">
                                    Write a Review
                                </h3>

                                <p class="text-color-body fs-16 mb-10px">
                                    Your review will appear after it has been approved.
                                </p>

                                <form
                                    action="<?php echo e(route(
                    'reviews.store',
                    $product->id
                )); ?>"
                                    method="POST"
                                    class="product-review-form">
                                    <?php echo csrf_field(); ?>

                                    <?php if(auth('web')->check() && filled(auth('web')->user()->email)): ?>

                                    <div class="review-user-information">

                                        <p class="text-color-body fs-16">
                                            Reviewing as
                                            <strong>
                                                <?php echo e(auth('web')->user()->name); ?>

                                            </strong>
                                        </p>

                                        <p class="text-color-body fs-16">
                                            <?php echo e(auth('web')->user()->email); ?>

                                        </p>

                                    </div>

                                    <?php else: ?>

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
                                                value="<?php echo e(old('name', auth('web')->user()?->name)); ?>"
                                                maxlength="255"
                                                autocomplete="name"
                                                required>

                                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                                            <span class="review-field-error">
                                                <?php echo e($message); ?>

                                            </span>

                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                                                value="<?php echo e(old('email')); ?>"
                                                maxlength="255"
                                                autocomplete="email"
                                                required>

                                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                                            <span class="review-field-error">
                                                <?php echo e($message); ?>

                                            </span>

                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                                        </div>

                                    </div>

                                    <?php endif; ?>

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
                                                value="<?php echo e(old('rating')); ?>"
                                                required>

                                            <div class="review-select">

                                                <button
                                                    type="button"
                                                    class="review-select-trigger">
                                                    <span id="selected-rating-text" class="fs-14 text-uppercase letter-space-4px">
                                                        <?php switch(old('rating')):
                                                        case (5): ?> 5 - Excellent <?php break; ?>
                                                        <?php case (4): ?> 4 - Very Good <?php break; ?>
                                                        <?php case (3): ?> 3 - Good <?php break; ?>
                                                        <?php case (2): ?> 2 - Fair <?php break; ?>
                                                        <?php case (1): ?> 1 - Poor <?php break; ?>
                                                        <?php default: ?> Choose a rating
                                                        <?php endswitch; ?>
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

                                            <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="review-field-error fs-14 text-uppercase letter-space-4px">
                                                <?php echo e($message); ?>

                                            </span>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                                        </div>

                                        <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                                        <span class="review-field-error fs-14 text-uppercase letter-space-4px">
                                            <?php echo e($message); ?>

                                        </span>

                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                                            value="<?php echo e(old('title')); ?>"
                                            maxlength="255"
                                            placeholder="Summarise your experience">

                                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                                        <span class="review-field-error fs-14 text-uppercase letter-space-4px">
                                            <?php echo e($message); ?>

                                        </span>

                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                                            required><?php echo e(old('review')); ?></textarea>

                                        <?php $__errorArgs = ['review'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                                        <span class="review-field-error">
                                            <?php echo e($message); ?>

                                        </span>

                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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

                
                <?php if(
                isset($relatedProducts)
                && $relatedProducts->isNotEmpty()
                ): ?>

                <section class="related-products-section">

                    <h2 class="fs-48 text-color-dark mb-10px text-capitalize">
                        Related Products
                    </h2>

                    <div class="products-grid">

                        <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <?php echo $__env->make(
                        'products.partials.product-card',
                        [
                        'product' =>
                        $relatedProduct,
                        ]
                        , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </div>

                </section>

                <?php endif; ?>

        </div>

        </section>
    </div>
    </div>
    </div>


    <?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../replacement-files/_staged/resources/views\products\show.blade.php ENDPATH**/ ?>