<header class="admin-topbar">
    <div class="admin-topbar-left">
        <button type="button" class="admin-mobile-menu-button" id="adminMobileMenuButton" aria-label="Open admin sidebar"><i class="fa-solid fa-bars"></i></button>
        <div class="admin-topbar-title"><span class="admin-topbar-eyebrow">Administration</span><h1>@yield('page-heading', 'Dashboard')</h1></div>
    </div>

    <div class="admin-topbar-right">
        <a href="{{ route('home-page') }}" class="admin-topbar-action" target="_blank" rel="noopener" title="View website"><i class="fa-solid fa-store"></i></a>

        @if (auth('admin')->user()?->hasAdminPermission('notifications.manage'))
            <div class="admin-notification-wrapper">
                <button type="button" class="admin-topbar-action" id="adminNotificationButton" aria-label="Open notifications" aria-expanded="false">
                    <i class="fa-regular fa-bell"></i>
                    @if (($unreadAdminNotificationCount ?? 0) > 0)
                        <span class="admin-notification-count">{{ $unreadAdminNotificationCount > 99 ? '99+' : $unreadAdminNotificationCount }}</span>
                    @endif
                </button>
                <div class="admin-dropdown admin-notification-dropdown" id="adminNotificationDropdown">
                    <div class="admin-dropdown-header notification-heading">
                        <div><strong>Notifications</strong><span>{{ number_format($unreadAdminNotificationCount ?? 0) }} unread update(s)</span></div>
                        <a href="{{ route('admin.notifications.index') }}">View all</a>
                    </div>
                    @forelse (($latestAdminNotifications ?? collect()) as $notification)
                        @php
                            $level = in_array(data_get($notification->data, 'level'), ['info','success','warning','danger'], true) ? data_get($notification->data, 'level') : 'info';
                        @endphp
                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}" class="topbar-notification {{ $notification->read_at ? '' : 'unread' }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">
                                <span class="notification-icon {{ $level }}"><i class="fa-solid {{ $level === 'danger' ? 'fa-circle-exclamation' : ($level === 'warning' ? 'fa-triangle-exclamation' : ($level === 'success' ? 'fa-circle-check' : 'fa-circle-info')) }}"></i></span>
                                <span><strong>{{ data_get($notification->data, 'title', 'Store update') }}</strong><small>{{ str(data_get($notification->data, 'message', ''))->limit(72) }}</small><time>{{ $notification->created_at?->diffForHumans() }}</time></span>
                            </button>
                        </form>
                    @empty
                        <div class="admin-dropdown-empty"><i class="fa-regular fa-bell-slash"></i><p>You have no notifications yet.</p></div>
                    @endforelse
                </div>
            </div>
        @endif

        @php($topbarAdmin = auth('admin')->user())
        <div class="admin-profile-wrapper">
            <button type="button" class="admin-profile-button" id="adminProfileButton" aria-expanded="false">
                <span class="admin-profile-avatar">{{ strtoupper(substr($topbarAdmin?->name ?? 'A', 0, 1)) }}</span>
                <span class="admin-profile-details"><strong>{{ $topbarAdmin?->name ?? 'Administrator' }}</strong><small>{{ $topbarAdmin?->isSuperAdmin() ? 'Super Administrator' : 'Administrator' }}</small></span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="admin-dropdown admin-profile-dropdown" id="adminProfileDropdown">
                <div class="admin-profile-dropdown-header"><strong>{{ $topbarAdmin?->name ?? 'Administrator' }}</strong><span>{{ $topbarAdmin?->email }}</span></div>
                <a href="{{ route('admin.profile.edit') }}"><i class="fa-regular fa-user"></i>Edit Profile</a>
                <a href="{{ route('home-page') }}" target="_blank" rel="noopener"><i class="fa-solid fa-store"></i>View Store</a>
                <form action="{{ route('admin.logout') }}" method="POST">@csrf<button type="submit"><i class="fa-solid fa-right-from-bracket"></i>Logout</button></form>
            </div>
        </div>
    </div>
</header>

<style>
.admin-notification-count{position:absolute;top:-6px;right:-7px;display:grid;min-width:19px;height:19px;place-items:center;padding:0 4px;border:2px solid #fff;border-radius:999px;background:#e11d48;color:#fff;font-size:9px;font-weight:900}.notification-heading{display:flex;align-items:center;justify-content:space-between}.notification-heading>a{color:#0f766e;font-size:11px;font-weight:800;text-decoration:none}.topbar-notification{border-bottom:1px solid #edf0f5}.topbar-notification button{display:grid;width:100%;grid-template-columns:34px 1fr;gap:10px;padding:12px 14px;border:0;background:#fff;color:#172033;text-align:left;cursor:pointer}.topbar-notification.unread button{background:#f0fdfa}.topbar-notification button:hover{background:#f8fafc}.topbar-notification button>span:last-child{min-width:0}.topbar-notification strong,.topbar-notification small,.topbar-notification time{display:block}.topbar-notification strong{font-size:12px}.topbar-notification small{margin-top:3px;color:#64748b;font-size:10px;line-height:1.4}.topbar-notification time{margin-top:4px;color:#94a3b8;font-size:9px}.notification-icon{display:grid;width:32px;height:32px;place-items:center;border-radius:9px;background:#e0f2fe;color:#0369a1}.notification-icon.success{background:#d1fae5;color:#047857}.notification-icon.warning{background:#fef3c7;color:#b45309}.notification-icon.danger{background:#ffe4e6;color:#be123c}.admin-notification-dropdown{max-height:470px;overflow-y:auto}
</style>
