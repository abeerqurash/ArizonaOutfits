<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'admin_id', 'action', 'route_name', 'method', 'url',
        'auditable_type', 'auditable_id', 'description', 'request_data',
        'ip_address', 'user_agent', 'status_code', 'outcome', 'created_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'status_code' => 'integer',
        'created_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('outcome', 'success');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('outcome', 'failed');
    }

    public function getActorNameAttribute(): string
    {
        return $this->admin?->name ?: $this->user?->name ?: 'Unknown administrator';
    }
}
