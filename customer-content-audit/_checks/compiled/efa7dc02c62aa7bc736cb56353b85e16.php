<aside class="admin-sidebar" id="adminSidebar">



    <div class="admin-sidebar-header">



        <a

            href="<?php echo e(route('admin.dashboard')); ?>"

            class="admin-brand">



            <span class="admin-brand-icon">

                <i class="fa-solid fa-shirt"></i>

            </span>



            <span class="admin-brand-content">

                <strong>Arizona Outfits</strong>

                <small>Administration</small>

            </span>



        </a>



        <button

            type="button"

            class="admin-sidebar-close"

            id="adminSidebarClose"

            aria-label="Close admin sidebar">



            <i class="fa-solid fa-xmark"></i>



        </button>



    </div>



    <div class="admin-sidebar-content">



        

        <div class="admin-menu-section">



            <span class="admin-menu-heading">

                Overview

            </span>



            <nav class="admin-menu">



                <a

                    href="<?php echo e(route('admin.dashboard')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.dashboard')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-chart-pie"></i>

                    </span>



                    <span class="admin-menu-text">

                        Dashboard

                    </span>



                </a>



            </nav>



        </div>



        

        <div class="admin-menu-section">



            <span class="admin-menu-heading">

                Store Management

            </span>



            <nav class="admin-menu">



                <?php if(auth('admin')->user()?->hasAdminPermission('orders.manage')): ?>
<a

                    href="<?php echo e(route('admin.orders.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.orders.*') && !request()->routeIs('admin.orders.archived', 'admin.orders.restore', 'admin.orders.bulk-restore')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-bag-shopping"></i>

                    </span>



                    <span class="admin-menu-text">

                        Orders

                    </span>



                </a>
<?php endif; ?>



                
                <?php if(auth('admin')->user()?->hasAdminPermission('orders.manage')): ?>
<a
                    href="<?php echo e(route('admin.orders.archived')); ?>"
                    class="admin-menu-link <?php echo e(request()->routeIs('admin.orders.archived', 'admin.orders.restore', 'admin.orders.bulk-restore') ? 'active' : ''); ?>">
                    <span class="admin-menu-icon"><i class="fa-solid fa-box-archive"></i></span>
                    <span class="admin-menu-text">Archived Orders</span>
                </a>
<?php endif; ?>

                
                <?php if(auth('admin')->user()?->hasAdminPermission('orders.manage')): ?>
<a
                    href="<?php echo e(route('admin.payment-verifications.index')); ?>"
                    class="admin-menu-link <?php echo e(request()->routeIs('admin.payment-verifications.*') ? 'active' : ''); ?>">
                    <span class="admin-menu-icon"><i class="fa-solid fa-credit-card"></i></span>
                    <span class="admin-menu-text">Payment Verifications</span>
                </a>
<?php endif; ?>

                <?php if(auth('admin')->user()?->hasAdminPermission('products.manage')): ?>
<a

                    href="<?php echo e(route('admin.products.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.products.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-box-open"></i>

                    </span>



                    <span class="admin-menu-text">

                        Products

                    </span>



                </a>
<?php endif; ?>



                <?php if(auth('admin')->user()?->hasAdminPermission('products.manage')): ?>
<a

                    href="<?php echo e(route('admin.product-categories.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.product-categories.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-layer-group"></i>

                    </span>



                    <span class="admin-menu-text">

                        Product Categories

                    </span>



                </a>
<?php endif; ?>



                <?php if(auth('admin')->user()?->hasAdminPermission('products.manage')): ?>
<a

                    href="<?php echo e(route('admin.product-tags.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.product-tags.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-tags"></i>

                    </span>



                    <span class="admin-menu-text">

                        Product Tags

                    </span>



                </a>
<?php endif; ?>



                <?php if(auth('admin')->user()?->hasAdminPermission('coupons.manage')): ?>
<a

                    href="<?php echo e(route('admin.coupons.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.coupons.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-ticket"></i>

                    </span>



                    <span class="admin-menu-text">

                        Coupons

                    </span>



                </a>
<?php endif; ?>



                <?php if(auth('admin')->user()?->hasAdminPermission('inventory.manage')): ?>
<a

                    href="<?php echo e(route('admin.inventory-alerts.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.inventory-alerts.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-regular fa-bell"></i>

                    </span>



                    <span class="admin-menu-text">

                        Inventory Alerts

                    </span>



                    <?php if(($activeInventoryAlertCount ?? 0) > 0): ?>



                    <span class="inventory-alert-count">



                        <?php echo e($activeInventoryAlertCount > 99

                                    ? '99+'

                                    : $activeInventoryAlertCount); ?>




                    </span>



                    <?php endif; ?>



                </a>
<?php endif; ?>



                <?php if(auth('admin')->user()?->hasAdminPermission('inventory.manage')): ?>
<a

                    href="<?php echo e(route('admin.inventory-history.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.inventory-history.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-clock-rotate-left"></i>

                    </span>



                    <span class="admin-menu-text">

                        Inventory History

                    </span>



                </a>
<?php endif; ?>



                <?php if(auth('admin')->user()?->hasAdminPermission('inventory.manage')): ?>
