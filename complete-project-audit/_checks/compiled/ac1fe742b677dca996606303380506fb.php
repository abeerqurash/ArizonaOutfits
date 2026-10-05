<?php $__empty_1 = true; $__currentLoopData = ($navigationMenuItems ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <a href="<?php echo e($menuItem->resolved_url); ?>" class="footer-nav-link" <?php if($menuItem->commerce_behavior==='cart_sidebar'): ?> data-header-cart-trigger <?php endif; ?> <?php if($menuItem->open_in_new_tab): ?> target="_blank" rel="noopener" <?php endif; ?>>
        <div class="link-item-text"><?php echo e($menuItem->display_label); ?></div>
        <i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i>
    </a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <a href="/about" class="footer-nav-link"><div class="link-item-text">About</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/projects" class="footer-nav-link"><div class="link-item-text">Projects</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/services" class="footer-nav-link"><div class="link-item-text">Services</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/blogs" class="footer-nav-link"><div class="link-item-text">Blogs</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/contact" class="footer-nav-link"><div class="link-item-text">Contact</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/category" class="footer-nav-link"><div class="link-item-text">Categories</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/privacy-policy" class="footer-nav-link"><div class="link-item-text">Privacy Policy</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/terms-and-conditions" class="footer-nav-link"><div class="link-item-text">Terms &amp; Conditions</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\partials\footer-menu-links.blade.php ENDPATH**/ ?>