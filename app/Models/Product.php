<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function categories()
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'product_category_product',
            'product_id',
            'product_category_id'
        );
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function tags()
    {
        return $this->belongsToMany(ProductTag::class, 'product_product_tag');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getFinalPriceAttribute()
    {
        return $this->sale_price ?: $this->regular_price;
    }

    public function getDiscountPercentAttribute()
    {
        if ($this->sale_price && $this->regular_price > $this->sale_price) {
            return round((($this->regular_price - $this->sale_price) / $this->regular_price) * 100);
        }

        return null;
    }
}
