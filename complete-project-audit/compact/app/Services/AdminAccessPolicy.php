<?php
namespace App\Services;
use App\Models\{Admin,AdminPermission,AdminRole};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AdminAccessPolicy
{
    public function permissions(array $ids): void
    {
        $actor=Auth::guard('admin')->user();abort_unless($actor,403);if($actor->is_super_admin)return;
        foreach(AdminPermission::whereIn('id',$ids)->pluck('slug')as $slug)if(!$actor->hasAdminPermission($slug))throw ValidationException::withMessages(['permission_ids'=>'You cannot grant permissions you do not have.']);
    }
    public function team(bool $super, array $roles, ?Admin $target=null): void
    {
        $actor=Auth::guard('admin')->user();abort_unless($actor,403);
        if(!$actor->is_super_admin && ($super || $target?->is_super_admin))throw ValidationException::withMessages(['is_super_admin'=>'Only a Super Administrator can manage Super Administrator access.']);
        $this->permissions(AdminRole::with('permissions')->whereIn('id',$roles)->get()->flatMap(fn($role)=>$role->permissions->pluck('id'))->unique()->all());
    }
}