@extends('admin.layouts.app')
@section('title', 'Audit Logs')
@section('content')
<div class="audit-page">
    <header class="audit-header">
        <div><span>Administration</span><h1>Activity & Audit Logs</h1><p>Review administrator changes, failed actions, affected records, and request details.</p></div>
        <a href="{{ route('admin.audit-logs.export', request()->query()) }}"><i class="fa-solid fa-file-csv"></i> Export Filtered CSV</a>
    </header>

    <section class="audit-stats">
        <article><small>Actions today</small><strong>{{ number_format($stats['today']) }}</strong></article>
        <article><small>Last 7 days</small><strong>{{ number_format($stats['seven_days']) }}</strong></article>
        <article><small>Failed in 7 days</small><strong class="failed-value">{{ number_format($stats['failed']) }}</strong></article>
        <article><small>Active administrators</small><strong>{{ number_format($stats['active_admins']) }}</strong></article>
    </section>

    <section class="audit-panel filters-panel">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="audit-filters">
            <div class="field wide"><label>Search</label><input name="search" value="{{ request('search') }}" placeholder="Action, administrator, route or IP address"></div>
            <div class="field"><label>Administrator</label><select name="user_id"><option value="">All administrators</option>@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected((string)request('user_id')===(string)$admin->id)>{{ $admin->name }}</option>@endforeach</select></div>
            <div class="field"><label>Action</label><select name="action"><option value="">All actions</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action')===$action)>{{ str($action)->headline() }}</option>@endforeach</select></div>
            <div class="field"><label>Outcome</label><select name="outcome"><option value="">All outcomes</option><option value="success" @selected(request('outcome')==='success')>Success</option><option value="failed" @selected(request('outcome')==='failed')>Failed</option></select></div>
            <div class="field"><label>From</label><input type="date" name="date_from" value="{{ request('date_from') }}"></div>
            <div class="field"><label>To</label><input type="date" name="date_to" value="{{ request('date_to') }}"></div>
            <div class="filter-actions"><button type="submit"><i class="fa-solid fa-filter"></i> Apply Filters</button><a href="{{ route('admin.audit-logs.index') }}">Reset</a></div>
        </form>
    </section>

    <section class="audit-panel">
        <div class="audit-panel-heading"><div><h2>Recorded activity</h2><p>{{ number_format($logs->total()) }} matching audit entries</p></div><span>Passwords, tokens, payment details, and bank details are automatically redacted.</span></div>
        <div class="audit-table-wrap">
            <table class="audit-table">
                <thead><tr><th>Date</th><th>Administrator</th><th>Action</th><th>Record</th><th>Request</th><th>Outcome</th><th></th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td><strong>{{ optional($log->created_at)->format('d M Y') }}</strong><small>{{ optional($log->created_at)->format('H:i:s') }}</small></td>
                            <td><strong>{{ $log->actor_name }}</strong><small>{{ $log->user?->email ?: 'Deleted account' }}</small></td>
                            <td><strong>{{ str($log->action)->headline() }}</strong><small>{{ $log->route_name ?: 'Unnamed route' }}</small></td>
                            <td>@if($log->auditable_type)<strong>{{ class_basename($log->auditable_type) }}</strong><small>#{{ $log->auditable_id }}</small>@else<span class="muted">Not detected</span>@endif</td>
                            <td><strong>{{ $log->method }} · {{ $log->status_code }}</strong><small>{{ $log->ip_address ?: 'Unknown IP' }}</small></td>
                            <td><span class="outcome {{ $log->outcome }}">{{ ucfirst($log->outcome) }}</span></td>
                            <td><details class="audit-details"><summary aria-label="View audit details"><i class="fa-solid fa-chevron-down"></i></summary><div class="detail-popover"><h3>{{ $log->description }}</h3><dl><div><dt>URL</dt><dd>{{ $log->url }}</dd></div><div><dt>User agent</dt><dd>{{ $log->user_agent ?: 'Unknown' }}</dd></div></dl><strong>Sanitized request data</strong><pre>{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></div></details></td>
                        </tr>
                    @empty<tr><td colspan="7"><div class="audit-empty"><i class="fa-solid fa-clipboard-check"></i><h3>No audit entries found</h3><p>Try removing filters or perform an administrator update.</p></div></td></tr>@endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())<div class="audit-pagination">{{ $logs->links() }}</div>@endif
    </section>
