<?php $__env->startSection('title', 'Edit Post'); ?>
<?php $__env->startSection('page-heading', 'Posts'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('admin.posts._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php $__currentLoopData = $post->revisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $revision): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <form id="restore-revision-<?php echo e($revision->id); ?>" method="POST"
            action="<?php echo e(route('admin.posts.revisions.restore', ['post' => $post, 'revision' => $revision])); ?>"
            onsubmit="return confirm('Restore this saved revision? Your current saved version will be kept in revision history. Unsaved edits will be discarded.');">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>
        </form>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/posts/edit.blade.php ENDPATH**/ ?>