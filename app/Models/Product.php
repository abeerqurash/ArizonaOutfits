<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'sku',
        'short_description',
        'long_description',
        'additional_info',
        'regular_price',
        'sale_price',
        'cost_price',
        'stock',
        'reorder_point',
        'reorder_quantity',
        'status',
        'featured_image',
        'views_count',
        'favorites_count',
        'cart_count',
        'purchase_count',
        'average_rating',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'integer',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'views_count' => 'integer',
        'favorites_count' => 'integer',
        'cart_count' => 'integer',
        'purchase_count' => 'integer',
        'average_rating' => 'decimal:2',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'product_category_product',
            'product_id',
            'product_category_id'
        )->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductTag::class,
            'product_product_tag',
            'product_id',
            'product_tag_id'
        )->withTimestamps();
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductOption::class,
            'product_option_product',
            'product_id',
            'product_option_id'
        )->withTimestamps();
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductOptionValue::class,
            'product_option_value_product',
            'product_id',
            'product_option_value_id'
        )->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(
            ProductVariant::class,
            'product_id'
        );
    }

    public function images(): HasMany
    {
        return $this->hasMany(
            ProductImage::class,
            'product_id'
        );
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(
            Review::class,
            'product_id'
        );
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(
            Review::class,
            'product_id'
        )->where('status', 'approved');
    }

    /**
     * Supplier-specific prices, SKUs, lead times, and minimum quantities.
     */
    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(
            Supplier::class,
            'supplier_products'
        )
            ->withPivot([
                'product_variant_id',
                'supplier_sku',
                'unit_cost',
                'minimum_order_quantity',
                'lead_time_days',
                'is_preferred',
                'is_active',
                'notes',
            ])
            ->withTimestamps();
    }

    protected function featuredImageUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $imagePath = $this->featured_image;

                if (blank($imagePath)) {
                    $firstImage = $this->relationLoaded('images')
                        ? $this->images->first()
                        : $this->images()->first();

                    if ($firstImage) {
                        $imagePath =
                            $firstImage->image_path
                            ?? $firstImage->image
                            ?? $firstImage->path
                            ?? $firstImage->filename
                            ?? null;
                    }
                }

                return $this->resolveImageUrl($imagePath);
            }
        );
    }

    public function getFinalPriceAttribute(): float
    {
        if (
            $this->sale_price !== null
            && (float) $this->sale_price < (float) $this->regular_price
        ) {
            return (float) $this->sale_price;
        }

        return (float) $this->regular_price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        $regularPrice = (float) $this->regular_price;
        $salePrice = $this->sale_price !== null
            ? (float) $this->sale_price
            : null;

        if (
            $salePrice !== null
            && $regularPrice > 0
            && $salePrice < $regularPrice
        ) {
            return (int) round(
                (($regularPrice - $salePrice) / $regularPrice) * 100
            );
        }

        return null;
    }

    private function resolveImageUrl(mixed $imagePath): ?string
    {
        if (!is_string($imagePath)) {
            return null;
        }

        $imagePath = trim($imagePath);

        if ($imagePath === '') {
            return null;
        }

        if (
            Str::startsWith(
                $imagePath,
                ['http://', 'https://', 'data:', '//']
            )
        ) {
            return $imagePath;
        }

        $normalizedPath = str_replace('\\', '/', $imagePath);
        $normalizedPath = preg_replace('#^/?public/#', '', $normalizedPath);

        if (
            Str::startsWith(
                $normalizedPath,
                ['/storage/', 'storage/']
            )
        ) {
            return asset(ltrim($normalizedPath, '/'));
        }

        if (file_exists(public_path(ltrim($normalizedPath, '/')))) {
            return asset(ltrim($normalizedPath, '/'));
        }

        if (
            Storage::disk('public')->exists(
                ltrim($normalizedPath, '/')
            )
        ) {
            return Storage::disk('public')->url(
                ltrim($normalizedPath, '/')
            );
        }

        return Storage::disk('public')->url(
            ltrim($normalizedPath, '/')
        );
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(InventoryHistory::class);
    }
}
