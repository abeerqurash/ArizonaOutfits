<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminPermission::class,
            'admin_permission_role'
        );
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(
            Admin::class,
            'admin_role_admin',
            'admin_role_id',
            'admin_id'
        )->withTimestamps();
    }

    /**
     * Temporary compatibility alias for the existing admin-role Blade.
     * It now returns administrators from admins, NOT customers from users.
     */
    public function users(): BelongsToMany
    {
        return $this->admins();
    }
}
