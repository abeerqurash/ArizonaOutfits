<div class="categories-list">
    <div class="list-wrapper">

        <div class="list-item">
            <div class="list-item-inner-2 fs-14 text-color-dark text-uppercase letter-space-4px">
                More from Arizona Outfits
            </div>
        </div>

        <div class="related-posts">

            <?php $__empty_1 = true; $__currentLoopData = $latestPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $latest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

            <div class="related-item">

                <a href="<?php echo e(route('blog-show', $latest->slug)); ?>"
                    class="list-item menu-item fs-16 text-color-body">

                    <div class="list-item-text">
                        <?php echo e($latest->title); ?>

                    </div>

                    <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                </a>

            </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

            <div class="related-item">
                <div class="list-item-text">
                    No posts found.
                </div>
            </div>

            <?php endif; ?>

        </div>

    </div>
</div>