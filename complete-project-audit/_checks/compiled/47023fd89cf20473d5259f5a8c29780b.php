<div class="categories-list">
    <div class="list-wrapper">

        <div class="list-item">
            <div class="list-item-inner-2 fs-14 text-color-dark text-uppercase letter-space-4px">
                More from IDEOSTREAM
            </div>
        </div>

        <div class="related-posts">

            <?php $__empty_1 = true; $__currentLoopData = $relatedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                <div class="related-item">


                    <a href="<?php echo e(route('blog-show', $related->slug)); ?>"
                        class="list-item menu-item fs-16 text-color-body">

                        <div class="list-item-text">
                            <?php echo e($related->title); ?>

                        </div>

                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                    </a>


                </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                <div class="related-item">
                    <div class="list-item-text">
                        No related posts found.
                    </div>
                </div>

            <?php endif; ?>

        </div>

    </div>
</div><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\blogs\partials\related-posts.blade.php ENDPATH**/ ?>