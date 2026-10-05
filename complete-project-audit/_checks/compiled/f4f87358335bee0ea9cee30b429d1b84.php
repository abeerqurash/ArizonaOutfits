<nav class="account-navigation" aria-label="Customer account navigation">
    <a href="<?php echo e(route('customer.dashboard')); ?>" class="<?php echo e(request()->routeIs('customer.dashboard') ? 'active' : ''); ?>">
        <i class="fa-solid fa-gauge-high"></i> Overview
    </a>
    <a href="<?php echo e(route('customer.orders.index')); ?>" class="<?php echo e(request()->routeIs('customer.orders.*') ? 'active' : ''); ?>">
        <i class="fa-solid fa-box"></i> My Orders
    </a>
    <a href="<?php echo e(route('profile.edit')); ?>" class="<?php echo e(request()->routeIs('profile.*') ? 'active' : ''); ?>">
        <i class="fa-solid fa-user-pen"></i> Profile
    </a>
    <a href="<?php echo e(route('customer.security')); ?>"><i class="fa-solid fa-shield-halved"></i> Login & Security</a>
    <a href="<?php echo e(route('favorites.index')); ?>"><i class="fa-regular fa-heart"></i> My Favorites</a>
    <form method="POST" action="<?php echo e(route('logout')); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</button>
    </form>
</nav>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\customer\partials\navigation.blade.php ENDPATH**/ ?>