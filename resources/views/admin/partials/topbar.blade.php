<header class="admin-topbar">

    <div class="admin-topbar-left">

        <button
            type="button"
            class="admin-mobile-menu-button"
            id="adminMobileMenuButton"
            aria-label="Open admin sidebar"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="admin-topbar-title">
            <span class="admin-topbar-eyebrow">
                Administration
            </span>

            <h1>
                @yield('page-heading', 'Dashboard')
            </h1>
        </div>

    </div>

    <div class="admin-topbar-right">

        <a
            href="{{ route('home-page') }}"
            class="admin-topbar-action"
            target="_blank"
            rel="noopener"
            title="View website"
        >
            <i class="fa-solid fa-store"></i>
        </a>

        <div class="admin-notification-wrapper">

            <button
                type="button"
                class="admin-topbar-action"
                id="adminNotificationButton"
                aria-label="Open notifications"
                aria-expanded="false"
            >
                <i class="fa-regular fa-bell"></i>

                <span class="admin-notification-dot"></span>
            </button>

            <div
                class="admin-dropdown admin-notification-dropdown"
                id="adminNotificationDropdown"
            >
                <div class="admin-dropdown-header">
                    <div>
                        <strong>Notifications</strong>
                        <span>Important store updates</span>
                    </div>
                </div>

                <div class="admin-dropdown-empty">
                    <i class="fa-regular fa-bell-slash"></i>

                    <p>
                        Notification data will be connected in Phase 3.
                    </p>
                </div>
            </div>

        </div>

        <div class="admin-profile-wrapper">

            <button
                type="button"
                class="admin-profile-button"
                id="adminProfileButton"
                aria-expanded="false"
            >
                <span class="admin-profile-avatar">
                    {{
                        strtoupper(
                            substr(auth()->user()?->name ?? 'A', 0, 1)
                        )
                    }}
                </span>

                <span class="admin-profile-details">
                    <strong>
                        {{ auth()->user()?->name ?? 'Administrator' }}
                    </strong>

                    <small>
                        Administrator
                    </small>
                </span>

                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div
                class="admin-dropdown admin-profile-dropdown"
                id="adminProfileDropdown"
            >
                <div class="admin-profile-dropdown-header">
                    <strong>
                        {{ auth()->user()?->name ?? 'Administrator' }}
                    </strong>

                    <span>
                        {{ auth()->user()?->email }}
                    </span>
                </div>

                <a href="{{ route('profile.edit') }}">
                    <i class="fa-regular fa-user"></i>
                    Edit Profile
                </a>

                <a
                    href="{{ route('home-page') }}"
                    target="_blank"
                    rel="noopener"
                >
                    <i class="fa-solid fa-store"></i>
                    View Store
                </a>

                <form
                    action="{{ route('admin.logout') }}"
                    method="POST"
                >
                    @csrf

                    <button type="submit">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </button>
                </form>
            </div>

        </div>

    </div>

</header>