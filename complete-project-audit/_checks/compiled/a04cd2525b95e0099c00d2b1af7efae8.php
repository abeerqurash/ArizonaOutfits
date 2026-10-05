<h1><?php echo e($post->title); ?></h1>

<img src="<?php echo e(asset('storage/'.$post->feature_image)); ?>" alt="<?php echo e($post->title); ?>">

<p><?php echo app(\App\Services\HtmlContentSanitizer::class)->clean($post->content ?? ''); ?></p>

<hr>

<h3>Related Posts</h3>

<?php $__currentLoopData = $relatedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<div>
    <a href="<?php echo e(route('blog-show', $related->slug)); ?>">
        <?php echo e($related->title); ?>

    </a>
</div>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../replacement-files/_staged/resources/views\blogs\show.blade.php ENDPATH**/ ?>