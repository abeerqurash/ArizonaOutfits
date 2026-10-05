<li>
    <a
        href="{{ route('admin.inventory-alerts.index') }}"
        class="{{ request()->routeIs('admin.inventory-alerts.*') ? 'active' : '' }}"
    >
        <span>Inventory Alerts</span>

        @if(($activeInventoryAlertCount ?? 0) > 0)
            <span class="inventory-alert-count">
                {{ $activeInventoryAlertCount > 99 ? '99+' : $activeInventoryAlertCount }}
            </span>
        @endif
    </a>
</li>