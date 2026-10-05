
<?php $__env->startSection('title', 'Audit Logs'); ?>
<?php $__env->startSection('content'); ?>
<div class="audit-page">
    <header class="audit-header">
        <div><span>Administration</span><h1>Activity & Audit Logs</h1><p>Review administrator changes, failed actions, affected records, and request details.</p></div>
        <a href="<?php echo e(route('admin.audit-logs.export', request()->query())); ?>"><i class="fa-solid fa-file-csv"></i> Export Filtered CSV</a>
    </header>

    <section class="audit-stats">
        <article><small>Actions today</small><strong><?php echo e(number_format($stats['today'])); ?></strong></article>
        <article><small>Last 7 days</small><strong><?php echo e(number_format($stats['seven_days'])); ?></strong></article>
        <article><small>Failed in 7 days</small><strong class="failed-value"><?php echo e(number_format($stats['failed'])); ?></strong></article>
        <article><small>Active administrators</small><strong><?php echo e(number_format($stats['active_admins'])); ?></strong></article>
    </section>

    <section class="audit-panel filters-panel">
        <form method="GET" action="<?php echo e(route('admin.audit-logs.index')); ?>" class="audit-filters">
            <div class="field wide"><label>Search</label><input name="search" value="<?php echo e(request('search')); ?>" placeholder="Action, administrator, route or IP address"></div>
            <div class="field"><label>Administrator</label><select name="user_id"><option value="">All administrators</option><?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($admin->id); ?>" <?php if((string)request('user_id')===(string)$admin->id): echo 'selected'; endif; ?>><?php echo e($admin->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
            <div class="field"><label>Action</label><select name="action"><option value="">All actions</option><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($action); ?>" <?php if(request('action')===$action): echo 'selected'; endif; ?>><?php echo e(str($action)->headline()); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
            <div class="field"><label>Outcome</label><select name="outcome"><option value="">All outcomes</option><option value="success" <?php if(request('outcome')==='success'): echo 'selected'; endif; ?>>Success</option><option value="failed" <?php if(request('outcome')==='failed'): echo 'selected'; endif; ?>>Failed</option></select></div>
            <div class="field"><label>From</label><input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"></div>
            <div class="field"><label>To</label><input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"></div>
            <div class="filter-actions"><button type="submit"><i class="fa-solid fa-filter"></i> Apply Filters</button><a href="<?php echo e(route('admin.audit-logs.index')); ?>">Reset</a></div>
        </form>
    </section>

    <section class="audit-panel">
        <div class="audit-panel-heading"><div><h2>Recorded activity</h2><p><?php echo e(number_format($logs->total())); ?> matching audit entries</p></div><span>Passwords, tokens, payment details, and bank details are automatically redacted.</span></div>
        <div class="audit-table-wrap">
            <table class="audit-table">
                <thead><tr><th>Date</th><th>Administrator</th><th>Action</th><th>Record</th><th>Request</th><th>Outcome</th><th></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e(optional($log->created_at)->format('d M Y')); ?></strong><small><?php echo e(optional($log->created_at)->format('H:i:s')); ?></small></td>
                            <td><strong><?php echo e($log->actor_name); ?></strong><small><?php echo e($log->user?->email ?: 'Deleted account'); ?></small></td>
                            <td><strong><?php echo e(str($log->action)->headline()); ?></strong><small><?php echo e($log->route_name ?: 'Unnamed route'); ?></small></td>
                            <td><?php if($log->auditable_type): ?><strong><?php echo e(class_basename($log->auditable_type)); ?></strong><small>#<?php echo e($log->auditable_id); ?></small><?php else: ?><span class="muted">Not detected</span><?php endif; ?></td>
                            <td><strong><?php echo e($log->method); ?> · <?php echo e($log->status_code); ?></strong><small><?php echo e($log->ip_address ?: 'Unknown IP'); ?></small></td>
                            <td><span class="outcome <?php echo e($log->outcome); ?>"><?php echo e(ucfirst($log->outcome)); ?></span></td>
                            <td><details class="audit-details"><summary aria-label="View audit details"><i class="fa-solid fa-chevron-down"></i></summary><div class="detail-popover"><h3><?php echo e($log->description); ?></h3><dl><div><dt>URL</dt><dd><?php echo e($log->url); ?></dd></div><div><dt>User agent</dt><dd><?php echo e($log->user_agent ?: 'Unknown'); ?></dd></div></dl><strong>Sanitized request data</strong><pre><?php echo e(json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre></div></details></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7"><div class="audit-empty"><i class="fa-solid fa-clipboard-check"></i><h3>No audit entries found</h3><p>Try removing filters or perform an administrator update.</p></div></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($logs->hasPages()): ?><div class="audit-pagination"><?php echo e($logs->links()); ?></div><?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('page-styles'); ?>
<style>
.audit-page{display:grid;gap:20px;color:#172033}.audit-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:27px;border-radius:20px;background:linear-gradient(135deg,#111827,#0f766e);color:#fff}.audit-header span{color:#99f6e4;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.audit-header h1{margin:6px 0 0}.audit-header p{margin:7px 0 0;color:#ccfbf1}.audit-header>a{padding:10px 13px;border-radius:9px;background:#fff;color:#115e59;text-decoration:none;font-weight:800}.audit-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.audit-stats article{padding:17px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.audit-stats small{display:block;color:#64748b}.audit-stats strong{display:block;margin-top:5px;font-size:23px}.failed-value{color:#be123c}.audit-panel{overflow:visible;border:1px solid #e2e8f0;border-radius:17px;background:#fff}.filters-panel{overflow:hidden}.audit-filters{display:grid;grid-template-columns:2fr repeat(5,1fr);align-items:end;gap:12px;padding:17px}.field label{display:block;margin-bottom:6px;font-size:11px;font-weight:800}.field input,.field select{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:9px;padding:9px;font:inherit}.filter-actions{display:flex;gap:7px;grid-column:1/-1;justify-content:flex-end}.filter-actions button,.filter-actions a{border:0;border-radius:8px;padding:9px 12px;font:inherit;font-weight:800;text-decoration:none}.filter-actions button{background:#0f766e;color:#fff}.filter-actions a{background:#f1f5f9;color:#475569}.audit-panel-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:17px 19px;border-bottom:1px solid #e2e8f0}.audit-panel-heading h2{margin:0;font-size:18px}.audit-panel-heading p{margin:4px 0 0;color:#64748b;font-size:12px}.audit-panel-heading>span{max-width:390px;color:#64748b;font-size:11px;text-align:right}.audit-table-wrap{overflow-x:auto}.audit-table{width:100%;min-width:1050px;border-collapse:collapse}.audit-table th{padding:11px 13px;background:#f8fafc;color:#64748b;font-size:10px;text-align:left;text-transform:uppercase}.audit-table td{position:relative;padding:13px;border-top:1px solid #e2e8f0;vertical-align:middle}.audit-table td strong,.audit-table td small{display:block}.audit-table td small{margin-top:3px;color:#64748b}.muted{color:#94a3b8}.outcome{padding:5px 8px;border-radius:999px;font-size:10px;font-weight:850}.outcome.success{background:#d1fae5;color:#047857}.outcome.failed{background:#ffe4e6;color:#be123c}.audit-details summary{display:grid;place-items:center;width:32px;height:32px;border-radius:8px;background:#f1f5f9;color:#475569;cursor:pointer;list-style:none}.detail-popover{position:absolute;z-index:20;right:13px;top:52px;width:min(520px,80vw);padding:16px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;box-shadow:0 18px 45px rgba(15,23,42,.2)}.detail-popover h3{margin:0 0 12px;font-size:14px}.detail-popover dl{margin:0 0 12px}.detail-popover dl>div{margin-top:7px}.detail-popover dt{color:#64748b;font-size:10px;font-weight:800;text-transform:uppercase}.detail-popover dd{margin:2px 0 0;overflow-wrap:anywhere;font-size:11px}.detail-popover pre{max-height:300px;overflow:auto;margin:7px 0 0;padding:11px;border-radius:8px;background:#0f172a;color:#e2e8f0;font-size:10px;white-space:pre-wrap}.audit-empty{text-align:center;padding:45px;color:#64748b}.audit-empty i{font-size:32px;color:#cbd5e1}.audit-empty h3{margin:11px 0 4px;color:#334155}.audit-pagination{padding:0 18px 18px}
@media(max-width:1100px){.audit-filters{grid-template-columns:repeat(3,1fr)}.field.wide{grid-column:span 2}}@media(max-width:680px){.audit-header,.audit-panel-heading{align-items:stretch;flex-direction:column}.audit-stats,.audit-filters{grid-template-columns:1fr}.field.wide{grid-column:auto}.audit-header>a{text-align:center}.audit-panel-heading>span{text-align:left}}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/audit-logs/index.blade.php ENDPATH**/ ?>