<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <a href="<?php echo e(route('customer.dashboard')); ?>" class="admin-brand">
            <span class="admin-brand-icon"><i class="fa-solid fa-shirt"></i></span>
            <span class="admin-brand-content"><strong>Arizona Outfits</strong><small>Customer Account</small></span>
        </a>
        <button type="button" class="admin-sidebar-close" id="adminSidebarClose" aria-label="Close account sidebar"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="admin-sidebar-content">
        <div class="admin-menu-section">
            <span class="admin-menu-heading">My Account</span>
            <nav class="admin-menu" aria-label="Customer account navigation">
                <a href="<?php echo e(route('customer.dashboard')); ?>" class="admin-menu-link <?php echo e(request()->routeIs('customer.dashboard') ? 'active' : ''); ?>">
                    <span class="admin-menu-icon"><i class="fa-solid fa-chart-pie"></i></span><span class="admin-menu-text">Overview</span>
                </a>
                <a href="<?php echo e(route('customer.orders.index')); ?>" class="admin-menu-link <?php echo e(request()->routeIs('customer.orders.*') ? 'active' : ''); ?>">
                    <span class="admin-menu-icon"><i class="fa-solid fa-box"></i></span><span class="admin-menu-text">My Orders</span>
                </a>
                <a href="<?php echo e(route('profile.edit')); ?>" class="admin-menu-link <?php echo e(request()->routeIs('profile.*') ? 'active' : ''); ?>">
                    <span class="admin-menu-icon"><i class="fa-solid fa-user-pen"></i></span><span class="admin-menu-text">Profile</span>
                </a>
                <a href="<?php echo e(route('customer.security')); ?>" class="admin-menu-link <?php echo e(request()->routeIs('customer.security*') ? 'active' : ''); ?>"><span class="admin-menu-icon"><i class="fa-solid fa-shield-halved"></i></span><span class="admin-menu-text">Login & Security</span></a>
                <a href="<?php echo e(route('favorites.index')); ?>" class="admin-menu-link <?php echo e(request()->routeIs('favorites.*') ? 'active' : ''); ?>"><span class="admin-menu-icon"><i class="fa-regular fa-heart"></i></span><span class="admin-menu-text">My Favorites</span></a>
            </nav>
        </div>

        <div class="admin-menu-section">
            <span class="admin-menu-heading">Shopping</span>
            <nav class="admin-menu">
                <a href="<?php echo e(route('products.index')); ?>" class="admin-menu-link">
                    <span class="admin-menu-icon"><i class="fa-solid fa-bag-shopping"></i></span><span class="admin-menu-text">Continue Shopping</span>
                </a>
                <a href="<?php echo e(route('home-page')); ?>" class="admin-menu-link">
                    <span class="admin-menu-icon"><i class="fa-solid fa-house"></i></span><span class="admin-menu-text">Store Home</span>
                </a>
            </nav>
        </div>
    </div>

    <div class="admin-sidebar-footer">
        <a href="<?php echo e(route('home-page')); ?>" class="admin-menu-link" target="_blank" rel="noopener">
            <span class="admin-menu-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span><span class="admin-menu-text">View Website</span>
        </a>
        <form action="<?php echo e(route('logout')); ?>" method="POST"><?php echo csrf_field(); ?>
            <button type="submit" class="admin-menu-link admin-logout-button"><span class="admin-menu-icon"><i class="fa-solid fa-right-from-bracket"></i></span><span class="admin-menu-text">Logout</span></button>
        </form>
    </div>
</aside>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\customer\partials\sidebar.blade.php ENDPATH**/ ?>