<a

                    href="<?php echo e(route('admin.inventory-reports.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.inventory-reports.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-file-lines"></i>

                    </span>



                    <span class="admin-menu-text">

                        Inventory Reports

                    </span>



                </a>
<?php endif; ?>



                

                <?php if(auth('admin')->user()?->hasAdminPermission('inventory.manage')): ?>
<a

                    href="<?php echo e(route('admin.stock-valuation.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.stock-valuation.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-chart-column"></i>

                    </span>



                    <span class="admin-menu-text">

                        Stock Valuation

                    </span>



                </a>
<?php endif; ?>

                <?php if(auth('admin')->user()?->hasAdminPermission('inventory.manage')): ?>
<a

                    href="<?php echo e(route('admin.reorder-dashboard.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.reorder-dashboard.*')

            ? 'active'

            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-cart-flatbed"></i>

                    </span>



                    <span class="admin-menu-text">

                        Reorder Dashboard

                    </span>



                    <?php if(($reorderRequiredCount ?? 0) > 0): ?>



                    <span class="inventory-alert-count">



                        <?php echo e($reorderRequiredCount > 99

                    ? '99+'

                    : $reorderRequiredCount); ?>




                    </span>



                    <?php endif; ?>



                </a>
<?php endif; ?>

                <?php if(auth('admin')->user()?->hasAdminPermission('purchase-orders.manage')): ?>
<a

                    href="<?php echo e(route('admin.purchase-orders.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.purchase-orders.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-file-invoice-dollar"></i>

                    </span>



                    <span class="admin-menu-text">

                        Purchase Orders

                    </span>



                </a>
<?php endif; ?>

                <?php if(auth('admin')->user()?->hasAdminPermission('suppliers.manage')): ?>
<a

                    href="<?php echo e(route(

        'admin.suppliers.index'

    )); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs(

            'admin.suppliers.*'

        )

            ? 'active'

            : ''); ?>">



                    <span class="admin-menu-icon">



                        <i class="fa-solid fa-truck-field"></i>



                    </span>



                    <span class="admin-menu-text">

                        Suppliers

                    </span>



                </a>
<?php endif; ?>

            </nav>



        </div>



        

        <div class="admin-menu-section">



            <span class="admin-menu-heading">

                Customers

            </span>



            <nav class="admin-menu">



                <?php if(auth('admin')->user()?->hasAdminPermission('customers.manage')): ?>
<a

                    href="<?php echo e(route('admin.customers.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.customers.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-users"></i>

                    </span>



                    <span class="admin-menu-text">

                        Customers

                    </span>



                </a>
<?php endif; ?>



                <?php if(auth('admin')->user()?->hasAdminPermission('reviews.manage')): ?>
<a

                    href="<?php echo e(route('admin.reviews.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.reviews.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-star"></i>

                    </span>



                    <span class="admin-menu-text">

                        Reviews

                    </span>



                </a>
<?php endif; ?>



            </nav>



        </div>



        

        <div class="admin-menu-section">



            <span class="admin-menu-heading">

                Content

            </span>



            <nav class="admin-menu">



                <?php if(auth('admin')->user()?->hasAdminPermission('content.manage')): ?>
<a

                    href="<?php echo e(route('admin.posts.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.posts.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-newspaper"></i>

                    </span>



                    <span class="admin-menu-text">

                        Blog Posts

                    </span>



                </a>
<?php endif; ?>



                <?php if(auth('admin')->user()?->hasAdminPermission('content.manage')): ?>
<a

                    href="<?php echo e(route('admin.categories.index')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.categories.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-folder-tree"></i>

                    </span>



                    <span class="admin-menu-text">

                        Blog Categories

                    </span>



                </a>
<?php endif; ?>



            </nav>



        </div>



        

        <div class="admin-menu-section">



            <span class="admin-menu-heading">

                Configuration

            </span>



            <nav class="admin-menu">



                <?php if(auth('admin')->user()?->hasAdminPermission('settings.manage')): ?>
<a

                    href="<?php echo e(route('admin.settings.edit')); ?>"

                    class="admin-menu-link <?php echo e(request()->routeIs('admin.settings.*')

                            ? 'active'

                            : ''); ?>">



                    <span class="admin-menu-icon">

                        <i class="fa-solid fa-gear"></i>

                    </span>



                    <span class="admin-menu-text">

                        Store Settings

                    </span>



                </a>
<?php endif; ?>

                <?php echo $__env->make('admin.partials.administration-links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            </nav>



        </div>



    </div>



    <div class="admin-sidebar-footer">



        <a

            href="<?php echo e(route('home-page')); ?>"

            class="admin-menu-link"

            target="_blank"

            rel="noopener">



            <span class="admin-menu-icon">

                <i class="fa-solid fa-arrow-up-right-from-square"></i>

            </span>



            <span class="admin-menu-text">

                View Website

            </span>



        </a>



        <form

            action="<?php echo e(route('admin.logout')); ?>"

            method="POST">



            <?php echo csrf_field(); ?>



            <button

                type="submit"

                class="admin-menu-link admin-logout-button">



                <span class="admin-menu-icon">

                    <i class="fa-solid fa-right-from-bracket"></i>

                </span>



                <span class="admin-menu-text">

                    Logout

                </span>



            </button>



        </form>



    </div>



</aside>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/partials/sidebar.blade.php ENDPATH**/ ?>