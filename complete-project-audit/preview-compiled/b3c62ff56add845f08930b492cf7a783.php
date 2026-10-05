<?php $__env->startSection('title', 'Blog Categories'); ?>
<?php $__env->startSection('meta_description', 'Explore Arizona Outfits blog categories and discover articles, ideas, stories, and insights across every topic.'); ?>

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
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">Blog Categories</p>
            </div>

            <div class="content-2 content">
                <a href="#blog-categories" class="moving-circle" aria-label="Explore blog categories">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>

            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">Explore Stories <br> By Category</h1>

                <a href="<?php echo e(route('blogs-page')); ?>"
                   class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">View All Posts</div>
                </a>
            </div>

            <div class="content-4 content"></div>

            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">
                        Discover Every Perspective
                    </div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="blog-posts" id="blog-categories">
        <div class="wrapper">
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php echo $__env->make('partials.post-card', ['category' => $category], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-color-body">
                                No blog categories are available yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/categories/index.blade.php ENDPATH**/ ?>