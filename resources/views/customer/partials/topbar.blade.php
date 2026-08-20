<header class="admin-topbar">
    <div class="admin-topbar-left">
        <button type="button" class="admin-mobile-menu-button" id="adminMobileMenuButton" aria-label="Open account sidebar"><i class="fa-solid fa-bars"></i></button>
        <div class="admin-topbar-title"><span class="admin-topbar-eyebrow">Customer Account</span><h1>@yield('page-heading', 'Overview')</h1></div>
    </div>
    <div class="admin-topbar-right">
        <a href="{{ route('products.index') }}" class="admin-topbar-action" title="Continue shopping"><i class="fa-solid fa-store"></i></a>
        <div class="admin-profile-wrapper">
            <button type="button" class="admin-profile-button" id="adminProfileButton" aria-expanded="false">
                <span class="admin-profile-avatar">{{ strtoupper(substr(auth()->user()?->name ?? 'C', 0, 1)) }}</span>
                <span class="admin-profile-details"><strong>{{ auth()->user()?->name ?? 'Customer' }}</strong><small>Customer</small></span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="admin-dropdown admin-profile-dropdown" id="adminProfileDropdown">
                <div class="admin-profile-dropdown-header"><strong>{{ auth()->user()?->name ?? 'Customer' }}</strong><span>{{ auth()->user()?->email }}</span></div>
                <a href="{{ route('profile.edit') }}"><i class="fa-regular fa-user"></i>Edit Profile</a>
                <a href="{{ route('customer.orders.index') }}"><i class="fa-solid fa-box"></i>My Orders</a>
                <a href="{{ route('home-page') }}"><i class="fa-solid fa-store"></i>View Store</a>
                <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit"><i class="fa-solid fa-right-from-bracket"></i>Logout</button></form>
            </div>
        </div>
    </div>
</header>
