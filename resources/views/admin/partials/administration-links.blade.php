@if (auth()->user()?->hasAdminPermission('content.manage'))
    <a href="{{ route('admin.pages.index') }}" class="admin-menu-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-regular fa-file-lines"></i></span>
        <span class="admin-menu-text">CMS Pages</span>
    </a>
@endif

@if (auth()->user()?->hasAdminPermission('content.manage'))
    <a href="{{ route('admin.navigation-menus.index') }}" class="admin-menu-link {{ request()->routeIs('admin.navigation-menus.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-solid fa-bars-staggered"></i></span>
        <span class="admin-menu-text">Menu Builder</span>
    </a>
@endif

@if (auth()->user()?->hasAdminPermission('dashboard.view'))
    <a href="{{ route('admin.analytics.index') }}" class="admin-menu-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-solid fa-chart-line"></i></span>
        <span class="admin-menu-text">Global Analytics</span>
    </a>
@endif

@if (auth()->user()?->hasAdminPermission('admin-users.manage'))
    <a href="{{ route('admin.admin-users.index') }}" class="admin-menu-link {{ request()->routeIs('admin.admin-users.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-solid fa-users-gear"></i></span>
        <span class="admin-menu-text">Admin Team</span>
    </a>
    <a href="{{ route('admin.admin-roles.index') }}" class="admin-menu-link {{ request()->routeIs('admin.admin-roles.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-solid fa-user-shield"></i></span>
        <span class="admin-menu-text">Roles & Permissions</span>
    </a>
@endif

@if (auth()->user()?->hasAdminPermission('notifications.manage'))
    <a href="{{ route('admin.notifications.index') }}" class="admin-menu-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-regular fa-bell"></i></span>
        <span class="admin-menu-text">Notification Center</span>
    </a>
@endif

@if (auth()->user()?->hasAdminPermission('audit-logs.view'))
    <a href="{{ route('admin.audit-logs.index') }}" class="admin-menu-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-solid fa-clipboard-list"></i></span>
        <span class="admin-menu-text">Audit Logs</span>
    </a>
@endif

@if (auth()->user()?->hasAdminPermission('backups.manage'))
    <a href="{{ route('admin.backups.index') }}" class="admin-menu-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-solid fa-database"></i></span>
        <span class="admin-menu-text">Backups</span>
    </a>
@endif

@if (auth()->user()?->hasAdminPermission('settings.manage'))
    <a href="{{ route('admin.email-templates.index') }}" class="admin-menu-link {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
        <span class="admin-menu-icon"><i class="fa-solid fa-envelope-open-text"></i></span>
        <span class="admin-menu-text">Email Templates</span>
    </a>
@endif
