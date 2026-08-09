@extends('admin.layouts.app')
@section('title', 'Notification Center')
@section('page-heading', 'Notification Center')

@section('content')
<div class="notification-page">
    <header class="notification-hero">
        <div><span>Administration</span><h2>Notification Center</h2><p>Review store updates and send important messages to your administrator team.</p></div>
        <div class="hero-actions">
            @if ($stats['unread'] > 0)
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">@csrf @method('PATCH')<button><i class="fa-solid fa-check-double"></i> Mark all read</button></form>
            @endif
            @if ($stats['read'] > 0)
                <form method="POST" action="{{ route('admin.notifications.clear-read') }}" onsubmit="return confirm('Delete all read notifications?')">@csrf @method('DELETE')<button class="secondary"><i class="fa-regular fa-trash-can"></i> Clear read</button></form>
            @endif
        </div>
    </header>

    <section class="notification-stats">
        <a href="{{ route('admin.notifications.index') }}" class="{{ $filter === '' ? 'active' : '' }}"><small>All notifications</small><strong>{{ number_format($stats['all']) }}</strong></a>
        <a href="{{ route('admin.notifications.index', ['status' => 'unread']) }}" class="{{ $filter === 'unread' ? 'active' : '' }}"><small>Unread</small><strong>{{ number_format($stats['unread']) }}</strong></a>
        <a href="{{ route('admin.notifications.index', ['status' => 'read']) }}" class="{{ $filter === 'read' ? 'active' : '' }}"><small>Read</small><strong>{{ number_format($stats['read']) }}</strong></a>
    </section>

    <div class="notification-layout">
        <section class="notification-panel feed-panel">
            <div class="panel-heading"><div><h3>{{ $filter === 'unread' ? 'Unread notifications' : ($filter === 'read' ? 'Read notifications' : 'All notifications') }}</h3><p>{{ number_format($notifications->total()) }} matching update(s)</p></div></div>

            <div class="notification-feed">
                @forelse ($notifications as $notification)
                    @php
                        $level = in_array(data_get($notification->data, 'level'), ['info','success','warning','danger'], true) ? data_get($notification->data, 'level') : 'info';
                        $icon = $level === 'danger' ? 'fa-circle-exclamation' : ($level === 'warning' ? 'fa-triangle-exclamation' : ($level === 'success' ? 'fa-circle-check' : 'fa-circle-info'));
                    @endphp
                    <article class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                        <div class="feed-icon {{ $level }}"><i class="fa-solid {{ $icon }}"></i></div>
                        <div class="feed-content">
                            <div class="feed-title"><h4>{{ data_get($notification->data, 'title', 'Store update') }}</h4>@unless($notification->read_at)<span>New</span>@endunless</div>
                            <p>{{ data_get($notification->data, 'message', 'No message was provided.') }}</p>
                            <time><i class="fa-regular fa-clock"></i> {{ $notification->created_at?->format('d M Y, h:i A') }} · {{ $notification->created_at?->diffForHumans() }}</time>
                        </div>
                        <div class="feed-actions">
                            @if (!$notification->read_at || data_get($notification->data, 'action_url'))
                                <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">@csrf @method('PATCH')<button class="open-button">{{ data_get($notification->data, 'action_label', data_get($notification->data, 'action_url') ? 'Open' : 'Mark read') }}</button></form>
                            @endif
                            <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}" onsubmit="return confirm('Delete this notification?')">@csrf @method('DELETE')<button class="delete-button" aria-label="Delete notification"><i class="fa-regular fa-trash-can"></i></button></form>
                        </div>
                    </article>
                @empty
                    <div class="notification-empty"><i class="fa-regular fa-bell-slash"></i><h3>No notifications found</h3><p>New store and team updates will appear here.</p></div>
                @endforelse
            </div>

            @if ($notifications->hasPages())<div class="notification-pagination">{{ $notifications->links() }}</div>@endif
        </section>

        <aside class="notification-panel compose-panel">
            <div class="panel-heading"><div><h3>Send team notification</h3><p>Deliver an update to active administrators.</p></div></div>
            <form method="POST" action="{{ route('admin.notifications.send') }}" class="compose-form">
                @csrf
                <label>Recipients<select name="recipient" id="notificationRecipient"><option value="all" @selected(old('recipient', 'all') === 'all')>All active administrators</option><option value="specific" @selected(old('recipient') === 'specific')>Selected administrators</option></select></label>
                <div id="specificAdminFields" class="specific-admins">
                    <span>Select administrators</span>
                    @foreach($admins as $admin)
                        <label class="admin-choice"><input type="checkbox" name="user_ids[]" value="{{ $admin->id }}" @checked(in_array($admin->id, old('user_ids', [])))><span><strong>{{ $admin->name }}</strong><small>{{ $admin->email }}</small></span></label>
                    @endforeach
                </div>
                <label>Title<input name="title" value="{{ old('title') }}" maxlength="120" required placeholder="Example: Scheduled maintenance"></label>
                <label>Message<textarea name="message" rows="5" maxlength="1000" required placeholder="Write the administrator update here...">{{ old('message') }}</textarea></label>
                <label>Importance<select name="level"><option value="info" @selected(old('level') === 'info')>Information</option><option value="success" @selected(old('level') === 'success')>Success</option><option value="warning" @selected(old('level') === 'warning')>Warning</option><option value="danger" @selected(old('level') === 'danger')>Urgent</option></select></label>
                <label>Internal action URL <small>Optional</small><input name="action_url" value="{{ old('action_url') }}" placeholder="/admin/orders"></label>
                <label>Action label <small>Optional</small><input name="action_label" value="{{ old('action_label') }}" maxlength="60" placeholder="View orders"></label>
                <button type="submit" class="send-button"><i class="fa-regular fa-paper-plane"></i> Send notification</button>
            </form>
        </aside>
    </div>
