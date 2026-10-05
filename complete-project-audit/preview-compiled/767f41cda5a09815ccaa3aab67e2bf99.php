<?php $__env->startSection('title', $category->meta_title ?? $category->title); ?>
<?php $__env->startSection('meta_description', $category->meta_description ?? ('Explore the latest ' . $category->title . ' articles, stories, ideas, and insights from Arizona Outfits.')); ?>

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
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">
                    Blog Category
                </p>
            </div>

            <div class="content-2 content">
                <a href="#category-posts"
                   class="moving-circle"
                   aria-label="Explore <?php echo e($category->title); ?> articles">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>

            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">
                    <?php echo e($category->title); ?>

                </h1>

                <a href="<?php echo e(route('blogs-page')); ?>"
                   class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">
                        View All Posts
                    </div>
                </a>
            </div>

            <div class="content-4 content"></div>

            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">
                        Explore Articles &amp; Ideas
                    </div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="blog-posts" id="category-posts">
        <div class="wrapper">
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php echo $__env->make('partials.post-card', ['post' => $post], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-color-body">
                                No published articles are available in this category yet.
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/categories/show.blade.php ENDPATH**/ ?>