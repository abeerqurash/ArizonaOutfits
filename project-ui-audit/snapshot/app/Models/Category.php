<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'parent_id',
        'image',
        'meta_title',
        'meta_description',
        'expert',
    ];

    protected $appends = [
        'image_url',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'category_post',
            'category_id',
            'post_id'
        )->withTimestamps();
    }

    public function primaryPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'primary_category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('title');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('title');
    }

    public function descendants(): Collection
    {
        $this->loadMissing('childrenRecursive');

        $descendants = new Collection();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    public function descendantIds(): array
    {
        return $this->descendants()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function canUseAsParentFor(Category $category): bool
    {
        if ($this->is($category)) {
            return false;
        }

        return !in_array(
            (int) $this->id,
            $category->descendantIds(),
            true
        );
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        $normalized = ltrim($this->image, '/');

        if (str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->url($normalized);
        }

        if (file_exists(public_path($normalized))) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    }
}
