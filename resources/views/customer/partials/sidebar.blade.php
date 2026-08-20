<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <a href="{{ route('customer.dashboard') }}" class="admin-brand">
            <span class="admin-brand-icon"><i class="fa-solid fa-shirt"></i></span>
            <span class="admin-brand-content"><strong>Arizona Outfits</strong><small>Customer Account</small></span>
        </a>
        <button type="button" class="admin-sidebar-close" id="adminSidebarClose" aria-label="Close account sidebar"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="admin-sidebar-content">
        <div class="admin-menu-section">
            <span class="admin-menu-heading">My Account</span>
            <nav class="admin-menu" aria-label="Customer account navigation">
                <a href="{{ route('customer.dashboard') }}" class="admin-menu-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                    <span class="admin-menu-icon"><i class="fa-solid fa-chart-pie"></i></span><span class="admin-menu-text">Overview</span>
                </a>
                <a href="{{ route('customer.orders.index') }}" class="admin-menu-link {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}">
                    <span class="admin-menu-icon"><i class="fa-solid fa-box"></i></span><span class="admin-menu-text">My Orders</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="admin-menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span class="admin-menu-icon"><i class="fa-solid fa-user-pen"></i></span><span class="admin-menu-text">Profile & Security</span>
                </a>
            </nav>
        </div>

        <div class="admin-menu-section">
            <span class="admin-menu-heading">Shopping</span>
            <nav class="admin-menu">
                <a href="{{ route('products.index') }}" class="admin-menu-link">
                    <span class="admin-menu-icon"><i class="fa-solid fa-bag-shopping"></i></span><span class="admin-menu-text">Continue Shopping</span>
                </a>
                <a href="{{ route('home-page') }}" class="admin-menu-link">
                    <span class="admin-menu-icon"><i class="fa-solid fa-house"></i></span><span class="admin-menu-text">Store Home</span>
                </a>
            </nav>
        </div>
    </div>

    <div class="admin-sidebar-footer">
        <a href="{{ route('home-page') }}" class="admin-menu-link" target="_blank" rel="noopener">
            <span class="admin-menu-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span><span class="admin-menu-text">View Website</span>
        </a>
        <form action="{{ route('logout') }}" method="POST">@csrf
            <button type="submit" class="admin-menu-link admin-logout-button"><span class="admin-menu-icon"><i class="fa-solid fa-right-from-bracket"></i></span><span class="admin-menu-text">Logout</span></button>
        </form>
    </div>
</aside>
