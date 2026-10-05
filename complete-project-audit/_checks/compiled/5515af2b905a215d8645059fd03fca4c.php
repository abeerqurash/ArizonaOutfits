<?php $__env->startSection('title',$page->meta_title?:$page->title); ?>
<?php $__env->startSection('meta_description',$page->meta_description?:$page->excerpt); ?>
<?php $__env->startSection('content'); ?>

<?php
    $posts = \App\Models\Post::published()->with(['categories','primaryCategory','author'])->latestPosts()->take(6)->get();
    $latestPosts = $posts;
    $popularCategories = \App\Models\Category::withCount(['posts as published_posts_count'=>fn($query)=>$query->published()])->whereHas('posts',fn($query)=>$query->published())->orderByDesc('published_posts_count')->orderBy('title')->take(10)->get();
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
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">Dive Deep, Think Bigger</p>
            </div>
            <div class="content-2 content">
                <a href="#services" class="moving-circle">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>
            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">Explore Everything <br> Around You</h1>
                <a href="/blogs" class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">Browse the World</div>
                </a>
            </div>
            <div class="content-4 content">

            </div>
            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">Welcome</div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>


        </div>
    </div>
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
    <div class="blog-posts service-content individual-page">
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
                <div class="post-categories">
                    <div class="categories-and-title">
                        <?php echo $__env->make('partials.blog-author-card-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                      <?php echo $__env->make('partials.latest-posts', ['latestPosts'=>\App\Models\Post::published()->latestPosts()->take(6)->get()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo $__env->make('blogs.partials.article-reviews', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="blog-posts">
        <div class="wrapper">
            <div class="heading-grid">
                <div class="intro">
                    <div class="subtitle">
                        <span class="fs-12 letter-space-4px text-uppercase text-color-dark">Recent Posts</span>
                    </div>
                    <div class="title">
                        <h1 class="fs-48 text-color-dark">What's New</h1>
                    </div>
                </div>
                <div class="button">
                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                        <div class="button-text text-uppercase letter-space-3px">View All Posts</div>
                    </a>
                </div>
            </div>
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('partials.post-card', ['post' => $post], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="post-categories">
                    <div class="categories-and-title">
                        <div class="list-heading">
                            <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-dark">Popular Categories</div>
                        </div>
                         <div class="categories-list">
                            <div class="list-wrapper">

                                <?php $__currentLoopData = $popularCategories->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <div class="list-item">
                                    <a href="<?php echo e(url('/category/' . $category->slug)); ?>"
                                        class="menu-item fs-18-400 text-color-body">

                                        <div class="list-item-text">
                                            <?php echo e($category->title); ?>

                                        </div>

                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\pages\custom\privacy-policy.blade.php ENDPATH**/ ?>