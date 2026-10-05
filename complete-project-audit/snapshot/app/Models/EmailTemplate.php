<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'subject',
        'body',
        'available_variables',
        'is_enabled',
        'is_system',
        'updated_by',
        'admin_id',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_enabled' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Current administrator who last edited this template.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Temporary historical bridge while legacy users are retained.
     */
    public function legacyEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
