

<?php $__env->startSection('title', 'Admin Team'); ?>

<?php $__env->startSection('content'); ?>
<div class="team-page">
    <header class="team-header">
        <div><span>Administration</span><h1>Admin Team</h1><p>Create administrators, promote existing users, and assign access roles.</p></div>
        <a href="<?php echo e(route('admin.admin-roles.index')); ?>" class="team-button secondary"><i class="fa-solid fa-user-shield"></i> Roles & Permissions</a>
    </header>

    <?php if(session('success')): ?><div class="team-alert success"><i class="fa-solid fa-circle-check"></i><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="team-alert error"><i class="fa-solid fa-circle-exclamation"></i><?php echo e(session('error')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="team-alert error"><i class="fa-solid fa-circle-exclamation"></i><div><strong>Please correct these fields:</strong><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div></div><?php endif; ?>

    <section class="team-stats">
        <article><small>Total administrators</small><strong><?php echo e(number_format($stats['total'])); ?></strong></article>
        <article><small>Active</small><strong><?php echo e(number_format($stats['active'])); ?></strong></article>
        <article><small>Super administrators</small><strong><?php echo e(number_format($stats['super_admins'])); ?></strong></article>
        <article><small>Available roles</small><strong><?php echo e(number_format($stats['roles'])); ?></strong></article>
    </section>

    <section class="team-panel">
        <div class="team-panel-heading"><span><i class="fa-solid fa-user-plus"></i></span><div><h2>Add administrator</h2><p>Create a new account or promote an existing customer account.</p></div></div>
        <form method="POST" action="<?php echo e(route('admin.admin-users.store')); ?>" class="team-form" id="adminCreateForm">
            <?php echo csrf_field(); ?>
            <div class="field"><label for="admin_mode">Account source</label><select id="admin_mode" name="mode"><option value="create" <?php if(old('mode','create')==='create'): echo 'selected'; endif; ?>>Create new account</option><option value="promote" <?php if(old('mode')==='promote'): echo 'selected'; endif; ?>>Promote existing user</option></select></div>
            <div class="field promote-field"><label for="existing_user_id">Existing user</label><select id="existing_user_id" name="existing_user_id"><option value="">Select user</option><?php $__currentLoopData = $availableUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($user->id); ?>" <?php if((string)old('existing_user_id')===(string)$user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?> — <?php echo e($user->email); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
            <div class="field create-field"><label for="admin_name">Name</label><input id="admin_name" name="name" value="<?php echo e(old('name')); ?>" maxlength="255"></div>
            <div class="field create-field"><label for="admin_email">Email</label><input id="admin_email" type="email" name="email" value="<?php echo e(old('email')); ?>" maxlength="255"></div>
            <div class="field create-field"><label for="admin_phone">Phone</label><input id="admin_phone" name="phone" value="<?php echo e(old('phone')); ?>" maxlength="50"></div>
            <div class="field create-field"><label for="admin_password">Password</label><input id="admin_password" type="password" name="password" minlength="8"></div>
            <div class="field create-field"><label for="admin_password_confirmation">Confirm password</label><input id="admin_password_confirmation" type="password" name="password_confirmation" minlength="8"></div>
            <div class="field"><label for="admin_status">Status</label><select id="admin_status" name="status"><option value="active" <?php if(old('status','active')==='active'): echo 'selected'; endif; ?>>Active</option><option value="inactive" <?php if(old('status')==='inactive'): echo 'selected'; endif; ?>>Inactive</option></select></div>

            <div class="role-selection full">
                <label class="super-option"><input type="hidden" name="is_super_admin" value="0"><input type="checkbox" name="is_super_admin" value="1" <?php if(old('is_super_admin')): echo 'checked'; endif; ?>><span><strong>Super Administrator</strong><small>Unrestricted access to every current and future admin feature.</small></span></label>
                <div><strong class="selection-title">Roles</strong><div class="role-grid"><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><label><input type="checkbox" name="role_ids[]" value="<?php echo e($role->id); ?>" <?php if(in_array((string)$role->id, array_map('strval',old('role_ids',[])),true)): echo 'checked'; endif; ?>><span><strong><?php echo e($role->name); ?></strong><small><?php echo e($role->description ?: 'Custom administrator role'); ?></small></span></label><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></div>
            </div>
            <div class="full form-actions"><button class="team-button primary" type="submit"><i class="fa-solid fa-user-plus"></i> Add Administrator</button></div>
        </form>
    </section>

    <section class="team-panel">
        <div class="team-panel-heading list-heading"><div class="heading-group"><span><i class="fa-solid fa-users-gear"></i></span><div><h2>Administrators</h2><p>Review account status and assigned roles.</p></div></div><form method="GET" class="team-search"><input name="search" value="<?php echo e($search); ?>" placeholder="Search name, email or phone"><button>Search</button><?php if($search!==''): ?><a href="<?php echo e(route('admin.admin-users.index')); ?>">Clear</a><?php endif; ?></form></div>
        <div class="admin-list">
            <?php $__empty_1 = true; $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="admin-card">
                    <div class="admin-identity"><span><?php echo e(strtoupper(substr($admin->name,0,1))); ?></span><div><h3><?php echo e($admin->name); ?></h3><p><?php echo e($admin->email); ?><?php echo e($admin->phone ? ' · '.$admin->phone : ''); ?></p></div></div>
                    <div class="admin-badges"><span class="status <?php echo e($admin->status); ?>"><?php echo e(ucfirst($admin->status)); ?></span><?php if($admin->is_super_admin): ?><span class="super"><i class="fa-solid fa-crown"></i> Super Administrator</span><?php else: ?> <?php $__empty_2 = true; $__currentLoopData = $admin->adminRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><span><?php echo e($role->name); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><span>No role</span><?php endif; ?> <?php endif; ?></div>
                    <div class="admin-actions"><a href="<?php echo e(route('admin.admin-users.edit',$admin)); ?>" class="team-button edit"><i class="fa-regular fa-pen-to-square"></i> Edit</a><?php if((int)$admin->id !== (int)auth()->id()): ?><form method="POST" action="<?php echo e(route('admin.admin-users.destroy',$admin)); ?>" onsubmit="return confirm('Revoke administrator access from this user?');"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="team-button danger" type="submit"><i class="fa-solid fa-user-minus"></i> Revoke</button></form><?php endif; ?></div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="empty-state"><i class="fa-solid fa-users-slash"></i><h3>No administrators found</h3></div><?php endif; ?>
        </div>
        <?php if($admins->hasPages()): ?><div class="pagination-wrap"><?php echo e($admins->links()); ?></div><?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page-styles'); ?>
<style>
.team-page{display:grid;gap:20px;color:#172033}.team-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:27px;border-radius:20px;background:linear-gradient(135deg,#111827,#4338ca);color:#fff}.team-header>div>span{color:#c7d2fe;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.team-header h1{margin:6px 0 0}.team-header p{margin:7px 0 0;color:#e0e7ff}.team-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:0;border-radius:9px;padding:10px 13px;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}.team-button.secondary{background:#fff;color:#3730a3}.team-button.primary{background:#4f46e5;color:#fff}.team-button.edit{background:#eff6ff;color:#1d4ed8}.team-button.danger{background:#fff1f2;color:#be123c}.team-alert{display:flex;gap:10px;padding:14px 17px;border-radius:12px}.team-alert.success{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0}.team-alert.error{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}.team-alert ul{margin:6px 0 0;padding-left:18px}.team-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.team-stats article{padding:17px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.team-stats small{display:block;color:#64748b}.team-stats strong{display:block;margin-top:5px;font-size:23px}.team-panel{overflow:hidden;border:1px solid #e2e8f0;border-radius:17px;background:#fff}.team-panel-heading{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid #e2e8f0}.team-panel-heading>span,.heading-group>span{display:grid;place-items:center;width:42px;height:42px;border-radius:11px;background:#e0e7ff;color:#4338ca}.team-panel-heading h2{margin:0;font-size:18px}.team-panel-heading p{margin:4px 0 0;color:#64748b;font-size:12px}.team-form{display:grid;grid-template-columns:repeat(3,1fr);gap:15px;padding:20px}.field label,.selection-title{display:block;margin-bottom:6px;font-size:12px;font-weight:800}.field input,.field select,.team-search input{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:9px;padding:10px;font:inherit}.full{grid-column:1/-1}.role-selection{display:grid;gap:14px}.super-option,.role-grid label{display:flex;align-items:flex-start;gap:9px;padding:12px;border:1px solid #e2e8f0;border-radius:10px}.super-option{background:#fffbeb;border-color:#fde68a}.super-option span strong,.super-option span small,.role-grid span strong,.role-grid span small{display:block}.super-option span small,.role-grid span small{margin-top:3px;color:#64748b}.role-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.form-actions{display:flex;justify-content:flex-end}.list-heading{justify-content:space-between}.heading-group{display:flex;align-items:center;gap:12px}.team-search{display:flex;gap:7px}.team-search input{width:240px}.team-search button{border:0;border-radius:8px;background:#172033;color:#fff;padding:9px 12px}.team-search a{align-self:center;color:#4f46e5;text-decoration:none}.admin-list{display:grid;gap:12px;padding:17px}.admin-card{display:grid;grid-template-columns:minmax(230px,1fr) minmax(260px,1fr) auto;align-items:center;gap:15px;padding:15px;border:1px solid #e2e8f0;border-radius:12px}.admin-identity{display:flex;align-items:center;gap:11px}.admin-identity>span{display:grid;place-items:center;width:42px;height:42px;border-radius:50%;background:#e0e7ff;color:#3730a3;font-weight:850}.admin-identity h3{margin:0;font-size:15px}.admin-identity p{margin:3px 0 0;color:#64748b;font-size:12px}.admin-badges{display:flex;flex-wrap:wrap;gap:6px}.admin-badges span{padding:5px 8px;border-radius:999px;background:#f1f5f9;font-size:10px;font-weight:800}.admin-badges .status.active{background:#d1fae5;color:#047857}.admin-badges .status.inactive{background:#ffe4e6;color:#be123c}.admin-badges .super{background:#fef3c7;color:#b45309}.admin-actions{display:flex;gap:7px}.admin-actions form{margin:0}.pagination-wrap{padding:0 18px 18px}.empty-state{text-align:center;padding:45px;color:#64748b}
@media(max-width:1000px){.team-stats{grid-template-columns:repeat(2,1fr)}.team-form{grid-template-columns:repeat(2,1fr)}.admin-card{grid-template-columns:1fr}.admin-actions{justify-content:flex-start}}
@media(max-width:680px){.team-header,.list-heading{align-items:stretch;flex-direction:column}.team-stats,.team-form,.role-grid{grid-template-columns:1fr}.team-search{flex-wrap:wrap}.team-search input{width:100%}.team-button{width:100%;box-sizing:border-box}.admin-actions{flex-direction:column}}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('page-scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded',function(){const mode=document.getElementById('admin_mode');const createFields=document.querySelectorAll('.create-field');const promoteFields=document.querySelectorAll('.promote-field');function update(){const promote=mode.value==='promote';createFields.forEach(el=>el.hidden=promote);promoteFields.forEach(el=>el.hidden=!promote);document.getElementById('existing_user_id').required=promote;['admin_name','admin_email','admin_password','admin_password_confirmation'].forEach(id=>{document.getElementById(id).required=!promote;});}mode.addEventListener('change',update);update();});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\admin-users\index.blade.php ENDPATH**/ ?>