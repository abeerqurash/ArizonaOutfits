<?php
    $products = $reviewProducts ?? \App\Models\Product::where('status','active')->whereHas('approvedReviews')->with(['images','approvedReviews'=>fn($q)=>$q->latest()->limit(20)])->latest('id')->take(10)->get();

    $reviewShowcaseData = $products->map(function ($product) {
        $imagePath = optional($product->images->first())->image;

        $imageUrl=app(\App\Services\PublicMediaService::class)->url($imagePath ?: $product->featured_image);

        return [
            'id' => $product->id,
            'title' => $product->title,
            'url' => route('products.show', $product->slug),
            'image' => $imageUrl,
            'reviews' => $product->approvedReviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'name' => $review->name,
                    'rating' => max(1, min(5, (int) $review->rating)),
                    'title' => $review->title,
                    'review' => $review->review,
                    'date' => optional($review->created_at)->format('M d, Y'),
                ];
            })->values()->all(),
        ];
    })->values();

    $firstProduct = $reviewShowcaseData->first();
    $firstReviews = collect($firstProduct['reviews'] ?? [])->take(4);
?>

<?php if($reviewShowcaseData->isNotEmpty()): ?>
<div class="testiomonials arizona-product-reviews" data-product-review-showcase>
    <div class="wrapper">
        <div class="testimonial-wrapper">
            <div class="testimonial-slider">
                <div class="slider-mask">
                    <div class="testimonial-slide">
                        <div class="testimonial-slide-content">

                            <div class="testimonial-column testimonial-image arizona-review-product-column">
                                <a href="<?php echo e($firstProduct['url']); ?>"
                                   class="testimonial-background-image arizona-review-product-image"
                                   data-review-product-link
                                   aria-label="View <?php echo e($firstProduct['title']); ?>"
                                   style="background-image:url('<?php echo e($firstProduct['image']); ?>')">
                                    <div class="image-overlay"></div>

                                    <div class="arizona-product-caption">
                                        <span class="arizona-product-label">PRODUCT</span>
                                        <span class="arizona-product-title" data-review-product-title><?php echo e($firstProduct['title']); ?></span>
                                    </div>
                                </a>

                                <div class="arizona-product-rail">
                                    <button type="button"
                                            class="arizona-product-rail-button arizona-product-rail-next"
                                            data-product-next
                                            aria-label="Next product">
                                        <span>NEXT</span>
                                    </button>

                                    <button type="button"
                                            class="arizona-product-rail-button arizona-product-rail-prev"
                                            data-product-prev
                                            aria-label="Previous product">
                                        <span>PREVIOUS</span>
                                    </button>
                                </div>
                            </div>

                            <div class="testimonial-column testimonial-description arizona-review-panel">
                                <div class="arizona-review-panel-inner">
                                    <div class="arizona-review-heading">
                                        <div class="arizona-review-eyebrow">CUSTOMER REVIEWS</div>

                                        <div class="arizona-review-heading-row">
                                            <h2 data-review-heading>Reviews for <?php echo e($firstProduct['title']); ?></h2>
                                            <span data-review-page-indicator>
                                                <?php if(count($firstProduct['reviews'] ?? []) > 4): ?>
                                                    1 / <?php echo e((int) ceil(count($firstProduct['reviews']) / 4)); ?>

                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="arizona-review-grid" data-review-grid>
                                        <?php $__empty_1 = true; $__currentLoopData = $firstReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <article class="arizona-review-card">
                                                <div class="arizona-review-card-top">
                                                    <div class="arizona-review-stars" aria-label="<?php echo e($review['rating']); ?> out of 5 stars">
                                                        <?php for($star = 1; $star <= 5; $star++): ?>
                                                            <i class="<?php echo e($star <= $review['rating'] ? 'fa-solid' : 'fa-regular'); ?> fa-star"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="arizona-review-date"><?php echo e($review['date']); ?></span>
                                                </div>

                                                <?php if(!empty($review['name'])): ?>
                                                    <div class="arizona-review-name"><?php echo e($review['name']); ?></div>
                                                <?php endif; ?>
                                                <?php if(!empty($review['title'])): ?>
                                                    <h3><?php echo e($review['title']); ?></h3>
                                                <?php endif; ?>
                                                <p><?php echo e($review['review']); ?></p>
                                            </article>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <div class="arizona-review-empty">No approved reviews are available for this product.</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="arizona-review-navigation">
                                        <button type="button"
                                                class="arizona-review-nav-button"
                                                data-review-prev
                                                <?php if(count($firstProduct['reviews'] ?? []) <= 4): echo 'disabled'; endif; ?>>
                                            <span class="button-text"><i class="fa-solid fa-arrow-left-long"></i> Previous</span>
                                        </button>

                                        <button type="button"
                                                class="arizona-review-nav-button"
                                                data-review-next
                                                <?php if(count($firstProduct['reviews'] ?? []) <= 4): echo 'disabled'; endif; ?>>
                                            <span class="button-text">Next <i class="fa-solid fa-arrow-right-long"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.arizona-product-reviews .testimonial-slide-content {
    position: relative;
}

