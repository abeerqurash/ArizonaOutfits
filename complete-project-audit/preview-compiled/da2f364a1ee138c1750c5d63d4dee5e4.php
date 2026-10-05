<?php $__env->startSection('title', isset($currentCategory) ? ($currentCategory->meta_title ?: $currentCategory->title.' | Shop') : 'Products'); ?>

<?php $__env->startSection(
'meta_description',
isset($currentCategory) ? ($currentCategory->meta_description ?: 'Shop '.$currentCategory->title.' at Arizona Outfits.') : 'Browse products, compare prices, explore categories and find the right product for your needs.'
); ?>

<?php $__env->startSection('content'); ?>

<?php
$catalogUrl = isset($currentCategory) ? route('products.category', $currentCategory->slug) : route('products.index');
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

            <section class="products-page">

                <div class="container">

                    <ul class="bread-crumbs list-style-none fs-12 text-uppercase letter-space-4px mb-10px d-flex gap-10px">

                        <li>
                            <a
                                href="<?php echo e(route('home-page')); ?>"
                                class="text-decoration-none text-color-dark">
                                Home
                            </a>
                        </li>

                        <?php if(isset($currentCategory)): ?>
                        <li><a href="<?php echo e(route('products.index')); ?>" class="text-decoration-none text-color-dark">Shop</a></li>
                        <?php if($currentCategory->parent): ?><li><a href="<?php echo e(route('products.category', $currentCategory->parent->slug)); ?>" class="text-decoration-none text-color-dark"><?php echo e($currentCategory->parent->title); ?></a></li><?php endif; ?>
                        <li aria-current="page"><?php echo e($currentCategory->title); ?></li>
                        <?php else: ?>
                        <li aria-current="page">Products</li>
                        <?php endif; ?>

                    </ul>





                    <?php if(isset($currentCategory)): ?>
                    <header class="az-category-intro">
                        <span>Arizona Outfits collection</span>
                        <h1><?php echo e($currentCategory->title); ?></h1>
                        <?php if($currentCategory->description): ?><p><?php echo e(strip_tags($currentCategory->description)); ?></p><?php endif; ?>
                        <?php if($currentCategory->children->isNotEmpty()): ?>
                        <nav class="az-category-children" aria-label="Subcategories">
                            <?php $__currentLoopData = $currentCategory->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('products.category', $child->slug)); ?>"><?php echo e($child->title); ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </nav>
                        <?php endif; ?>
                    </header>
                    <?php endif; ?>

                    <div class="shop-layout">

                        <details class="shop-sidebar az-shop-filters" id="shop-filter-box"><summary class="az-shop-filter-heading"><h2><i class="fa-solid fa-sliders" aria-hidden="true"></i> Filter products</h2><p>Open to search and filter products.</p><i class="fa-solid fa-chevron-down az-shop-filter-chevron" aria-hidden="true"></i></summary>


                            <form
                                method="GET"
                                action="<?php echo e($catalogUrl); ?>"
                                class="shop-filter-form"
                                id="shop-filter-form">
