<?php
    $heroImage = $post->feature_image_url;
    $heroDescription = $post->meta_description ?: $post->excerpt;
    $publishedDate = $post->status === \App\Models\Post::STATUS_SCHEDULED
        ? $post->scheduled_at
        : $post->published_at;
    $publishedDate = $publishedDate ?: $post->created_at;
?>

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
        <div class="background-hero" <?php if($heroImage): ?> style="background-image: url('<?php echo e($heroImage); ?>');" <?php endif; ?>>
            <div class="background-overlay"></div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="content-1 content">
            <?php if($post->categories->isNotEmpty()): ?>
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">
                    Categories:
                    <?php $__currentLoopData = $post->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('category-show', $category->slug)); ?>"
                           class="fs-12 text-color-white text-uppercase letter-space-4px text-decoration-none">
                            <?php echo e($category->title); ?>

                        </a><?php if(!$loop->last): ?>, <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="content-2 content">
            <a href="#brands" class="moving-circle">
                <i class="fa-solid fa-arrow-down-long"></i>
            </a>
        </div>

        <div class="content-3 content">
            <h1 class="fs-78 text-color-white"><?php echo e($post->title); ?></h1>

            <?php if($heroDescription): ?>
                <p class="fs-12 text-color-white text-uppercase letter-space-4px text-decoration-none">
                    <?php echo e($heroDescription); ?>

                </p>
            <?php endif; ?>

            <a href="tel:923123743890" class="btn-style-1 fs-12 text-color-white justify-self-start">
                <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
            </a>
        </div>

        <div class="content-4 content"></div>

        <div class="home content">
            <div class="header-subtitle">
                <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">
                    <?php if($publishedDate): ?>
                        <p>Published: <?php echo e($publishedDate->format('M d, Y')); ?></p>
                    <?php endif; ?>
                </div>
                <div class="horizontal-line white"></div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/blogs/partials/article-hero.blade.php ENDPATH**/ ?>