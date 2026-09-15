<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',

        'google_id',
        'facebook_id',
        'avatar',

        'status',
        'is_admin',
        'is_super_admin',
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
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(InventoryHistory::class);
    }

    public function adminRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRole::class,
            'admin_role_user'
        )->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_admin
            && (bool) $this->is_super_admin
            && $this->status === 'active';
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!(bool) $this->is_admin || $this->status !== 'active') {
            return false;
        }

        if ($this->resolvedAdminPermissions === null) {
            $this->resolvedAdminPermissions = AdminPermission::query()
                ->whereHas(
                    'roles.users',
                    fn($query) => $query->whereKey($this->id)
                )
                ->pluck('slug')
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