<div class="az-shop-filter-field"><label for="shop-product-search">Search products</label>
                                <input
                                    type="search" id="shop-product-search"
                                    name="search"
                                    placeholder="Search products"
                                    value="<?php echo e(request('search')); ?>"
                                    class="input-type-field mb-10px fs-12"></div>

                                <?php if (! (isset($currentCategory))): ?>
                                
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
                                            <?php if(count($selectedCategories)): ?>
                                            <?php echo e(count($selectedCategories)); ?>

                                            <?php echo e(count($selectedCategories) === 1 ? 'category' : 'categories'); ?>

                                            selected
                                            <?php else: ?>
                                            Select categories
                                            <?php endif; ?>
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

                                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <label
                                                class="filter-select-option"
                                                data-search-text="<?php echo e(strtolower($category->title)); ?>">
                                                <input
                                                    type="checkbox"
                                                    name="categories[]"
                                                    value="<?php echo e($category->slug); ?>"
                                                    <?php if(
                                                    in_array(
                                                    $category->slug,
                                                $selectedCategories,
                                                true
                                                )
                                                ): echo 'checked'; endif; ?>
                                                data-multi-select-checkbox
                                                >

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    <?php echo e($category->title); ?>

                                                </span>
                                            </label>

                                            <?php $__currentLoopData = $category->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <label
                                                class="filter-select-option filter-child-option"
                                                data-search-text="<?php echo e(strtolower($child->title)); ?>">
                                                <input
                                                    type="checkbox"
                                                    name="categories[]"
                                                    value="<?php echo e($child->slug); ?>"
                                                    <?php if(
                                                    in_array(
                                                    $child->slug,
                                                $selectedCategories,
                                                true
                                                )
                                                ): echo 'checked'; endif; ?>
                                                data-multi-select-checkbox
                                                >

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    <?php echo e($child->title); ?>

                                                </span>
                                            </label>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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

                                
                                
                                <?php endif; ?>
                                <?php $__currentLoopData = $filterOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <?php
                                $selectedOptionValues = array_map(
                                'strval',
                                (array) ($selectedOptions[$option->id] ?? [])
                                );
                                ?>

                                <div
                                    class="filter-multi-select mb-20px"
                                    data-multi-select>
                                    <h4 class="fs-24 text-color-dark mb-10px text-capitalize">
                                        <?php echo e($option->name); ?>

                                    </h4>

                                    <button
                                        type="button"
                                        class="filter-select-trigger input-type-field fs-12 text-uppercase letter-space-3px justify-content-between d-flex"
                                        data-multi-select-trigger
                                        aria-expanded="false">
                                        <span
                                            data-multi-select-label
                                            data-default-label="Select <?php echo e(strtolower($option->name)); ?>">
                                            <?php if(count($selectedOptionValues)): ?>
                                            <?php echo e(count($selectedOptionValues)); ?>

                                            selected
                                            <?php else: ?>
                                            Select <?php echo e(strtolower($option->name)); ?>

                                            <?php endif; ?>
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
                                                placeholder="Search <?php echo e(strtolower($option->name)); ?>"
                                                autocomplete="off"
                                                data-multi-select-search>
                                        </div>

                                        <div class="filter-select-options">

                                            <?php $__empty_1 = true; $__currentLoopData = $option->values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                            <?php
                                            $valueLabel =
                                            $value->value
                                            ?? $value->name
                                            ?? $value->title
                                            ?? 'Value ' . $value->id;
                                            ?>

                                            <label
                                                class="filter-select-option"
                                                data-search-text="<?php echo e(strtolower($valueLabel)); ?>">
                                                <input
                                                    type="checkbox"
                                                    name="options[<?php echo e($option->id); ?>][]"
                                                    value="<?php echo e($value->id); ?>"
                                                    <?php if(
                                                    in_array(
                                                    (string) $value->id,
                                                $selectedOptionValues,
                                                true
                                                )
                                                ): echo 'checked'; endif; ?>
                                                data-multi-select-checkbox
                                                >

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    <?php echo e($valueLabel); ?>

                                                </span>
                                            </label>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                            <div class="filter-no-results">
                                                No <?php echo e(strtolower($option->name)); ?> values available
                                            </div>

                                            <?php endif; ?>

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

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                
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
                                            <?php if(count($selectedRatings)): ?>
                                            <?php echo e(count($selectedRatings)); ?>

                                            <?php echo e(count($selectedRatings) === 1 ? 'rating' : 'ratings'); ?>

                                            selected
                                            <?php else: ?>
                                            Select customer rating
                                            <?php endif; ?>
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

                                            <?php
                                            $selectedRatingValues = array_map(
                                            'strval',
                                            $selectedRatings
                                            );
                                            ?>

                                            <?php $__currentLoopData = $ratingOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <label
                                                class="filter-select-option"
                                                data-search-text="<?php echo e(strtolower($label)); ?>">
                                                <input
                                                    type="checkbox"
                                                    name="ratings[]"
                                                    value="<?php echo e($value); ?>"
                                                    <?php if(
                                                    in_array(
                                                    (string) $value,
                                                    $selectedRatingValues,
                                                    true
                                                    )
                                                    ): echo 'checked'; endif; ?>
                                                    data-multi-select-checkbox>

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    <span class="filter-rating-stars">
                                                        <?php for($star = 1; $star <= 5; $star++): ?>
                                                            <i
                                                            class="<?php echo e($star <= (int) $value
                                        ? 'fa-solid'
                                        : 'fa-regular'); ?> fa-star"></i>
                                                            <?php endfor; ?>
                                                    </span>

                                                    <?php echo e($label); ?>

                                                </span>
                                            </label>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                                            <?php if(count($selectedAvailability)): ?>
                                            <?php echo e(count($selectedAvailability)); ?>

                                            selected
                                            <?php else: ?>
                                            Select availability
                                            <?php endif; ?>
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

                                            <?php $__currentLoopData = $availabilityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <label
                                                class="filter-select-option"
                                                data-search-text="<?php echo e(strtolower($label)); ?>">
                                                <input
                                                    type="checkbox"
                                                    name="availability[]"
                                                    value="<?php echo e($value); ?>"
                                                    <?php if(
                                                    in_array(
                                                    $value,
                                                    $selectedAvailability,
                                                    true
                                                    )
                                                    ): echo 'checked'; endif; ?>
                                                    data-multi-select-checkbox>

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    <?php echo e($label); ?>

                                                </span>
                                            </label>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                                            <?php if(count($selectedOffers)): ?>
                                            <?php echo e(count($selectedOffers)); ?>

                                            <?php echo e(count($selectedOffers) === 1 ? 'offer' : 'offers'); ?>

                                            selected
                                            <?php else: ?>
                                            Select offers
                                            <?php endif; ?>
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

                                            <?php $__currentLoopData = $offerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <label
                                                class="filter-select-option"
                                                data-search-text="<?php echo e(strtolower($label)); ?>">
                                                <input
                                                    type="checkbox"
                                                    name="offers[]"
                                                    value="<?php echo e($value); ?>"
                                                    <?php if(
                                                    in_array(
                                                    $value,
                                                    $selectedOffers,
                                                    true
                                                    )
                                                    ): echo 'checked'; endif; ?>
                                                    data-multi-select-checkbox>

                                                <span class="custom-checkbox"></span>

                                                <span class="filter-option-label">
                                                    <?php echo e($label); ?>

                                                </span>
                                            </label>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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

