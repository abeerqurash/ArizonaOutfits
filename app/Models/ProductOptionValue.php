<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOptionValue extends Model
{
    protected $fillable = [
        'product_option_id',
        'label',
        'value',
        'color_code',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(
            ProductOption::class,
            'product_option_id'
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_option_value_product',
            'product_option_value_id',
            'product_id'
        )->withTimestamps();
    }
}