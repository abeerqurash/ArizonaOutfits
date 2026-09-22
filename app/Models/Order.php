<?php
    namespace App\Models;

    use App\Models\OrderItem;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Order extends Model
    {
        protected $fillable = [
            'user_id', 'order_number', 'tracking_number', 'courier', 'courier_provider',
            'subtotal', 'discount', 'shipping', 'shipping_method', 'shipping_price',
            'estimated_delivery', 'tax', 'total', 'currency', 'coupon_code',
            'payment_method', 'payment_status', 'order_status',
            'manual_status_override_at', 'manual_status_override_shipment_event_at',
            'billing_name', 'billing_email', 'billing_phone', 'billing_address',
            'billing_city', 'billing_state', 'billing_zip', 'billing_country',
            'shipping_name', 'shipping_email', 'shipping_phone', 'shipping_address',
            'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country',
            'order_notes', 'payment_provider', 'payment_reference', 'payment_intent_id',
            'paid_at', 'inventory_deducted_at', 'payment_failed_at',
            'payment_failure_message', 'payment_metadata', 'admin_notes',
        ];

        protected $casts = [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'shipping_price' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'inventory_deducted_at' => 'datetime',
            'payment_failed_at' => 'datetime',
            'manual_status_override_at' => 'datetime',
            'manual_status_override_shipment_event_at' => 'datetime',
            'payment_metadata' => 'array',
        ];

        public function items(): HasMany
        {
            return $this->hasMany(OrderItem::class);
        }

        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        public function activities(): HasMany
        {
            return $this->hasMany(OrderActivity::class);
        }

        public function notes(): HasMany
        {
            return $this->hasMany(OrderNote::class);
        }

        public function shipments(): HasMany
        {
            return $this->hasMany(OrderShipment::class);
        }

        public function inventoryHistories(): HasMany
        {
            return $this->hasMany(InventoryHistory::class);
        }
    }
