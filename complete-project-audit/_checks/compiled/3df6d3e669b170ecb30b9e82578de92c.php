



<?php $__env->startSection('title', 'Arizona Blog System Test'); ?> 

<?php $__env->startSection('meta_description', 'Welcome to our homepage'); ?>



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

    <?php echo $__env->make('blogs.partials.article-hero', ['post' => $post], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="scrolling-text-section no-vertical-padding">

        <div class="wrapper">

            <div class="text-info-page">

                <div class="page-info">

                    <div class="banner-item">

                        <div class="scrolling-item">

                            <div class="scrolling-text">

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                                <div class="subtitle">Start The Conversation</div>

                                <div class="dark-dot"></div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="blog-posts service-content">

        <div class="wrapper">

            <div class="post-and-categories blog-single-post">

                <div class="post-cards-parent background-color-pinstrip border-top-dark">

                    <div class="parent-wrapper">

                        <div class="content">

                            <h2 class="fs-32 text-color-dark">Lorem, ipsum dolor sit amet consectetur adipisicing.</h2>

                            <p class="fs-18-400 text-color-body">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae quae corporis aut eligendi distinctio maiores, atque modi id veniam aperiam.</p>

                            <h3 class="fs-24 text-color-dark">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Provident, molestias.</h3>

                            <div class="special-para-text fs-18-400 text-color-body background-color-white">

                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Optio fugit eos voluptatum ex minus ipsum nulla aliquam dolorum iusto quisquam.

                            </div>

                            <p class="fs-18-400 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. At, maxime error. Iure quia autem libero! Accusamus esse est dignissimos saepe?</p>

                            <p class="fs-18-400 text-color-body">

                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Quidem in eos architecto qui neque vero iusto delectus illum rerum ad esse voluptatem aperiam harum, quaerat maiores accusantium voluptas dolore sapiente tempora dolorum aliquam incidunt consequatur, voluptatibus alias. Necessitatibus, ullam modi.

                            </p>

                            <h2 class="fs-32 text-color-dark">Lorem ipsum dolor sit amet consectetur adipisicing.</h2>

                            <p class="fs-18-400 text-color-body">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi eveniet soluta cumque quisquam. Nemo iusto, harum accusantium voluptatibus accusamus incidunt.</p>

                            <h3 class="fs-24 text-color-dark">Lorem ipsum dolor sit amet consectetur adipisicing.</h3>

                            <div class="special-para-text fs-18-400 text-color-body background-color-white">

                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Optio fugit eos voluptatum ex minus ipsum nulla aliquam dolorum iusto quisquam.

                            </div>

                            <p class="fs-18-400 text-color-body">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi eveniet soluta cumque quisquam. Nemo iusto, harum accusantium voluptatibus accusamus incidunt.</p>

                        </div>

                    </div>

                </div>

                <?php echo $__env->make('blogs.partials.article-author-latest', [

    'post' => $post,

    'latestPosts' => $latestPosts

], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            </div>

        </div>

    </div>

    <?php echo $__env->make('blogs.partials.article-reviews', [

    'reviewProducts' => $reviewProducts

], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="news-letter">

        <?php echo $__env->make('blogs.partials.blog-form-container', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </div>

    <div class="blog-posts service-content">

        <div class="wrapper">

            <div class="heading-grid">

                <div class="intro">

                    <div class="subtitle">

                        <span class="fs-12 letter-space-4px text-uppercase text-color-dark">Related Posts</span>

                    </div>

                    <div class="title">

                        <h2 class="fs-48 text-color-dark">Keep Learning</h2>

                    </div>

                </div>

                <div class="button">

                    <a href="<?php echo e(route('blogs-page')); ?>" class="btn-style-2 fs-12 text-color-white justify-self-start">

                        <div class="button-text text-uppercase letter-space-3px">View All Posts</div>

                    </a>

                </div>

            </div>

            <div class="post-and-categories">

                <div class="post-cards-parent">

                    <div class="parent-wrapper az-responsive-cards" data-card-carousel aria-label="Related articles">
<?php $__currentLoopData = $relatedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedPost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <?php echo $__env->make('partials.post-card', ['post' => $relatedPost], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

                </div>

                <div class="post-categories">

                    <div class="categories-and-title">

                        <div class="list-heading">

                            <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-dark">Popular Categories</div>

                        </div>

                        <div class="categories-list">

                            <div class="list-wrapper">

                                <?php $__empty_1 = true; $__currentLoopData = $popularCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $popularCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="list-item">
                                        <a href="<?php echo e(route('category-show', $popularCategory->slug)); ?>" class="menu-item fs-18-400 text-color-body">
                                            <div class="list-item-text"><?php echo e($popularCategory->title); ?></div>
                                            <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="list-item">
                                        <span class="menu-item fs-18-400 text-color-body">
                                            <div class="list-item-text">No categories available yet.</div>
                                        </span>
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../replacement-files/_staged/resources/views\blogs\posts\arizona-blog-system-test-3.blade.php ENDPATH**/ ?>