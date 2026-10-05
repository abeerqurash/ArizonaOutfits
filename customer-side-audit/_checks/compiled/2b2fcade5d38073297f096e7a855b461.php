<div class="preloader">
    <div class="preloader-middle">
        <div class="left-preloader"></div>
        <div class="middle-preloader">
            <div class="stripe-preloader left"></div>
            <div class="stripe-preloader middle"></div>
            <div class="stripe-preloader right"></div>
        </div>
        <div class="right-preloader"></div>
    </div>
</div>
<div class="navbar">
    <div class="navbar-wrapper">
        <div class="left-navbar">
            <a href="/" class="logo">Arizona Outfits</a>
            <button type="button" class="menu-button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="arizonaMegaMenu">
                <span class="line line-1"></span>
                <span class="line line-2"></span>
                <span class="line line-3"></span>
            </button>
        </div>
        <div class="menu-wrapper">
            <div class="navigaiton">
                <div class="navigation-links">
                    <?php echo $__env->make('partials.header-menu-links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
            <div class="navigation-cover">

            </div>
        </div>
        <div class="mega-menu" id="arizonaMegaMenu" aria-hidden="true">
            <div class="mega-menu-wrapper">
                <div class="project-socials">
                    <div class="social-wrapper">
                        <div class="social-media">
                            <?php ($headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->facebook_url); ?>
<?php if($headerSocial): ?><a href="<?php echo e($headerSocial); ?>" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f text-color-dark"></i></a><?php endif; ?>
                            <?php ($headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->instagram_url); ?>
<?php if($headerSocial): ?><a href="<?php echo e($headerSocial); ?>" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram text-color-dark"></i></a><?php endif; ?>
                            <?php ($headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->x_url); ?>
<?php if($headerSocial): ?><a href="<?php echo e($headerSocial); ?>" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-x-twitter text-color-dark"></i></a><?php endif; ?>
                            <?php ($headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->linkedin_url); ?>
<?php if($headerSocial): ?><a href="<?php echo e($headerSocial); ?>" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-linkedin text-color-dark"></i></a><?php endif; ?>
                            <?php ($headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->youtube_url); ?>
<?php if($headerSocial): ?><a href="<?php echo e($headerSocial); ?>" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-youtube text-color-dark"></i></a><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="categories-wrapper">
                    <div class="categories-description"><div class="title">Product Categories</div>
                        <a href="<?php echo e(route('products.index')); ?>" class="btn-style-2 fs-12 text-color-white justify-self-end"><div class="button-text text-uppercase letter-space-3px">View All Products</div></a>
                    </div>
                    <div class="category-list"><div class="list-collection az-mega-category-grid">
                        <?php $__empty_1 = true; $__currentLoopData = app(\App\Services\NavigationMenuService::class)->megaCategories(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $megaCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="list-item"><a href="<?php echo e(route('products.category',$megaCategory->slug)); ?>" class="list"><div class="list-description"><div class="list-item-text"><?php echo e($megaCategory->title); ?></div></div><i class="fa-solid fa-arrow-right-long right-arrow-icon"></i></a></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p>No product categories selected yet.</p><?php endif; ?>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* White navigation cover remains visible before and after scrolling. */
.navbar .navigation-cover,
.navbar .navigation-cover.active {
    background-color: #fff !important;
    transform: none !important;
    transition: none !important;
    will-change: auto;
    border: 1px solid var(--dark-outline, #dbe2ed);
}
.navbar .navigation-links .nav-links-header,
.navbar.scrolled .navigation-links .nav-links-header,
.navbar .navigation-links .nav-links-header .button-text {
    color: #172033 !important;
}
.navbar .navigation-links .nav-links-header:hover,
.navbar .navigation-links .nav-links-header:hover .button-text {
    color: #0f766e !important;
}
.navbar .navigation-links .header-commerce-nav-count {
    background: #eef2f7;
    color: #172033;
    border-color: #dbe2ed;
}
.navbar .navigation-links .nav-links-header:focus-visible {
    outline: 2px solid #0f766e;
    outline-offset: -3px;
}
</style>

<style>
.navbar button.menu-button { background: #fff; color: #172033; font: inherit; }
.navbar button.menu-button:focus-visible { outline: 2px solid #0f766e; outline-offset: -3px; }
</style>
<script>
(function () {
    const navbarElement = document.querySelector('.navbar');
    if (!navbarElement) return;
    const trigger = navbarElement.querySelector('.menu-button');
    const panel = navbarElement.querySelector('.mega-menu');
    if (!trigger || !panel || trigger.dataset.arizonaMenuBound) return;
    trigger.dataset.arizonaMenuBound = 'true';
    function setOpen(open) {
        trigger.classList.toggle('open', open);
        panel.classList.toggle('active', open);
        trigger.setAttribute('aria-expanded', String(open));
        trigger.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        panel.setAttribute('aria-hidden', String(!open));
    }
    setOpen(false);
    // Capture handles the click before the older page-specific bubble listeners.
    trigger.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        setOpen(!panel.classList.contains('active'));
    }, true);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && panel.classList.contains('active')) {
            setOpen(false);
            trigger.focus();
        }
    });
    document.addEventListener('click', function (event) {
        if (panel.classList.contains('active') && !navbarElement.contains(event.target)) setOpen(false);
    });
    panel.addEventListener('click', function (event) {
        if (event.target.closest('a[href]')) setOpen(false);
    });
})();
</script>

<style>
.navbar .az-mega-category-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:0 24px;width:100%}
.navbar .az-mega-category-grid .list-item{width:100%;min-width:0}
.navbar .az-mega-category-grid .list-item-text{overflow-wrap:anywhere}
@media(max-width:600px){.navbar .az-mega-category-grid{grid-template-columns:1fr}}
</style>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\storefront-sync-files\_staged\resources\views/partials/header.blade.php ENDPATH**/ ?>