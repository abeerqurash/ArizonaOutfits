<?php ($footerSettings=app(\App\Services\StoreSettingsService::class)->settings()); ?>
<div class="footer-section">
    <div class="wrapper">
        <div class="footer-wrapper">
            <div class="footer-logo-heading">
                <a href="/" class="text-decoration-none"><h1 class="text-color-white">ARIZONA OUTFITS</h1></a>
            </div>
            <div class="follow-social"><div class="subtitle">Follow Us</div><div class="social-link-list"><?php $__currentLoopData = ['facebook'=>['Facebook','facebook-f'],'instagram'=>['Instagram','instagram'],'linkedin'=>['LinkedIn','linkedin'],'youtube'=>['YouTube','youtube'],'x'=>['X','x-twitter']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $network=>$details): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php ($socialUrl=$footerSettings->{$network.'_url'}); ?><?php if($socialUrl && \App\Services\SafeContentUrl::allowed($socialUrl)): ?><a href="<?php echo e($socialUrl); ?>" class="list-item" target="_blank" rel="noopener noreferrer"><div class="social-link-icon"><i class="fa-brands fa-<?php echo e($details[1]); ?> text-color-dark"></i></div><div class="social-link-text"><?php echo e($details[0]); ?></div><i class="fa-solid fa-arrow-right-long social-right-arrow"></i></a><?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div></div>
            <div class="navigation-link">
                <div class="subtitle">Navigation</div>
                <div class="navigation-wrapper">
                    <?php echo $__env->make('partials.footer-menu-links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
            <div class="about-description">
                <h3>About</h3>
                <p class="about-para"><?php echo e($footerSettings->footer_about ?: 'Discover the latest collections at Arizona Outfits.'); ?></p>
            </div>
            <div class="footer-copyright"><div><?php echo e($footerSettings->footer_copyright ?: '© '.date('Y').' '.($footerSettings->store_name ?: 'Arizona Outfits').'. All rights reserved.'); ?></div></div>
        </div>
    </div>
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
</div>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\partials\footer.blade.php ENDPATH**/ ?>