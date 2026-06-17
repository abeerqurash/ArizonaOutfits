<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTag extends Model
{
    protected $fillable = [
        'title',
        'slug',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product_tag');
    }
}