</div>
@endsection
@push('page-styles')
<style>
.audit-page{display:grid;gap:20px;color:#172033}.audit-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:27px;border-radius:20px;background:linear-gradient(135deg,#111827,#0f766e);color:#fff}.audit-header span{color:#99f6e4;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.audit-header h1{margin:6px 0 0}.audit-header p{margin:7px 0 0;color:#ccfbf1}.audit-header>a{padding:10px 13px;border-radius:9px;background:#fff;color:#115e59;text-decoration:none;font-weight:800}.audit-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.audit-stats article{padding:17px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.audit-stats small{display:block;color:#64748b}.audit-stats strong{display:block;margin-top:5px;font-size:23px}.failed-value{color:#be123c}.audit-panel{overflow:visible;border:1px solid #e2e8f0;border-radius:17px;background:#fff}.filters-panel{overflow:hidden}.audit-filters{display:grid;grid-template-columns:2fr repeat(5,1fr);align-items:end;gap:12px;padding:17px}.field label{display:block;margin-bottom:6px;font-size:11px;font-weight:800}.field input,.field select{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:9px;padding:9px;font:inherit}.filter-actions{display:flex;gap:7px;grid-column:1/-1;justify-content:flex-end}.filter-actions button,.filter-actions a{border:0;border-radius:8px;padding:9px 12px;font:inherit;font-weight:800;text-decoration:none}.filter-actions button{background:#0f766e;color:#fff}.filter-actions a{background:#f1f5f9;color:#475569}.audit-panel-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:17px 19px;border-bottom:1px solid #e2e8f0}.audit-panel-heading h2{margin:0;font-size:18px}.audit-panel-heading p{margin:4px 0 0;color:#64748b;font-size:12px}.audit-panel-heading>span{max-width:390px;color:#64748b;font-size:11px;text-align:right}.audit-table-wrap{overflow-x:auto}.audit-table{width:100%;min-width:1050px;border-collapse:collapse}.audit-table th{padding:11px 13px;background:#f8fafc;color:#64748b;font-size:10px;text-align:left;text-transform:uppercase}.audit-table td{position:relative;padding:13px;border-top:1px solid #e2e8f0;vertical-align:middle}.audit-table td strong,.audit-table td small{display:block}.audit-table td small{margin-top:3px;color:#64748b}.muted{color:#94a3b8}.outcome{padding:5px 8px;border-radius:999px;font-size:10px;font-weight:850}.outcome.success{background:#d1fae5;color:#047857}.outcome.failed{background:#ffe4e6;color:#be123c}.audit-details summary{display:grid;place-items:center;width:32px;height:32px;border-radius:8px;background:#f1f5f9;color:#475569;cursor:pointer;list-style:none}.detail-popover{position:absolute;z-index:20;right:13px;top:52px;width:min(520px,80vw);padding:16px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;box-shadow:0 18px 45px rgba(15,23,42,.2)}.detail-popover h3{margin:0 0 12px;font-size:14px}.detail-popover dl{margin:0 0 12px}.detail-popover dl>div{margin-top:7px}.detail-popover dt{color:#64748b;font-size:10px;font-weight:800;text-transform:uppercase}.detail-popover dd{margin:2px 0 0;overflow-wrap:anywhere;font-size:11px}.detail-popover pre{max-height:300px;overflow:auto;margin:7px 0 0;padding:11px;border-radius:8px;background:#0f172a;color:#e2e8f0;font-size:10px;white-space:pre-wrap}.audit-empty{text-align:center;padding:45px;color:#64748b}.audit-empty i{font-size:32px;color:#cbd5e1}.audit-empty h3{margin:11px 0 4px;color:#334155}.audit-pagination{padding:0 18px 18px}
@media(max-width:1100px){.audit-filters{grid-template-columns:repeat(3,1fr)}.field.wide{grid-column:span 2}}@media(max-width:680px){.audit-header,.audit-panel-heading{align-items:stretch;flex-direction:column}.audit-stats,.audit-filters{grid-template-columns:1fr}.field.wide{grid-column:auto}.audit-header>a{text-align:center}.audit-panel-heading>span{text-align:left}}
</style>
@endpush
