<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOption extends Model
{
    protected $fillable = [
        'name',
        'type',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(
            ProductOptionValue::class,
            'product_option_id'
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_option_product',
            'product_option_id',
            'product_id'
        )->withTimestamps();
    }
}