.arizona-product-reviews .arizona-review-product-column {
    position: relative;
    overflow: hidden;
}

.arizona-product-reviews .arizona-review-product-image {
    position: absolute;
    inset: 0;
    z-index: 1;
    display: block;
    width: 100%;
    height: 100%;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    text-decoration: none;
}

.arizona-product-reviews .arizona-product-rail {
    position: absolute !important;
    z-index: 50 !important;
    top: 0 !important;
    bottom: 0 !important;
    left: 0 !important;
    display: block !important;
    width: 66px !important;
    height: 100% !important;
    border-right: 1px solid rgba(255,255,255,.45);
    background: rgba(7,10,24,.42);
}

.arizona-product-reviews .arizona-product-rail-button {
    position: absolute !important;
    left: 0 !important;
    display: block !important;
    width: 100% !important;
    height: 50% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    color: #fff !important;
    cursor: pointer;
    opacity: 1 !important;
    visibility: visible !important;
}

.arizona-product-reviews .arizona-product-rail-next {
    top: 0 !important;
}

.arizona-product-reviews .arizona-product-rail-prev {
    bottom: 0 !important;
}

.arizona-product-reviews .arizona-product-rail-button span {
    position: absolute;
    top: 50%;
    left: 50%;
    display: block;
    white-space: nowrap;
    font-size: 13px;
    font-weight: 400;
    letter-spacing: 2px;
    line-height: 1;
    text-transform: uppercase;
    transform: translate(-50%, -50%) rotate(-90deg);
}

.arizona-product-reviews .arizona-product-rail-button:hover {
    background: rgba(255,255,255,.08) !important;
}

.arizona-product-reviews .arizona-product-caption {
    position: absolute;
    z-index: 3;
    right: 45px;
    bottom: 50px;
    left: 100px;
    display: grid;
    gap: 6px;
    color: #fff;
}

.arizona-product-reviews .arizona-product-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 4px;
}

.arizona-product-reviews .arizona-product-title {
    font-size: 20px;
    font-weight: 600;
    line-height: 1.25;
}

.arizona-product-reviews .arizona-review-panel {
    width: 66.666666%;
}

.arizona-product-reviews .arizona-review-panel-inner {
    display: flex;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
    flex-direction: column;
    justify-content: center;
    padding: 38px;
}

.arizona-product-reviews .arizona-review-heading {
    margin-bottom: 18px;
}

.arizona-product-reviews .arizona-review-eyebrow {
    margin-bottom: 8px;
    color: rgba(255,255,255,.6);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 4px;
}

.arizona-product-reviews .arizona-review-heading-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
}

.arizona-product-reviews .arizona-review-heading h2 {
    margin: 0;
    color: #fff;
    font-size: 22px;
    font-weight: 500;
    line-height: 1.25;
}

.arizona-product-reviews [data-review-page-indicator] {
    flex: 0 0 auto;
    color: rgba(255,255,255,.55);
    font-size: 10px;
    letter-spacing: 2px;
}

.arizona-product-reviews .arizona-review-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 12px;
}

.arizona-product-reviews .arizona-review-card {
    display: flex;
    min-width: 0;
    min-height: 145px;
    box-sizing: border-box;
    flex-direction: column;
    padding: 16px;
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.04);
}

.arizona-product-reviews .arizona-review-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 9px;
}

.arizona-product-reviews .arizona-review-stars {
    display: flex;
    gap: 2px;
    color: #fff;
    font-size: 10px;
}

.arizona-product-reviews .arizona-review-date {
    color: rgba(255,255,255,.5);
    font-size: 9px;
    white-space: nowrap;
}