<div class="az-shop-filter-field"><label>Price range</label><div class="az-shop-price-fields">
<input type="number" name="min_price" aria-label="Minimum price" placeholder="Minimum price" value="<?php echo e(request('min_price')); ?>" min="0" step="0.01">
<input type="number" name="max_price" aria-label="Maximum price" placeholder="Maximum price" value="<?php echo e(request('max_price')); ?>" min="0" step="0.01">
</div></div>
                                <?php if(request('sort')): ?>
                                <input
                                    type="hidden"
                                    name="sort"
                                    value="<?php echo e(request('sort')); ?>">
                                <?php endif; ?>

                                <div class="az-shop-filter-actions">
                                    <button
                                        type="submit"
                                        class="filter-btn btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Filter</div>

                                    </button>

                                    <?php if(
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
                                    ): ?>
                                    <a
                                        href="<?php echo e($catalogUrl); ?>"
                                        class="filter-btn text-decoration-none btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Reset</div>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>

                        </details>

                        <div class="shop-content">

                            <div class="shop-topbar d-flex justify-content-between">

                                <div class="fs-12 text-uppercase letter-space-4px">

                                    <?php if($products->total() > 0): ?>

                                    Showing <?php echo e($products->firstItem()); ?>–<?php echo e($products->lastItem()); ?>

                                    of <?php echo e($products->total()); ?> products

                                    <?php else: ?>

                                    No products found

                                    <?php endif; ?>

                                </div>

                                <form
                                    method="GET"
                                    action="<?php echo e($catalogUrl); ?>"
                                    id="sort-form"
                                    class="custom-sort-form">

                                    

                                    <?php $__currentLoopData = request()->except([
                                    'sort',
                                    'page',
                                    'options',
                                    ]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <?php if(
                                    is_string($key)
                                    && is_scalar($value)
                                    ): ?>

                                    <input
                                        type="hidden"
                                        name="<?php echo e($key); ?>"
                                        value="<?php echo e((string) $value); ?>">

                                    <?php endif; ?>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    

                                    <?php $__currentLoopData = $selectedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionId => $optionValues): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <?php
                                    if (!is_array($optionValues)) {
                                    $optionValues = [
                                    $optionValues,
                                    ];
                                    }
                                    ?>

                                    <?php $__currentLoopData = $optionValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <?php if(
                                    is_scalar($optionId)
                                    && is_scalar($optionValue)
                                    ): ?>

                                    <input
                                        type="hidden"
                                        name="options[<?php echo e((string) $optionId); ?>][]"
                                        value="<?php echo e((string) $optionValue); ?>">

                                    <?php endif; ?>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <input
                                        type="hidden"
                                        name="sort"
                                        id="sort-value"
                                        value="<?php echo e($selectedSort); ?>">

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
                                                <?php echo e($sortLabel); ?>

                                            </span>

                                            <i
                                                class="fa-solid fa-chevron-down"
                                                aria-hidden="true"></i>
                                        </button>

                                        <div
                                            class="sort-options"
                                            id="sort-options"
                                            role="listbox">

                                            <?php $__currentLoopData = $sortLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sortValue => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <div
                                                class="sort-option <?php echo e($selectedSort === $sortValue ? 'active' : ''); ?>"
                                                data-value="<?php echo e($sortValue); ?>"
                                                role="option"
                                                aria-selected="<?php echo e($selectedSort === $sortValue ? 'true' : 'false'); ?>"
                                                tabindex="0">
                                                <?php echo e($label); ?>

                                            </div>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        </div>

                                    </div>

                                </form>

                            </div>

                            <?php if($products->isNotEmpty()): ?>

                            <div class="products-grid">

                                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <?php echo $__env->make(
                                'products.partials.product-card',
                                [
                                'product' => $product,
                                ]
                                , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>

                            <?php else: ?>

                            <div class="empty-products">

                                <h2 class="fs-24 text-color-dark mb-10px">
                                    No products found
                                </h2>

                                <p class="text-color-body fs-16">
                                    Try changing your search, category, variant,
                                    rating or price filters.
                                </p>

                            </div>

                            <?php endif; ?>

                            <?php if($products->hasPages()): ?>

                            <div class="shop-pagination">
                                <?php echo e($products->withQueryString()->links()); ?>

                            </div>

                            <?php endif; ?>

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

<?php $__env->stopSection(); ?>
<?php $__env->startPush('page-styles'); ?>
<style>
.products-page .shop-layout{display:block;grid-template-columns:none;width:100%}
.products-page .shop-content{width:100%;min-width:0;grid-column:auto}
.products-page .az-shop-filters{box-sizing:border-box;width:100%;max-width:none;margin:0 0 40px;padding:48px 50px;position:relative;top:auto;align-self:auto;overflow:visible;border:0;border-top:1px solid #e2e7f1;background:#f3f6fc;color:#090b19}
.az-shop-filter-heading{display:flex;align-items:center;gap:20px;flex-wrap:wrap;padding-bottom:18px;margin-bottom:36px;border-bottom:1px solid #d5d9e2}
.az-shop-filter-heading h2{margin:0;padding:11px 20px;border-radius:100px;background:#182132;color:#fff;font-size:12px;font-weight:600;letter-spacing:3px;text-transform:uppercase;line-height:1.5}
.az-shop-filter-heading p{margin:0;color:#6e7488;font-size:14px;line-height:1.6}
.az-shop-filters .shop-filter-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px 12px}
.az-shop-filters .filter-multi-select,.az-shop-filter-field{min-width:0;margin-bottom:0!important;position:relative}
.az-shop-filters h4,.az-shop-filter-field>label{display:block;margin:0 0 12px!important;color:#090b19;font-size:12px!important;font-weight:400;letter-spacing:4px;text-transform:uppercase!important;line-height:1.6}
.az-shop-filter-field input,.az-shop-filters .filter-select-trigger{box-sizing:border-box;width:100%;min-width:0;min-height:54px;height:54px;padding:8px 12px;border:1px solid #e2e7f1;border-radius:2px;background:#ffffffa6;color:#333;font-size:14px;line-height:1.42857;margin-bottom:0!important}
.az-shop-filters .filter-select-trigger{letter-spacing:1px}
.az-shop-filter-field input:focus,.az-shop-filters .filter-select-trigger:focus-visible{outline:2px solid #090b19;outline-offset:2px}
.az-shop-price-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.az-shop-filters .filter-select-dropdown{z-index:30;max-height:360px;overflow:auto;background:#fff}
.az-shop-filter-actions{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:12px;padding-top:8px}
.az-shop-filter-actions .filter-btn{display:inline-flex;align-items:center;justify-content:center;min-height:46px;min-width:160px;padding:14px 24px;border:1px solid #090b19;border-radius:100px;background:#090b19;color:#fff;font-size:11px;font-weight:500;letter-spacing:3px;line-height:1.5;text-decoration:none}
.az-shop-filter-actions a.filter-btn{background:transparent;color:#090b19;border-color:#c6cbd5}
.az-shop-filter-actions .filter-btn:hover{background:#e2e7f1;border-color:#e2e7f1;color:#090b19}
.products-page .shop-content .products-grid{height:auto;grid-template-columns:repeat(3,minmax(0,1fr));align-items:stretch;gap:24px;margin-top:28px}
.products-page .shop-content .products-grid>.post-and-categories:nth-child(odd){margin-top:0;margin-bottom:20px;min-width:0}
.products-page .shop-content .products-grid>.post-and-categories:nth-child(even){margin-top:20px;margin-bottom:0;min-width:0}
@media(max-width:1000px){.products-page .az-shop-filters{padding:36px 28px}}
@media(max-width:900px){.products-page .shop-content .products-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.products-page .az-shop-filters{padding:28px 20px}.az-shop-filters .shop-filter-form{grid-template-columns:1fr}.az-shop-filter-field input,.az-shop-filters .filter-select-trigger{font-size:16px}.az-shop-filter-actions{flex-direction:column}.az-shop-filter-actions .filter-btn{width:100%;min-width:0}.products-page .shop-content .products-grid{grid-template-columns:1fr}}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('page-styles'); ?>
<style>
.az-shop-filters>summary{cursor:pointer;list-style:none;user-select:none}
.az-shop-filters>summary::-webkit-details-marker{display:none}
.az-shop-filters>summary:focus-visible{outline:2px solid #090b19;outline-offset:6px}
.az-shop-filters>summary h2{display:inline-flex;align-items:center;gap:10px}
.az-shop-filter-chevron{margin-left:auto;color:#182132;transition:transform .2s ease}
.az-shop-filters[open] .az-shop-filter-chevron{transform:rotate(180deg)}
.az-shop-filters:not([open])>.az-shop-filter-heading{margin-bottom:0;padding-bottom:0;border-bottom:0}
.products-page details.az-shop-filters:not([open]){padding:20px 28px}
@media(max-width:600px){.products-page details.az-shop-filters:not([open]){padding:20px}.az-shop-filters>summary p{display:none}}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('page-scripts'); ?>
<script>
(function () {
    const box = document.getElementById('shop-filter-box');
    const form = document.getElementById('shop-filter-form');
    if (!box || !form) return;
    const summary = box.querySelector('summary');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let expanded = false;
    let animation = null;
    let contentAnimation = null;
    box.classList.add('has-smooth-filter');
    function cancelAnimations() {
        if (animation) animation.cancel();
        if (contentAnimation) contentAnimation.cancel();
        animation = contentAnimation = null;
    }
    function closeInstantly() {
        expanded = false;
        cancelAnimations();
        box.removeAttribute('open');
        box.classList.remove('is-animating', 'is-closing');
        summary.setAttribute('aria-expanded', 'false');
    }
    function toggle() {
        const start = box.getBoundingClientRect().height;
        expanded = !expanded;
        cancelAnimations();
        summary.setAttribute('aria-expanded', String(expanded));
        if (reduced.matches || typeof box.animate !== 'function') {
            box.open = expanded;
            box.classList.remove('is-animating', 'is-closing');
            return;
        }
        box.open = true;
        const styles = getComputedStyle(box);
        const closedHeight = summary.getBoundingClientRect().height
            + parseFloat(styles.paddingTop) + parseFloat(styles.paddingBottom)
            + parseFloat(styles.borderTopWidth) + parseFloat(styles.borderBottomWidth);
        const end = expanded ? box.getBoundingClientRect().height : closedHeight;
        box.classList.add('is-animating');
        box.classList.toggle('is-closing', !expanded);
        const current = box.animate([{ height: start + 'px' }, { height: end + 'px' }], {
            duration: 360, easing: 'cubic-bezier(.22,1,.36,1)'
        });
        animation = current;
        contentAnimation = form.animate(expanded
            ? [{ opacity: 0, transform: 'translateY(-8px)' }, { opacity: 1, transform: 'translateY(0)' }]
            : [{ opacity: 1 }, { opacity: 0 }],
            { duration: expanded ? 280 : 160, easing: 'ease-out', fill: 'both' });
        current.onfinish = function () {
            if (animation !== current) return;
            box.open = expanded;
            box.classList.remove('is-animating', 'is-closing');
            cancelAnimations();
        };
    }
    summary.addEventListener('click', function (event) { event.preventDefault(); toggle(); });
    form.addEventListener('submit', closeInstantly);
    window.addEventListener('pageshow', closeInstantly);
    closeInstantly();
})();
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('page-styles'); ?>
<style>
.products-page details.az-shop-filters.has-smooth-filter{padding:24px 28px}
.has-smooth-filter>.az-shop-filter-heading{margin-bottom:0;padding-bottom:0;border-bottom:0}
.has-smooth-filter .shop-filter-form{margin-top:28px;padding-top:28px;border-top:1px solid #d5d9e2}
.has-smooth-filter.is-animating{overflow:hidden}
.has-smooth-filter .az-shop-filter-chevron{transition:transform .36s cubic-bezier(.22,1,.36,1)}
.has-smooth-filter.is-closing .az-shop-filter-chevron{transform:rotate(0)}
.az-shop-filter-heading h2{transition:background-color .2s ease,box-shadow .2s ease}
.az-shop-filter-heading:hover h2{background:#283752;box-shadow:0 4px 12px #17203318}
.az-shop-filter-field input,.az-shop-filters .filter-select-trigger{transition:border-color .2s ease,background-color .2s ease,box-shadow .2s ease}
.az-shop-filter-field input:focus,.az-shop-filters .filter-select-trigger:focus-visible{box-shadow:0 0 0 3px #1720330a}
.az-shop-filter-actions .filter-btn{transition:background-color .2s ease,color .2s ease,box-shadow .2s ease}
.az-shop-filter-actions .filter-btn:hover{box-shadow:0 4px 12px #17203312}
.products-page .products-grid>.post-and-categories{transition:transform .25s ease,box-shadow .25s ease}
@media(hover:hover){.products-page .products-grid>.post-and-categories:hover{transform:translateY(-3px)}}
@media(max-width:600px){.products-page details.az-shop-filters.has-smooth-filter{padding:20px}}
@media(prefers-reduced-motion:reduce){.has-smooth-filter *, .products-page .products-grid>.post-and-categories{transition:none!important;animation:none!important}.products-page .products-grid>.post-and-categories:hover{transform:none}}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('page-styles'); ?>
<style>
.az-category-intro{padding:28px 0 36px;color:#172033}.az-category-intro>span{font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#64748b}.az-category-intro h1{font-size:clamp(30px,4vw,48px);line-height:1.15;margin:12px 0}.az-category-intro p{max-width:760px;font-size:15px;line-height:1.8;color:#64748b;margin:0}.az-category-children{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px}.az-category-children a{display:inline-flex;align-items:center;gap:10px;padding:10px 16px;border:1px solid #dbe2ed;border-radius:999px;background:#f3f6fc;color:#172033;text-decoration:none;font-size:12px;transition:background .2s ease}.az-category-children a:hover{background:#e2e7f1}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/products/index.blade.php ENDPATH**/ ?>