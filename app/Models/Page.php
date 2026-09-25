<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'template',
        'render_mode',
        'blade_template',
        'meta_title',
        'meta_description',
        'published_at',
        'created_by',
        'updated_by',
        'creator_admin_id',
        'editor_admin_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'creator_admin_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'editor_admin_id');
    }

    public function legacyCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function legacyEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getPublicUrlAttribute(): string
    {
        return route('pages.show', $this->slug);
    }
}