.arizona-product-reviews .arizona-review-name {
    margin: 0 0 5px;
    color: rgba(255,255,255,.68);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.arizona-product-reviews .arizona-review-card h3 {
    margin: 0 0 7px;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.3;
}

.arizona-product-reviews .arizona-review-card p {
    display: -webkit-box;
    overflow: hidden;
    margin: 0;
    color: rgba(255,255,255,.75);
    font-size: 12px;
    line-height: 1.55;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 4;
}

.arizona-product-reviews .arizona-review-navigation {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 15px;
}

.arizona-product-reviews .arizona-review-nav-button {
    position: relative;
    display: inline-flex;
    min-width: 128px;
    min-height: 46px;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 0 22px;
    border: 1px solid #fff;
    border-radius: 999px;
    background: #fff;
    color: #080b1b;
    cursor: pointer;
    transition: background-color .3s ease, color .3s ease, border-color .3s ease;
}
.arizona-product-reviews .arizona-review-nav-button .button-text {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 2px;
    line-height: 1;
    text-transform: uppercase;
    pointer-events: none;
    transition: transform .18s ease-out;
    will-change: transform;
}
.arizona-product-reviews .arizona-review-nav-button:not(:disabled):hover {
    border-color: var(--dark-outline);
    background: var(--dark-outline);
    color: #000;
}
.arizona-product-reviews .arizona-review-nav-button:disabled {
    cursor: not-allowed;
    opacity: .3;
}

.arizona-product-reviews .arizona-review-empty {
    grid-column: 1 / -1;
    display: grid;
    min-height: 300px;
    place-items: center;
    border: 1px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.65);
    font-size: 12px;
    text-align: center;
}

@media(max-width:991px) {
    .arizona-product-reviews .arizona-review-panel-inner {
        padding: 26px;
    }

    .arizona-product-reviews .arizona-product-rail {
        width: 58px !important;
    }

    .arizona-product-reviews .arizona-product-caption {
        left: 80px;
    }
}

@media(max-width:767px) {
    .arizona-product-reviews .testimonial-slide-content {
        display: block;
    }

    .arizona-product-reviews .arizona-review-product-column,
    .arizona-product-reviews .arizona-review-panel {
        width: 100%;
    }

    .arizona-product-reviews .arizona-review-product-column {
        min-height: 430px;
    }

    .arizona-product-reviews .arizona-review-panel-inner {
        padding: 28px 20px;
    }
}

@media(max-width:520px) {
    .arizona-product-reviews .arizona-product-rail {
        width: 52px !important;
    }

    .arizona-product-reviews .arizona-product-rail-button span {
        font-size: 11px;
        letter-spacing: 1.5px;
    }

    .arizona-product-reviews .arizona-product-caption {
        right: 16px;
        left: 70px;
    }

    .arizona-product-reviews .arizona-review-grid {
        grid-template-columns: 1fr;
    }

    .arizona-product-reviews .arizona-review-heading-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 6px;
    }

    .arizona-product-reviews .arizona-review-card {
        min-height: 0;
    }

    .arizona-product-reviews .arizona-review-navigation {
        justify-content: space-between;
    }
}
</style>

