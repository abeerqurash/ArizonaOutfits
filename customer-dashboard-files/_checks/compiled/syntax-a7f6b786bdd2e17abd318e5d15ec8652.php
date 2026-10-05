<?php
    $topbarUser = auth('web')->user();
    $topbarEmail = null;

    if ($topbarUser) {
        $topbarEmailState = $topbarUser->resolvedEmailIdentityState();

        /*
         * Show only an email belonging to a CURRENTLY CONNECTED source.
         * Never fall back to users.email because that column may intentionally
         * retain historical/contact data after a login identity is disconnected.
         */
        $topbarEmail = $topbarEmailState['primary_email'] ?? null;
    }
?>

<header class="admin-topbar">
    <div class="admin-topbar-left">
        <button
            type="button"
            class="admin-mobile-menu-button"
            id="adminMobileMenuButton"
            aria-label="Open account sidebar"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="admin-topbar-title">
            <span class="admin-topbar-eyebrow">Customer Account</span>
            <h1><?php echo $__env->yieldContent('page-heading', 'Overview'); ?></h1>
        </div>
    </div>

    <div class="admin-topbar-right">
        <a
            href="<?php echo e(route('products.index')); ?>"
            class="admin-topbar-action"
            title="Continue shopping"
        >
            <i class="fa-solid fa-store"></i>
        </a>

        <div class="admin-profile-wrapper">
            <button
                type="button"
                class="admin-profile-button"
                id="adminProfileButton"
                aria-expanded="false"
            >
                <span class="admin-profile-avatar">
                    <?php echo e(strtoupper(substr($topbarUser?->name ?? 'C', 0, 1))); ?>

                </span>

                <span class="admin-profile-details">
                    <strong><?php echo e($topbarUser?->name ?? 'Customer'); ?></strong>
                    <small>Customer</small>
                </span>

                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div
                class="admin-dropdown admin-profile-dropdown"
                id="adminProfileDropdown"
            >
                <div class="admin-profile-dropdown-header">
                    <strong><?php echo e($topbarUser?->name ?? 'Customer'); ?></strong>

                    <?php if(filled($topbarEmail)): ?>
                        <span><?php echo e($topbarEmail); ?></span>
                    <?php endif; ?>
                </div>

                <a href="<?php echo e(route('profile.edit')); ?>">
                    <i class="fa-regular fa-user"></i>
                    Edit Profile
                </a>

                <a href="<?php echo e(route('customer.orders.index')); ?>">
                    <i class="fa-solid fa-box"></i>
                    My Orders
                </a>

                <a href="<?php echo e(route('customer.security')); ?>"><i class="fa-solid fa-shield-halved"></i>Login & Security</a>
                <a href="<?php echo e(route('favorites.index')); ?>"><i class="fa-regular fa-heart"></i>My Favorites</a>
                <a href="<?php echo e(route('home-page')); ?>">
                    <i class="fa-solid fa-store"></i>
                    View Store
                </a>

                <form action="<?php echo e(route('logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <button type="submit">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
