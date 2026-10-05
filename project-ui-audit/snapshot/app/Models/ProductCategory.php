<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'parent_id',
        'description',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_category_product');
    }

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }
}
