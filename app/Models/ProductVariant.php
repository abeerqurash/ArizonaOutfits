<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'options',
        'regular_price',
        'sale_price',
        'stock',
        'image',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}