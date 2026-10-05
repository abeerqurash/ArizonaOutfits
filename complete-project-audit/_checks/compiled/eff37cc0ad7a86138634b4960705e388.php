<?php $__env->startSection('title','Edit Coupon'); ?>
<?php $__env->startSection('page-heading','Edit Coupon'); ?>
<?php $__env->startSection('content'); ?>
<div class="admin-page-header coupon-page-head"><div><span class="admin-page-eyebrow">Promotions</span><h2>Edit Coupon</h2><p>Update discount, timing, usage limits or catalog eligibility.</p></div><div class="admin-page-actions"><a href="<?php echo e(route('admin.coupons.index')); ?>" class="admin-button admin-button-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Coupons</a></div></div>
<form action="<?php echo e(route('admin.coupons.update',$coupon)); ?>" method="POST" novalidate><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?> <?php echo $__env->make('admin.coupons.partials.form',['coupon'=>$coupon], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></form>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('page-styles'); ?><style>.coupon-page-head{margin-bottom:18px}.coupon-page-head h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800}.coupon-page-head p{color:#7b8497;font-size:12px}</style><?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\coupons\edit.blade.php ENDPATH**/ ?>