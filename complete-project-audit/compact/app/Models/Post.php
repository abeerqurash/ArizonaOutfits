<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
class Post extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ARCHIVED = 'archived';
    protected $fillable = [
        'title',
        'slug',
        'expert',
        'excerpt',
        'content',
        'feature_image',
        'feature_image_alt',
        'template',
        'primary_category_id',
        'author_id',
        'status',
        'published_at',
        'scheduled_at',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots_index',
        'robots_follow',
        'og_title',
        'og_description',
        'og_image',
    ];
    protected $appends = [
        'feature_image_url',
        'og_image_url',
    ];
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_post',
            'post_id',
            'category_id'
        )->withTimestamps();
    }
    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }
    public function redirects(): HasMany
    {
        return $this->hasMany(PostRedirect::class);
    }
    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class)
            ->orderByDesc('revision_number');
    }
    public function scopeLatestPosts(Builder $query): Builder
    {
        return $query->latest('published_at')->latest('id');
    }
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
    public function scopeScheduled(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at');
    }
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED
            && $this->scheduled_at !== null;
    }
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }
    public function getFeatureImageUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->feature_image);
    }
    public function getOgImageUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->og_image);
    }
    private function resolveMediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        $normalized = ltrim($path, '/');
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