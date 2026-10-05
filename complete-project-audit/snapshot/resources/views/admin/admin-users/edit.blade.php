@extends('admin.layouts.app')
@section('title', 'Edit Administrator')
@section('content')
<div class="admin-edit-page">
    <header><div><span>Admin team</span><h1>Edit {{ $adminUser->name }}</h1><p>Update account information and access level.</p></div><a href="{{ route('admin.admin-users.index') }}"><i class="fa-solid fa-arrow-left"></i> Admin Team</a></header>
    @if($errors->any())<div class="edit-alert"><strong>The account was not updated.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('admin.admin-users.update',$adminUser) }}" class="edit-panel">@csrf @method('PUT')
        <div class="identity"><span>{{ strtoupper(substr($adminUser->name,0,1)) }}</span><div><strong>{{ $adminUser->email }}</strong><small>{{ $adminUser->is_super_admin ? 'Super Administrator' : $adminUser->adminRoles->pluck('name')->implode(', ') }}</small></div></div>
        <div class="edit-grid">
            <div class="field"><label>Name</label><input name="name" value="{{ old('name',$adminUser->name) }}" required maxlength="255"></div>
            <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email',$adminUser->email) }}" required maxlength="255"></div>
            <div class="field"><label>Phone</label><input name="phone" value="{{ old('phone',$adminUser->phone) }}" maxlength="50"></div>
            <div class="field"><label>Status</label><select name="status"><option value="active" @selected(old('status',$adminUser->status)==='active')>Active</option><option value="inactive" @selected(old('status',$adminUser->status)==='inactive')>Inactive</option></select></div>
            <div class="field"><label>New password</label><input type="password" name="password" minlength="8" placeholder="Leave empty to keep current password"></div>
            <div class="field"><label>Confirm password</label><input type="password" name="password_confirmation" minlength="8"></div>
        </div>
        <div class="access-section">
            <label class="super-option"><input type="hidden" name="is_super_admin" value="0"><input type="checkbox" name="is_super_admin" value="1" @checked(old('is_super_admin',$adminUser->is_super_admin))><span><strong>Super Administrator</strong><small>Provides unrestricted access and overrides assigned roles.</small></span></label>
            @php $selectedRoles=array_map('strval',old('role_ids',$adminUser->adminRoles->pluck('id')->all())); @endphp
            <h2>Assigned roles</h2><div class="role-grid">@foreach($roles as $role)<label><input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array((string)$role->id,$selectedRoles,true))><span><strong>{{ $role->name }}</strong><small>{{ $role->description ?: 'Custom role' }}</small></span></label>@endforeach</div>
        </div>
        <footer><a href="{{ route('admin.admin-users.index') }}">Cancel</a><button type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Administrator</button></footer>
    </form>
</div>
@endsection
@push('page-styles')
<style>
.admin-edit-page{display:grid;gap:20px;color:#172033}.admin-edit-page>header{display:flex;align-items:center;justify-content:space-between;padding:26px;border-radius:19px;background:linear-gradient(135deg,#111827,#4338ca);color:#fff}.admin-edit-page header span{color:#c7d2fe;font-size:11px;font-weight:850;text-transform:uppercase;letter-spacing:.14em}.admin-edit-page h1{margin:6px 0 0}.admin-edit-page header p{margin:6px 0 0;color:#e0e7ff}.admin-edit-page header a{padding:10px 13px;border-radius:9px;background:#fff;color:#3730a3;text-decoration:none;font-weight:800}.edit-alert{padding:15px;border:1px solid #fecdd3;border-radius:12px;background:#fff1f2;color:#be123c}.edit-alert ul{margin:6px 0 0}.edit-panel{overflow:hidden;border:1px solid #e2e8f0;border-radius:17px;background:#fff}.identity{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid #e2e8f0}.identity>span{display:grid;place-items:center;width:48px;height:48px;border-radius:50%;background:#e0e7ff;color:#3730a3;font-size:20px;font-weight:850}.identity strong,.identity small{display:block}.identity small{margin-top:4px;color:#64748b}.edit-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px;padding:20px}.field label{display:block;margin-bottom:6px;font-size:12px;font-weight:800}.field input,.field select{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:9px;padding:10px;font:inherit}.access-section{padding:20px;border-top:1px solid #e2e8f0}.access-section h2{font-size:16px}.super-option,.role-grid label{display:flex;align-items:flex-start;gap:9px;padding:12px;border:1px solid #e2e8f0;border-radius:10px}.super-option{background:#fffbeb;border-color:#fde68a}.super-option strong,.super-option small,.role-grid strong,.role-grid small{display:block}.super-option small,.role-grid small{margin-top:3px;color:#64748b}.role-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.edit-panel footer{display:flex;justify-content:flex-end;gap:9px;padding:16px 20px;border-top:1px solid #e2e8f0}.edit-panel footer a,.edit-panel footer button{border:0;border-radius:9px;padding:10px 14px;font:inherit;font-weight:800;text-decoration:none}.edit-panel footer a{background:#f1f5f9;color:#475569}.edit-panel footer button{background:#4f46e5;color:#fff}
@media(max-width:680px){.admin-edit-page>header{align-items:stretch;flex-direction:column;gap:15px}.edit-grid,.role-grid{grid-template-columns:1fr}.edit-panel footer{flex-direction:column}.edit-panel footer a,.edit-panel footer button{text-align:center}}
</style>
@endpush
