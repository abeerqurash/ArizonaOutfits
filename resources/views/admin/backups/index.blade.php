@extends('admin.layouts.app')
@section('title', 'Backup Center')
@section('page-heading', 'Backup Center')

@section('content')
<div class="backup-page">
    <header class="backup-hero">
        <div><span>System protection</span><h2>Backup & Export Center</h2><p>Create private, downloadable snapshots of your store database and uploaded files.</p></div>
        <button type="button" onclick="document.getElementById('createBackupPanel').scrollIntoView({behavior:'smooth'})"><i class="fa-solid fa-shield-halved"></i> Create backup</button>
    </header>

    <section class="backup-stats">
        <article><small>Completed backups</small><strong>{{ number_format($stats['count']) }}</strong></article>
        <article><small>Storage used</small><strong>{{ \App\Http\Controllers\Admin\AdminBackupController::formatBytes($stats['size']) }}</strong></article>
        <article><small>Latest backup</small><strong class="date-value">{{ $stats['latest']?->completed_at?->diffForHumans() ?? 'Never' }}</strong></article>
        <article><small>Failed attempts</small><strong class="{{ $stats['failed'] ? 'danger-value' : '' }}">{{ number_format($stats['failed']) }}</strong></article>
    </section>

    <div class="backup-grid">
        <section class="backup-panel" id="createBackupPanel">
            <div class="panel-heading"><div><h3>Create a protected backup</h3><p>Backup creation may take a few minutes on a large store. Keep this page open until it finishes.</p></div></div>
            <form method="POST" action="{{ route('admin.backups.store') }}" class="backup-options" id="backupForm">
                @csrf
                <label class="backup-option"><input type="radio" name="type" value="database" checked><span class="option-icon"><i class="fa-solid fa-database"></i></span><span><strong>Database backup</strong><small>Orders, products, customers, settings, inventory and all other database records.</small></span></label>
                <label class="backup-option"><input type="radio" name="type" value="full"><span class="option-icon purple"><i class="fa-solid fa-box-archive"></i></span><span><strong>Full store backup</strong><small>Database plus files from storage/app/public and public/uploads, packaged as ZIP.</small></span></label>
                <div class="backup-warning"><i class="fa-solid fa-lock"></i><span><strong>Stored privately</strong> Backups are saved under storage/app/private/admin-backups and require administrator authentication to download.</span></div>
                <button type="submit" class="create-button" id="createBackupButton"><i class="fa-solid fa-rotate"></i> Create selected backup</button>
            </form>
        </section>

        <aside class="backup-panel cleanup-panel">
            <div class="panel-heading"><div><h3>Storage cleanup</h3><p>Permanently remove backups older than your selected age.</p></div></div>
            <form method="POST" action="{{ route('admin.backups.cleanup') }}" onsubmit="return confirm('Permanently delete all backups older than this age?')">
                @csrf
                <label>Delete backups older than<select name="days"><option value="7">7 days</option><option value="14">14 days</option><option value="30" selected>30 days</option><option value="60">60 days</option><option value="90">90 days</option></select></label>
                <button type="submit"><i class="fa-regular fa-trash-can"></i> Run cleanup</button>
            </form>
            <div class="restore-note"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>Restoring a backup</strong><br>Restore is intentionally a server-level operation. Test every backup on a staging copy before replacing production data.</p></div>
        </aside>
    </div>

    <section class="backup-panel history-panel">
        <div class="panel-heading"><div><h3>Backup history</h3><p>{{ number_format($backups->total()) }} recorded backup attempt(s)</p></div></div>
        <div class="table-wrap"><table><thead><tr><th>Backup</th><th>Type</th><th>Created by</th><th>Contents</th><th>Size</th><th>Status</th><th>Created</th><th></th></tr></thead><tbody>
            @forelse($backups as $backup)
                <tr>
                    <td><strong>{{ $backup->name }}</strong><small>#{{ $backup->id }}</small></td>
                    <td><span class="type-badge {{ $backup->type }}">{{ ucfirst($backup->type) }}</span></td>
                    <td><strong>{{ $backup->creator?->name ?? 'Deleted administrator' }}</strong><small>{{ $backup->creator?->email }}</small></td>
                    <td><strong>{{ number_format($backup->table_count) }} tables</strong><small>{{ number_format($backup->row_count) }} rows</small></td>
                    <td>{{ $backup->status === 'completed' ? $backup->formatted_size : '—' }}</td>
                    <td><span class="status-badge {{ $backup->status }}">{{ ucfirst($backup->status) }}</span>@if($backup->error_message)<details><summary>Error details</summary><p>{{ $backup->error_message }}</p></details>@endif</td>
                    <td><strong>{{ $backup->created_at?->format('d M Y') }}</strong><small>{{ $backup->created_at?->format('h:i A') }}</small></td>
                    <td><div class="row-actions">@if($backup->status === 'completed')<a href="{{ route('admin.backups.download', $backup) }}" title="Download"><i class="fa-solid fa-download"></i></a>@endif<form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" onsubmit="return confirm('Permanently delete this backup?')">@csrf @method('DELETE')<button title="Delete"><i class="fa-regular fa-trash-can"></i></button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-box-open"></i><h3>No backups created yet</h3><p>Create your first database or full store backup above.</p></div></td></tr>
            @endforelse
        </tbody></table></div>
        @if($backups->hasPages())<div class="backup-pagination">{{ $backups->links() }}</div>@endif
    </section>
