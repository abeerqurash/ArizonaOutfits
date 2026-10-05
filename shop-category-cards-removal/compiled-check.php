

<?php $__env->startSection('title', 'Products'); ?>

<?php $__env->startSection(
'meta_description',
'Browse products, compare prices, explore categories and find the right product for your needs.'
); ?>

<?php $__env->startSection('content'); ?>

<?php
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

                        <li aria-current="page">
                            Products
                        </li>

                    </ul>





                    <div class="shop-layout">

                        <aside class="shop-sidebar">


                            <form
                                method="GET"
                                action="<?php echo e(route('products.index')); ?>"
                                class="shop-filter-form"
                                id="shop-filter-form">
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Search products"
                                    value="<?php echo e(request('search')); ?>"
                                    class="input-type-field mb-10px fs-12">

                                
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

                                
                                <h4 class="fs-24 text-color-dark mb-10px text-capitalize">
                                    Price
                                </h4>

                                <div class="d-flex gap-10px mb-10px">
                                    <input
                                        type="number"
                                        name="min_price"
                                        placeholder="Min"
                                        value="<?php echo e(request('min_price')); ?>"
                                        min="0"
                                        step="0.01"
                                        class="input-type-field fs-12">

                                    <input
                                        type="number"
                                        name="max_price"
                                        placeholder="Max"
                                        value="<?php echo e(request('max_price')); ?>"
                                        min="0"
                                        step="0.01"
                                        class="input-type-field fs-12">
                                </div>

                                <?php if(request('sort')): ?>
                                <input
                                    type="hidden"
                                    name="sort"
                                    value="<?php echo e(request('sort')); ?>">
                                <?php endif; ?>

                                <div class="d-flex gap-10px">
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
                                        href="<?php echo e(route('products.index')); ?>"
                                        class="filter-btn text-decoration-none btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Reset</div>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>

                        </aside>

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
                                    action="<?php echo e(route('products.index')); ?>"
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>