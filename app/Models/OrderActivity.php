<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderActivity extends Model
{
    use HasFactory;

    /**
     * Activity types.
     */
    public const TYPE_ORDER_CREATED = 'order_created';

    public const TYPE_ORDER_STATUS_CHANGED =
        'order_status_changed';

    public const TYPE_PAYMENT_STATUS_CHANGED =
        'payment_status_changed';

    public const TYPE_TRACKING_UPDATED =
        'tracking_updated';

    public const TYPE_NOTE_ADDED =
        'note_added';

    public const TYPE_ORDER_UPDATED =
        'order_updated';

    public const TYPE_EMAIL_SENT =
        'email_sent';

    public const TYPE_INVOICE_GENERATED =
        'invoice_generated';

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'title',
        'description',
        'field_name',
        'old_value',
        'new_value',
        'metadata',
    ];

    /**
     * Attribute casts.
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Order associated with the activity.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Admin or user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return activities from newest to oldest.
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Human-readable actor name.
     */
    public function getActorNameAttribute(): string
    {
        return $this->user?->name ?: 'System';
    }

    /**
     * Human-readable activity value.
     */
    public function getFormattedChangeAttribute(): ?string
    {
        if (
            blank($this->old_value) &&
            blank($this->new_value)
        ) {
            return null;
        }

        if (blank($this->old_value)) {
            return ucfirst((string) $this->new_value);
        }

        if (blank($this->new_value)) {
            return ucfirst((string) $this->old_value);
        }

        return sprintf(
            '%s → %s',
            ucfirst((string) $this->old_value),
            ucfirst((string) $this->new_value)
        );
    }
}