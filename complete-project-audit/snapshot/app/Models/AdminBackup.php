<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminBackup extends Model
{
    protected $fillable = [
        'created_by',
        'admin_id',
        'name',
        'type',
        'disk',
        'file_path',
        'file_size',
        'status',
        'table_count',
        'row_count',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'table_count' => 'integer',
        'row_count' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Current administrator who created the backup.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Temporary historical bridge while legacy users are retained.
     */
    public function legacyCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCreatorNameAttribute(): string
    {
        return $this->creator?->name
            ?: $this->legacyCreator?->name
            ?: 'System';
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = max(0, (int) $this->file_size);

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        if ($bytes < 1073741824) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        return number_format($bytes / 1073741824, 2) . ' GB';
    }
}
