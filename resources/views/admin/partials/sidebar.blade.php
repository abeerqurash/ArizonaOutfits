<aside class="admin-sidebar" id="adminSidebar">

    <div class="admin-sidebar-header">

        <a
            href="{{ route('admin.dashboard') }}"
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
                    href="{{ route('admin.dashboard') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.dashboard')
                            ? 'active'
                            : ''
                    }}">
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

                <a
                    href="{{ route('admin.orders.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.orders.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </span>

                    <span class="admin-menu-text">
                        Orders
                    </span>
                </a>

                <a
                    href="{{ route('admin.products.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.products.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-box-open"></i>
                    </span>

                    <span class="admin-menu-text">
                        Products
                    </span>
                </a>

                <a
                    href="{{ route('admin.product-categories.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.product-categories.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>

                    <span class="admin-menu-text">
                        Product Categories
                    </span>
                </a>

                <a
                    href="{{ route('admin.product-tags.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.product-tags.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-tags"></i>
                    </span>

                    <span class="admin-menu-text">
                        Product Tags
                    </span>
                </a>

                <a
                    href="{{ route('admin.coupons.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.coupons.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-ticket"></i>
                    </span>

                    <span class="admin-menu-text">
                        Coupons
                    </span>
                </a>

                <a
                    href="{{ route('admin.inventory-alerts.index') }}"
                    class="admin-menu-link {{ request()->routeIs('admin.inventory-alerts.*') ? 'active' : '' }}">
                    <span class="admin-menu-icon">
                        <i class="fa-regular fa-bell"></i>
                    </span>
                    <span class="admin-menu-text">Inventory Alerts</span>

                    @if(($activeInventoryAlertCount ?? 0) > 0)
                    <span class="inventory-alert-count">
                        {{ $activeInventoryAlertCount > 99 ? '99+' : $activeInventoryAlertCount }}
                    </span>
                    @endif
                </a>

                <a
                    href="{{ route('admin.inventory-history.index') }}"
                    class="admin-menu-link {{ request()->routeIs('admin.inventory-history.*') ? 'active' : '' }}">

                    <i class="fas fa-history"></i>

                    <span>

                        Inventory History

                    </span>

                </a>
                <a
                    href="{{ route('admin.inventory-reports.index') }}"
                    class="admin-menu-link {{ request()->routeIs('admin.inventory-reports.*') ? 'active' : '' }}">
                    <i class="fas fa-file"></i>
                    <span>Inventory Reports</span>
                </a>

            </nav>

        </div>

        <div class="admin-menu-section">

            <span class="admin-menu-heading">
                Customers
            </span>

            <nav class="admin-menu">

                <a
                    href="{{ route('admin.customers.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.customers.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>

                    <span class="admin-menu-text">
                        Customers
                    </span>
                </a>

                <a
                    href="{{ route('admin.reviews.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.reviews.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-star"></i>
                    </span>

                    <span class="admin-menu-text">
                        Reviews
                    </span>
                </a>

            </nav>

        </div>

        <div class="admin-menu-section">

            <span class="admin-menu-heading">
                Content
            </span>

            <nav class="admin-menu">

                <a
                    href="{{ route('admin.posts.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.posts.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-newspaper"></i>
                    </span>

                    <span class="admin-menu-text">
                        Blog Posts
                    </span>
                </a>

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.categories.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-folder-tree"></i>
                    </span>

                    <span class="admin-menu-text">
                        Blog Categories
                    </span>
                </a>

            </nav>

        </div>

        <div class="admin-menu-section">

            <span class="admin-menu-heading">
                Configuration
            </span>

            <nav class="admin-menu">

                <a
                    href="{{ route('admin.settings.edit') }}"
                    class="admin-menu-link {{
                        request()->routeIs('admin.settings.*')
                            ? 'active'
                            : ''
                    }}">
                    <span class="admin-menu-icon">
                        <i class="fa-solid fa-gear"></i>
                    </span>

                    <span class="admin-menu-text">
                        Store Settings
                    </span>
                </a>

            </nav>

        </div>

    </div>

    <div class="admin-sidebar-footer">

        <a
            href="{{ route('home-page') }}"
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
            action="{{ route('admin.logout') }}"
            method="POST">
            @csrf

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