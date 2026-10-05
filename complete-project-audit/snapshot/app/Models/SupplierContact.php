<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierContact extends Model
{
    protected $fillable = [
        'supplier_id',
        'name',
        'job_title',
        'department',
        'email',
        'phone',
        'mobile',
        'is_primary',
        'receives_purchase_orders',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'receives_purchase_orders' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }
}
