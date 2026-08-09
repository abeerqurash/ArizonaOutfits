<nav class="account-navigation" aria-label="Customer account navigation">
    <a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge-high"></i> Overview
    </a>
    <a href="{{ route('customer.orders.index') }}" class="{{ request()->routeIs('customer.orders.*') ? 'active' : '' }}">
        <i class="fa-solid fa-box"></i> My Orders
    </a>
    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <i class="fa-solid fa-user-pen"></i> Profile
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</button>
    </form>
</nav>
