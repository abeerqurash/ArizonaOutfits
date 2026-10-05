<div class="author-card">
    <div class="author-avator" style="background-image: url('<?php echo e(asset('asset/media/about-abeer.png')); ?>');"></div>
    <div class="author-information">
        <div class="author-name-title">
            <div class="author-subtitle fs-12 text-color-dark letter-space-4px">CEO</div>
            <div class="author-name fs-18 text-color-dark"><?php echo e(app(\App\Services\StoreSettingsService::class)->settings()->store_name ?: 'Arizona Outfits'); ?></div>
        </div>
        <div class="social-wrapper">
            <div class="social-media">
                <?php ($profileSocial=app(\App\Services\StoreSettingsService::class)->settings()->facebook_url); ?>
<?php if($profileSocial): ?><a href="<?php echo e($profileSocial); ?>" class="icon-and-title text-decoration-none all-border" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f text-color-dark"></i></a><?php endif; ?>
                <?php ($profileSocial=app(\App\Services\StoreSettingsService::class)->settings()->instagram_url); ?>
<?php if($profileSocial): ?><a href="<?php echo e($profileSocial); ?>" class="icon-and-title text-decoration-none all-border" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram text-color-dark"></i></a><?php endif; ?>
                <?php ($profileSocial=app(\App\Services\StoreSettingsService::class)->settings()->x_url); ?>
<?php if($profileSocial): ?><a href="<?php echo e($profileSocial); ?>" class="icon-and-title text-decoration-none all-border" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-x-twitter text-color-dark"></i></a><?php endif; ?>
                <?php ($profileSocial=app(\App\Services\StoreSettingsService::class)->settings()->linkedin_url); ?>
<?php if($profileSocial): ?><a href="<?php echo e($profileSocial); ?>" class="icon-and-title text-decoration-none all-border" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-linkedin text-color-dark"></i></a><?php endif; ?>
                <?php ($profileSocial=app(\App\Services\StoreSettingsService::class)->settings()->youtube_url); ?>
<?php if($profileSocial): ?><a href="<?php echo e($profileSocial); ?>" class="icon-and-title text-decoration-none all-border" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-youtube text-color-dark"></i></a><?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/partials/blog-author-card-info.blade.php ENDPATH**/ ?>