</div>
@endsection

@push('page-styles')
<style>
.backup-page{display:grid;gap:19px;color:#172033}.backup-hero{display:flex;align-items:center;justify-content:space-between;gap:22px;padding:28px;border-radius:20px;background:linear-gradient(135deg,#172033,#4338ca);color:#fff}.backup-hero span{color:#c7d2fe;font-size:10px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.backup-hero h2{margin:6px 0 0;font-size:30px}.backup-hero p{margin:7px 0 0;color:#e0e7ff}.backup-hero button{padding:11px 14px;border:0;border-radius:10px;background:#fff;color:#3730a3;font-weight:850;cursor:pointer}.backup-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:13px}.backup-stats article{padding:17px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.backup-stats small,.backup-stats strong{display:block}.backup-stats small{color:#64748b}.backup-stats strong{margin-top:6px;font-size:23px}.backup-stats .date-value{font-size:17px}.danger-value{color:#be123c}.backup-grid{display:grid;grid-template-columns:1.5fr .8fr;align-items:start;gap:17px}.backup-panel{overflow:hidden;border:1px solid #e2e8f0;border-radius:17px;background:#fff}.panel-heading{padding:17px 19px;border-bottom:1px solid #e2e8f0}.panel-heading h3{margin:0;font-size:17px}.panel-heading p{margin:4px 0 0;color:#64748b;font-size:12px}.backup-options{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:18px}.backup-option{display:grid;grid-template-columns:auto 42px 1fr;align-items:start;gap:10px;padding:14px;border:1px solid #cbd5e1;border-radius:12px;cursor:pointer}.backup-option:has(input:checked){border-color:#6366f1;background:#eef2ff;box-shadow:0 0 0 2px #e0e7ff}.backup-option input{margin-top:13px}.option-icon{display:grid;width:40px;height:40px;place-items:center;border-radius:10px;background:#dbeafe;color:#1d4ed8}.option-icon.purple{background:#ede9fe;color:#6d28d9}.backup-option strong,.backup-option small{display:block}.backup-option small{margin-top:5px;color:#64748b;line-height:1.5}.backup-warning{display:flex;grid-column:1/-1;gap:10px;padding:12px;border-radius:10px;background:#ecfdf5;color:#065f46;font-size:11px;line-height:1.5}.create-button{grid-column:1/-1;padding:12px;border:0;border-radius:10px;background:#4f46e5;color:#fff;font-weight:850;cursor:pointer}.create-button:disabled{opacity:.65;cursor:wait}.cleanup-panel form{display:grid;gap:12px;padding:18px}.cleanup-panel label{font-size:11px;font-weight:800}.cleanup-panel select{width:100%;margin-top:7px;padding:10px;border:1px solid #cbd5e1;border-radius:9px}.cleanup-panel button{padding:10px;border:0;border-radius:9px;background:#fff1f2;color:#be123c;font-weight:800;cursor:pointer}.restore-note{display:flex;gap:10px;margin:0 18px 18px;padding:13px;border-radius:10px;background:#fffbeb;color:#92400e;font-size:11px;line-height:1.5}.restore-note p{margin:0}.table-wrap{overflow-x:auto}table{width:100%;min-width:1050px;border-collapse:collapse}th{padding:11px 13px;background:#f8fafc;color:#64748b;font-size:10px;text-align:left;text-transform:uppercase}td{padding:13px;border-top:1px solid #edf0f5;font-size:12px;vertical-align:middle}td strong,td small{display:block}td small{margin-top:3px;color:#64748b}.type-badge,.status-badge{padding:5px 8px;border-radius:999px;font-size:9px;font-weight:900;text-transform:uppercase}.type-badge{background:#e0e7ff;color:#4338ca}.type-badge.full{background:#ede9fe;color:#6d28d9}.status-badge.completed{background:#d1fae5;color:#047857}.status-badge.processing{background:#dbeafe;color:#1d4ed8}.status-badge.failed{background:#ffe4e6;color:#be123c}details{margin-top:6px;color:#be123c;font-size:10px}details p{max-width:270px;overflow-wrap:anywhere}.row-actions{display:flex;gap:6px}.row-actions a,.row-actions button{display:grid;width:33px;height:33px;place-items:center;border:0;border-radius:8px;background:#eef2ff;color:#4338ca;cursor:pointer}.row-actions button{background:#fff1f2;color:#be123c}.empty-state{padding:50px;text-align:center;color:#64748b}.empty-state i{font-size:34px;color:#cbd5e1}.empty-state h3{margin:11px 0 4px;color:#334155}.empty-state p{margin:0}.backup-pagination{padding:15px 18px}
@media(max-width:1000px){.backup-grid{grid-template-columns:1fr}}@media(max-width:700px){.backup-hero{align-items:stretch;flex-direction:column}.backup-stats,.backup-options{grid-template-columns:1fr}.backup-warning,.create-button{grid-column:auto}}
</style>
@endpush

@push('page-scripts')
<script>
document.getElementById('backupForm')?.addEventListener('submit', function () {
    const button = document.getElementById('createBackupButton');
    button.disabled = true;
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating backup—please wait';
});
</script>
@endpush
