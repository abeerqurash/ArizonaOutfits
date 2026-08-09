<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPurchaseOrderDelivery extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'supplier_id',
        'purchase_order_id',
        'recipient_emails',
        'subject',
        'message',
        'attach_pdf',
        'status',
        'error_message',
        'sent_by',
        'queued_at',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'recipient_emails' => 'array',
        'attach_pdf' => 'boolean',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'sent_by'
        );
    }
}
