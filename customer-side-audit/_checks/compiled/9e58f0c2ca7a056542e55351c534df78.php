

<?php $__env->startSection('title', 'Blogs'); ?>
<?php $__env->startSection('meta_description', 'Explore the latest articles, stories, ideas, and insights from Arizona Outfits.'); ?>


<?php $__env->startPush('page-styles'); ?>
<style>
/* Match the contact form: pale panel, square fields, spaced labels, pill buttons. */
.az-blog-filters{position:relative;z-index:11;padding:64px 5vw 0;background:#fff;scroll-margin-top:120px}
.az-blog-filter-box{padding:64px 60px;background:var(--pin-stripe,#f3f6fc);border-top:1px solid var(--dark-outline,#e2e7f1);color:var(--dark,#090b19)}
.az-blog-filter-heading{display:flex;align-items:center;gap:20px;flex-wrap:wrap;padding-bottom:18px;margin-bottom:48px;border-bottom:1px solid #d5d9e2}
.az-blog-filter-heading h2{display:inline-flex;align-items:center;min-height:40px;padding:11px 20px;margin:0;background:#182132;color:#fff;border-radius:100px;font-size:12px;font-weight:600;line-height:1.5;letter-spacing:3px;text-transform:uppercase}
.az-blog-filter-heading p{margin:0;color:var(--body-display,#6e7488);font-size:14px;line-height:1.6}
.az-blog-filter-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px 12px}
.az-blog-filter-field{min-width:0}
.az-blog-filter-field label{display:block;margin-bottom:12px;color:var(--dark,#090b19);font-size:12px;font-weight:400;letter-spacing:4px;text-transform:uppercase;line-height:1.6}
.az-blog-filter-field input,.az-blog-filter-field select{width:100%;min-width:0;min-height:54px;height:54px;padding:8px 12px;border:1px solid var(--dark-outline,#e2e7f1);border-radius:2px;background:#ffffffa6;color:#333;font-size:14px;line-height:1.42857;box-sizing:border-box;transition:border-color .25s ease}
.az-blog-filter-field input:focus,.az-blog-filter-field select:focus{outline:2px solid #090b19;outline-offset:2px}
.az-blog-filter-field input::placeholder{color:var(--body-display,#6e7488)}
.az-blog-filter-field select{padding-right:25px;text-overflow:ellipsis}
.az-blog-filter-bottom{grid-column:1/-1;display:flex;flex-direction:column;align-items:flex-start;gap:24px}
.az-blog-filter-check{display:flex;align-items:center;gap:10px;font-size:13px;color:var(--body-display,#6e7488);cursor:pointer;line-height:1.6}
.az-blog-filter-check input{width:16px;height:16px;accent-color:#090b19;flex-shrink:0}
.az-blog-filter-actions{display:flex;gap:12px;flex-wrap:wrap}
.az-blog-filter-submit,.az-blog-filter-clear{display:inline-flex;align-items:center;justify-content:center;min-height:46px;min-width:160px;padding:14px 24px;border:1px solid #090b19;border-radius:100px;font-size:11px;font-weight:500;letter-spacing:3px;text-transform:uppercase;line-height:1.5;text-decoration:none;cursor:pointer;position:relative;overflow:hidden;transition:background-color .3s ease,color .3s ease}
.az-blog-filter-submit{background:#090b19;color:#fff}
.az-blog-filter-clear{background:transparent;color:#090b19;border-color:#c6cbd5}
.az-blog-filter-submit:hover{background:#e2e7f1;border-color:#e2e7f1;color:#090b19}
.az-blog-filter-clear:hover{background:#e2e7f1;border-color:#c6cbd5;color:#090b19}
.az-blog-filter-submit .button-text,.az-blog-filter-clear .button-text{display:block;pointer-events:none;transition:transform .25s ease;transform:translate3d(0,0,0) scale(1)}
.az-blog-filter-submit:focus-visible,.az-blog-filter-clear:focus-visible,.az-blog-filter-chip:focus-visible{outline:2px solid #090b19;outline-offset:3px}
.az-blog-filter-error{margin:0 0 24px;padding:14px 16px;border:1px solid #f3c7c4;border-radius:2px;background:#fff5f4;color:#9d2923;font-size:13px;line-height:1.6}
.az-blog-filter-error ul{margin:6px 0 0 18px}
.az-blog-active-filters{display:flex;flex-wrap:wrap;gap:8px;margin-top:24px;padding-top:20px;border-top:1px solid #d5d9e2}
.az-blog-filter-chip{display:inline-flex;align-items:center;gap:10px;max-width:100%;padding:8px 12px;border:1px solid #c6cbd5;border-radius:100px;background:transparent;color:#475064;font-size:12px;text-decoration:none;overflow-wrap:anywhere}
.az-blog-filter-chip:hover{background:#e2e7f1}
.az-blog-results{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:25px;color:#6e7488;font-size:13px;line-height:1.6}
.az-blog-results strong{color:#090b19}
#blog-posts.az-filtered-posts{padding-top:44px}
.az-blog-empty{grid-column:1/-1;min-height:190px;padding:40px 20px;text-align:center;border:1px solid #e2e7f1;background:#f3f6fc}
.az-blog-empty h3{margin-bottom:10px;font-size:22px;color:#090b19}
.az-blog-empty p{margin-bottom:20px;color:#6e7488;font-size:14px;line-height:1.6}
@media(max-width:900px){.az-blog-filter-box{padding:40px 32px}}
@media(max-width:600px){.az-blog-filters{padding-top:36px;scroll-margin-top:90px}.az-blog-filter-box{padding:32px 20px}.az-blog-filter-heading{gap:14px;margin-bottom:28px}.az-blog-filter-form{grid-template-columns:minmax(0,1fr)}.az-blog-filter-field input,.az-blog-filter-field select{font-size:16px}.az-blog-filter-actions{width:100%}.az-blog-filter-submit,.az-blog-filter-clear{width:100%;min-width:0}.az-blog-filter-field label{letter-spacing:3px}}
@media(prefers-reduced-motion:reduce){.az-blog-filter-submit .button-text,.az-blog-filter-clear .button-text{transform:none!important;transition:none}}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

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

    <div class="home-hero">
        <div class="stripe-wrapper">
            <div class="wrapper">
                <div class="stripe-container">
                    <div class="pin-stripe white"></div>
                    <div class="pin-stripe white"></div>
                    <div class="pin-stripe white"></div>
                    <div class="pin-stripe white"></div>
                </div>
            </div>
        </div>

        <div class="background-cover">
            <div class="background-hero">
                <div class="background-overlay"></div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="content-1 content">
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">Blogs</p>
            </div>

            <div class="content-2 content">
                <a href="#blog-posts" class="moving-circle" aria-label="Explore blog posts">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>

            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">Ideas Beyond <br> Every Boundary</h1>

                <a href="<?php echo e(route('categories-page')); ?>"
                   class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">Explore Categories</div>
                </a>
            </div>

            <div class="content-4 content"></div>

            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">
                        Explore Every Human Thought
                    </div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>
        </div>
    </div>

    <section class="az-blog-filters" id="article-filters" aria-labelledby="article-filter-title">
        <div class="wrapper">
            <details class="az-blog-filter-box" id="article-filter-box">
                <summary class="az-blog-filter-heading" aria-expanded="false">
                    <h2 id="article-filter-title">Filter articles <i class="fa-solid fa-chevron-down az-shop-filter-chevron"></i></h2>
                    <p>Search by topic, category, author, or publication date.</p>
                </summary>

                <?php if($filterErrors): ?>
                    <div class="az-blog-filter-error" role="alert">
                        <strong>Please check your filters.</strong>
                        <ul>
                            <?php $__currentLoopData = $filterErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $messages): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($message); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="GET" action="<?php echo e(route('blogs-page')); ?>#article-filters" id="article-filter-form" class="az-blog-filter-form" role="search" aria-label="Search and filter articles">
                    <div class="az-blog-filter-field">
                        <label for="article-search">Search articles</label>
                        <input type="search" id="article-search" name="q" maxlength="120"
                            value="<?php echo e($filters['q']); ?>" placeholder="Title, topic, or keywords">
                    </div>
                    <div class="az-blog-filter-field">
                        <label for="article-category">Category</label>
                        <select id="article-category" name="category_id">
                            <option value="">All categories</option>
                            <?php $__currentLoopData = $filterCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filterCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($filterCategory['id']); ?>" <?php if((string) $filters['category_id'] === (string) $filterCategory['id']): echo 'selected'; endif; ?>><?php echo e($filterCategory['label']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="az-blog-filter-field">
                        <label for="article-author">Author</label>
                        <select id="article-author" name="author_id">
                            <option value="">All authors</option>
                            <?php $__currentLoopData = $filterAuthors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filterAuthor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($filterAuthor->id); ?>" <?php if((string) $filters['author_id'] === (string) $filterAuthor->id): echo 'selected'; endif; ?>><?php echo e($filterAuthor->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="az-blog-filter-field">
                        <label for="article-from">Published from</label>
                        <input type="date" id="article-from" name="from" value="<?php echo e($filters['from']); ?>">
                    </div>
                    <div class="az-blog-filter-field">
                        <label for="article-to">Published until</label>
                        <input type="date" id="article-to" name="to" value="<?php echo e($filters['to']); ?>">
                    </div>
                    <div class="az-blog-filter-field">
                        <label for="article-sort">Sort articles</label>
                        <select id="article-sort" name="sort">
                            <?php $__currentLoopData = $sortOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sortKey => $sortLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sortKey); ?>" <?php if($filters['sort'] === $sortKey): echo 'selected'; endif; ?>><?php echo e($sortLabel); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="az-blog-filter-bottom">
                        <input type="hidden" name="include_children" value="0">
                        <label class="az-blog-filter-check" for="article-subcategories">
                            <input type="checkbox" id="article-subcategories" name="include_children" value="1" <?php if((bool) $filters['include_children']): echo 'checked'; endif; ?>>
                            Include subcategories
                        </label>
                        <div class="az-blog-filter-actions">
                            <button type="submit" class="btn-style-2 az-blog-filter-submit"><span class="button-text">Find articles</span></button>
                            <a class="btn-style-2 az-blog-filter-clear" href="<?php echo e(route('blogs-page')); ?>#article-filters"><span class="button-text">Clear filters</span></a>
                        </div>
                    </div>
                </form>

                <?php if($activeFilters): ?>
                    <div class="az-blog-active-filters" aria-label="Applied filters">
                        <?php $__currentLoopData = $activeFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activeFilter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="az-blog-filter-chip" href="<?php echo e($activeFilter['url']); ?>" aria-label="<?php echo e('Remove filter: ' . $activeFilter['label']); ?>">
                                <?php echo e($activeFilter['label']); ?> <span aria-hidden="true">&times;</span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </details>
        </div>
    </section>

    <div class="blog-posts az-filtered-posts" id="blog-posts">
        <div class="wrapper">
            <div class="az-blog-results" role="status">
                <span>
                    <?php if($posts->count()): ?>
                        Showing <strong><?php echo e($posts->firstItem()); ?>-<?php echo e($posts->lastItem()); ?></strong> of <strong><?php echo e($posts->total()); ?></strong> <?php echo e($posts->total() === 1 ? 'article' : 'articles'); ?>

                    <?php else: ?>
                        <strong><?php echo e($posts->total()); ?></strong> <?php echo e($posts->total() === 1 ? 'article' : 'articles'); ?> found
                    <?php endif; ?>
                </span>
                <span><?php echo e($sortOptions[$filters['sort']]); ?></span>
            </div>
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php echo $__env->make('partials.post-card', ['post' => $post], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="az-blog-empty">
                                <h3><?php echo e($filterErrors ? 'Check your filters' : 'No articles found'); ?></h3>
                                <p><?php echo e($activeFilters || $filterErrors ? 'Try another keyword, category, author, or date range.' : 'New articles will appear here when they are published.'); ?></p>
                                <?php if($activeFilters || $filterErrors): ?>
                                    <a class="btn-style-2 az-blog-filter-clear" href="<?php echo e(route('blogs-page')); ?>#article-filters"><span class="button-text">View all articles</span></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if(method_exists($posts, 'links') && $posts->hasPages()): ?>
                <div class="blog-pagination">
                    <?php echo e($posts->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('page-scripts'); ?><script>
(function () {
    const box = document.getElementById('article-filter-box');
    const form = document.getElementById('article-filter-form');
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
</script><?php $__env->stopPush(); ?>
<?php $__env->startPush('page-styles'); ?><style>
.az-blog-filter-box.has-smooth-filter{padding:24px 28px;background:#f3f6fc}.az-blog-filter-box>summary{cursor:pointer;list-style:none}.az-blog-filter-box>summary::-webkit-details-marker{display:none}.az-blog-filter-box>summary h2{display:inline-flex;align-items:center;gap:16px;border-radius:30px;background:#172033;color:white;padding:12px 20px;font-size:13px;letter-spacing:2px;text-transform:uppercase}.az-blog-filter-box>summary:focus-visible{outline:2px solid #172033;outline-offset:4px}.az-blog-filter-form{margin-top:28px;padding-top:28px;border-top:1px solid #d5d9e2}.az-blog-filter-box.is-animating{overflow:hidden}.az-shop-filter-chevron{transition:transform .36s ease}.az-blog-filter-box[open] .az-shop-filter-chevron{transform:rotate(180deg)}.az-blog-filter-box.is-closing .az-shop-filter-chevron{transform:rotate(0)}.az-blog-filter-field input,.az-blog-filter-field select{min-height:54px;border-radius:0;transition:border-color .2s,box-shadow .2s}.az-blog-filter-field label{letter-spacing:3px;text-transform:uppercase}.az-blog-filter-submit,.az-blog-filter-clear{border-radius:999px;transition:background .2s,box-shadow .2s}@media(prefers-reduced-motion:reduce){.az-blog-filter-box *{transition:none!important;animation:none!important}}</style><?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\storefront-sync-files\_staged\resources\views/blogs/index.blade.php ENDPATH**/ ?>