</div>
@endsection

@push('page-styles')
<style>
.notification-page{display:grid;gap:19px;color:#172033}.notification-hero{display:flex;align-items:center;justify-content:space-between;gap:22px;padding:27px;border-radius:20px;background:linear-gradient(135deg,#172554,#1d4ed8);color:#fff}.notification-hero>div>span{color:#bfdbfe;font-size:10px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.notification-hero h2{margin:6px 0 0;font-size:30px}.notification-hero p{margin:7px 0 0;color:#dbeafe}.hero-actions{display:flex;flex-wrap:wrap;gap:8px}.hero-actions button{padding:10px 13px;border:0;border-radius:9px;background:#fff;color:#1d4ed8;font-weight:800;cursor:pointer}.hero-actions button.secondary{background:#dbeafe}.notification-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:13px}.notification-stats a{padding:16px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:inherit;text-decoration:none}.notification-stats a.active{border-color:#60a5fa;box-shadow:0 0 0 3px #dbeafe}.notification-stats small,.notification-stats strong{display:block}.notification-stats small{color:#64748b}.notification-stats strong{margin-top:5px;font-size:23px}.notification-layout{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(310px,.8fr);align-items:start;gap:17px}.notification-panel{overflow:hidden;border:1px solid #e2e8f0;border-radius:17px;background:#fff}.panel-heading{padding:17px 19px;border-bottom:1px solid #e2e8f0}.panel-heading h3{margin:0;font-size:17px}.panel-heading p{margin:4px 0 0;color:#64748b;font-size:12px}.notification-item{display:grid;grid-template-columns:42px 1fr auto;gap:13px;padding:17px 19px;border-bottom:1px solid #edf0f5}.notification-item.unread{background:#f0f9ff}.feed-icon{display:grid;width:40px;height:40px;place-items:center;border-radius:11px;background:#e0f2fe;color:#0369a1}.feed-icon.success{background:#d1fae5;color:#047857}.feed-icon.warning{background:#fef3c7;color:#b45309}.feed-icon.danger{background:#ffe4e6;color:#be123c}.feed-title{display:flex;align-items:center;gap:7px}.feed-title h4{margin:0;font-size:14px}.feed-title span{padding:3px 6px;border-radius:999px;background:#2563eb;color:#fff;font-size:8px;font-weight:900;text-transform:uppercase}.feed-content p{margin:6px 0;color:#475569;font-size:12px;line-height:1.6}.feed-content time{color:#94a3b8;font-size:10px}.feed-actions{display:flex;align-items:center;gap:6px}.feed-actions button{border:0;cursor:pointer}.open-button{padding:8px 10px;border-radius:8px!important;background:#dbeafe;color:#1d4ed8;font-weight:800}.delete-button{display:grid;width:33px;height:33px;place-items:center;border-radius:8px;background:#fff1f2;color:#be123c}.notification-empty{padding:55px 20px;text-align:center;color:#64748b}.notification-empty i{font-size:36px;color:#cbd5e1}.notification-empty h3{margin:12px 0 4px;color:#334155}.notification-empty p{margin:0}.notification-pagination{padding:15px 18px}.compose-form{display:grid;gap:14px;padding:18px}.compose-form>label,.specific-admins>span{display:block;color:#334155;font-size:11px;font-weight:800}.compose-form label small{color:#94a3b8;font-weight:500}.compose-form input,.compose-form select,.compose-form textarea{width:100%;box-sizing:border-box;margin-top:6px;padding:10px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;font:inherit}.compose-form textarea{resize:vertical}.specific-admins{display:none;max-height:220px;overflow-y:auto;padding:10px;border:1px solid #e2e8f0;border-radius:10px}.specific-admins.visible{display:block}.admin-choice{display:flex!important;align-items:center;gap:9px;margin-top:9px;font-weight:500!important}.admin-choice input{width:auto;margin:0}.admin-choice strong,.admin-choice small{display:block}.admin-choice small{margin-top:2px}.send-button{padding:11px;border:0;border-radius:9px;background:#1d4ed8;color:#fff;font-weight:850;cursor:pointer}
@media(max-width:1000px){.notification-layout{grid-template-columns:1fr}}@media(max-width:680px){.notification-hero{align-items:stretch;flex-direction:column}.notification-stats{grid-template-columns:1fr}.notification-item{grid-template-columns:38px 1fr}.feed-actions{grid-column:2;justify-content:flex-start}.hero-actions{flex-direction:column}.hero-actions button{width:100%}}
</style>
@endpush

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const recipient = document.getElementById('notificationRecipient');
    const fields = document.getElementById('specificAdminFields');
    function toggleRecipients() { fields.classList.toggle('visible', recipient.value === 'specific'); }
    recipient.addEventListener('change', toggleRecipients);
    toggleRecipients();
});
</script>
@endpush
