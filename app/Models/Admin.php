<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'email_verified_at',
        'password',
        'status',
        'is_super_admin',
        'legacy_user_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    private ?array $resolvedAdminPermissions = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function adminRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRole::class,
            'admin_role_admin',
            'admin_id',
            'admin_role_id'
        )->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->isActive() && (bool) $this->is_super_admin;
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->isActive()) {
            return false;
        }

        if ($this->resolvedAdminPermissions === null) {
            $this->resolvedAdminPermissions = DB::table('admin_permissions')
                ->join(
                    'admin_permission_role',
                    'admin_permissions.id',
                    '=',
                    'admin_permission_role.admin_permission_id'
                )
                ->join(
                    'admin_role_admin',
                    'admin_permission_role.admin_role_id',
                    '=',
                    'admin_role_admin.admin_role_id'
                )
                ->where('admin_role_admin.admin_id', $this->id)
                ->distinct()
                ->pluck('admin_permissions.slug')
                ->all();
        }

        return in_array(
            $permission,
            $this->resolvedAdminPermissions,
            true
        );
    }

    public function flushAdminPermissionCache(): void
    {
        $this->resolvedAdminPermissions = null;
        $this->unsetRelation('adminRoles');
    }
}
