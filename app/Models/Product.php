<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'stock',
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
        'stock' => 'integer',
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

    public function getFinalPriceAttribute()
    {
        return $this->sale_price
            ?: $this->regular_price;
    }

    public function getDiscountPercentAttribute()
    {
        if (
            $this->sale_price !== null
            && $this->regular_price > 0
            && $this->regular_price > $this->sale_price
        ) {
            return round(
                (
                    ($this->regular_price - $this->sale_price)
                    / $this->regular_price
                ) * 100
            );
        }

        return null;
    }
}