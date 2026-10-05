
<?php $__env->startSection('title',$page->meta_title?:$page->title); ?>
<?php $__env->startSection('meta_description',$page->meta_description?:$page->excerpt); ?>
<?php $__env->startSection('content'); ?>
<section class="custom-cms-page"><div class="custom-page-container"><span>Arizona Outfits</span><h1><?php echo e($page->title); ?></h1><?php if($page->excerpt): ?><p><?php echo e($page->excerpt); ?></p><?php endif; ?><div class="custom-page-card"><h2>Your custom Blade content starts here</h2><p>Edit this file manually. Dashboard publishing, URL and SEO settings will continue working.</p></div></div></section>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('page-styles'); ?><style>.custom-cms-page{min-height:70vh;padding:170px 20px 90px;background:#f8fafc;color:#172033}.custom-page-container{width:min(1100px,100%);margin:auto}.custom-page-container>span{color:#0f766e;font-weight:800;text-transform:uppercase}.custom-page-container>h1{margin:10px 0;font-size:clamp(40px,8vw,82px)}.custom-page-container>p{max-width:700px;color:#64748b;font-size:18px;line-height:1.7}.custom-page-card{margin-top:45px;padding:28px;border-radius:18px;background:#fff;box-shadow:0 20px 60px rgba(15,23,42,.08)}</style><?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\pages\custom\example.blade.php ENDPATH**/ ?>