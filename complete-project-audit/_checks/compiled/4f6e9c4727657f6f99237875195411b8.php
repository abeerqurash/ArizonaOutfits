<?php
$isCategory = isset($category) && !isset($post);
$item = $post ?? $category;

$backgroundImage = $isCategory
    ? ($item->image_url ?: asset('asset/media/hero.webp'))
    : ($item->feature_image_url ?: asset('asset/media/hero.webp'));

$itemUrl = $isCategory
    ? route('category-show', $item->slug)
    : route('blog-show', $item->slug);
?>

<div class="card-parent">
    <div class="card-image">
        <div class="background-image" style="background-image: url('<?php echo e(app(\App\Services\ResponsiveMediaService::class)->url($backgroundImage,640)); ?>');">
            <div class="image-overlay"></div>
            <div class="post-link">
                <a href="<?php echo e($itemUrl); ?>" class="moving-circle">
                    <?php echo e($isCategory ? 'View' : 'Read'); ?>

                </a>
            </div>
        </div>
    </div>

    <div class="card-information">
        <div class="card-heading-description">
            <h3>
                <a href="<?php echo e($itemUrl); ?>"
                   class="post-card-description heading fs-18 text-color-dark">
                    <?php echo e($item->title); ?>

                </a>
            </h3>

            <?php if($item->excerpt): ?>
                <p class="experts fs-16 text-color-body"><?php echo e($item->excerpt); ?></p>
            <?php endif; ?>
        </div>

        <?php if(!$isCategory && $item->categories->count()): ?>
            <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">
                <?php $__currentLoopData = $item->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryLink): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('category-show', $categoryLink->slug)); ?>"
                       class="text-decoration-none text-color-body cursor-pointer">
                        <?php echo e($categoryLink->title); ?>

                    </a><?php if(!$loop->last): ?>, <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if(!$isCategory): ?>
            <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">
                <?php echo e(($item->published_at ?: $item->scheduled_at ?: $item->created_at)->format('m.d.y')); ?>

            </div>
        <?php endif; ?>

        <div class="post-card-circle"></div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../replacement-files/_staged/resources/views\partials\post-card.blade.php ENDPATH**/ ?>