<script>
(function () {
    'use strict';

    const showcase = document.currentScript.previousElementSibling &&
        document.currentScript.previousElementSibling.matches('style')
        ? document.currentScript.previousElementSibling.previousElementSibling
        : document.querySelector('[data-product-review-showcase]');

    if (!showcase || !showcase.matches('[data-product-review-showcase]')) {
        return;
    }

    const products = <?php echo json_encode(
        $reviewShowcaseData,
        JSON_UNESCAPED_SLASHES |
        JSON_UNESCAPED_UNICODE |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    ); ?>;

    if (!Array.isArray(products) || products.length === 0) {
        return;
    }

    const productImage = showcase.querySelector('[data-review-product-link]');
    const productTitle = showcase.querySelector('[data-review-product-title]');
    const reviewHeading = showcase.querySelector('[data-review-heading]');
    const reviewGrid = showcase.querySelector('[data-review-grid]');
    const pageIndicator = showcase.querySelector('[data-review-page-indicator]');
    const productPrev = showcase.querySelector('[data-product-prev]');
    const productNext = showcase.querySelector('[data-product-next]');
    const reviewPrev = showcase.querySelector('[data-review-prev]');
    const reviewNext = showcase.querySelector('[data-review-next]');

    if (!productImage || !reviewGrid || !productPrev || !productNext || !reviewPrev || !reviewNext) {
        return;
    }

    const reviewsPerPage = 4;
    let productIndex = 0;
    let reviewPage = 0;

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderReviews(product) {
        const reviews = Array.isArray(product.reviews) ? product.reviews : [];
        const totalPages = Math.max(1, Math.ceil(reviews.length / reviewsPerPage));

        if (reviewPage >= totalPages) {
            reviewPage = 0;
        }

        const start = reviewPage * reviewsPerPage;
        const visibleReviews = reviews.slice(start, start + reviewsPerPage);

        if (visibleReviews.length === 0) {
            reviewGrid.innerHTML =
                '<div class="arizona-review-empty">No approved reviews are available for this product.</div>';
        } else {
            reviewGrid.innerHTML = visibleReviews.map(function (review) {
                const rating = Math.max(1, Math.min(5, Number(review.rating) || 1));
                let stars = '';

                for (let star = 1; star <= 5; star += 1) {
                    stars += '<i class="' +
                        (star <= rating ? 'fa-solid' : 'fa-regular') +
                        ' fa-star"></i>';
                }

                const name = review.name
                    ? '<div class="arizona-review-name">' + escapeHtml(review.name) + '</div>'
                    : '';
                const title = review.title
                    ? '<h3>' + escapeHtml(review.title) + '</h3>'
                    : '';

                return '<article class="arizona-review-card">' +
                    '<div class="arizona-review-card-top">' +
                        '<div class="arizona-review-stars" aria-label="' +
                            rating + ' out of 5 stars">' +
                            stars +
                        '</div>' +
                        '<span class="arizona-review-date">' +
                            escapeHtml(review.date || '') +
                        '</span>' +
                    '</div>' +
                    name +
                    title +
                    '<p>' + escapeHtml(review.review || '') + '</p>' +
                '</article>';
            }).join('');
        }

        pageIndicator.textContent = reviews.length > reviewsPerPage
            ? (reviewPage + 1) + ' / ' + totalPages
            : '';

        reviewPrev.disabled = totalPages <= 1;
        reviewNext.disabled = totalPages <= 1;
    }

    function renderProduct() {
        const product = products[productIndex];

        productImage.href = product.url || '#';
        productImage.setAttribute(
            'aria-label',
            'View ' + (product.title || 'product')
        );

        productImage.style.backgroundImage = product.image
            ? 'url("' + String(product.image).replaceAll('"', '\\"') + '")'
            : 'none';

        productTitle.textContent = product.title || '';
        reviewHeading.textContent = 'Reviews for ' + (product.title || 'product');

        reviewPage = 0;
        renderReviews(product);
    }

    [reviewPrev, reviewNext].forEach(function (button) {
        const text = button.querySelector('.button-text');
        if (!text) return;

        button.addEventListener('mousemove', function (event) {
            const rect = button.getBoundingClientRect();
            const x = event.clientX - rect.left - rect.width / 2;
            const y = event.clientY - rect.top - rect.height / 2;
            text.style.transform =
                'translate3d(' + (x / 6) + 'px, ' + (y / 6) + 'px, 0) scale(1.12)';
        });

        button.addEventListener('mouseleave', function () {
            text.style.transform = 'translate3d(0, 0, 0) scale(1)';
        });
    });

    productNext.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        productIndex = (productIndex + 1) % products.length;
        renderProduct();
    });

    productPrev.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        productIndex = (productIndex - 1 + products.length) % products.length;
        renderProduct();
    });

    reviewNext.addEventListener('click', function () {
        const reviews = products[productIndex].reviews || [];
        const totalPages = Math.max(1, Math.ceil(reviews.length / reviewsPerPage));

        if (totalPages <= 1) {
            return;
        }

        reviewPage = (reviewPage + 1) % totalPages;
        renderReviews(products[productIndex]);
    });

    reviewPrev.addEventListener('click', function () {
        const reviews = products[productIndex].reviews || [];
        const totalPages = Math.max(1, Math.ceil(reviews.length / reviewsPerPage));

        if (totalPages <= 1) {
            return;
        }

        reviewPage = (reviewPage - 1 + totalPages) % totalPages;
        renderReviews(products[productIndex]);
    });
})();
</script>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\storefront-sync-files\_staged\resources\views/blogs/partials/article-reviews.blade.php ENDPATH**/ ?>