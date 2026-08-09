<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = ['name','slug','description','subject','body','available_variables','is_enabled','is_system','updated_by'];

    protected $casts = [
        'available_variables' => 'array',
        'is_enabled' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
    