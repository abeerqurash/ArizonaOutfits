<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'company_name',
        'slug',
        'supplier_code',
        'contact_person',

        'email',
        'phone',
        'alternate_phone',
        'website',

        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',

        'currency',
        'payment_terms',
        'lead_time_days',
        'credit_limit',
        'tax_number',
        'registration_number',

        'bank_name',
        'account_name',
        'account_number',
        'sort_code',
        'iban',
        'swift_code',

        'status',
        'is_preferred',

        'notes',
        'internal_notes',

        'created_by',
    ];

    protected $casts = [
        'lead_time_days' => 'integer',
        'credit_limit' => 'decimal:2',
        'is_preferred' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model booting
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(
            function (Supplier $supplier): void {
                if (blank($supplier->slug)) {
                    $supplier->slug =
                        self::generateUniqueSlug(
                            $supplier->company_name
                        );
                }

                if (blank($supplier->supplier_code)) {
                    $supplier->supplier_code =
                        self::generateSupplierCode();
                }

                if (blank($supplier->status)) {
                    $supplier->status =
                        self::STATUS_ACTIVE;
                }

                if (blank($supplier->currency)) {
                    $supplier->currency = 'GBP';
                }
            }
        );

        static::updating(
            function (Supplier $supplier): void {
                if (
                    $supplier->isDirty('company_name')
                    && blank($supplier->slug)
                ) {
                    $supplier->slug =
                        self::generateUniqueSlug(
                            $supplier->company_name,
                            $supplier->id
                        );
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function contacts(): HasMany
    {
        return $this->hasMany(
            SupplierContact::class
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            SupplierDocument::class
        );
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(
            SupplierRating::class
        );
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(
            PurchaseOrder::class
        );
    }

    public function purchaseOrderDeliveries(): HasMany
    {
        return $this->hasMany(
            SupplierPurchaseOrderDelivery::class
        );
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(
            SupplierProduct::class
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'supplier_products'
        )
            ->withPivot([
                'product_variant_id',
                'supplier_sku',
                'unit_cost',
                'minimum_order_quantity',
                'lead_time_days',
                'is_preferred',
                'is_active',
                'notes',
            ])
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Primary contact
    |--------------------------------------------------------------------------
    */

    public function primaryContact(): HasOne
    {
        return $this->hasOne(
            SupplierContact::class
        )->where('is_primary', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Status helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status
            === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status
            === self::STATUS_INACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this->status
            === self::STATUS_BLOCKED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE =>
                'Active',

            self::STATUS_INACTIVE =>
                'Inactive',

            self::STATUS_BLOCKED =>
                'Blocked',

            default =>
                Str::headline(
                    (string) $this->status
                ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Full address
    |--------------------------------------------------------------------------
    */

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ])
            ->filter(
                fn (mixed $value): bool =>
                    filled($value)
            )
            ->implode(', ');
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase analytics
    |--------------------------------------------------------------------------
    */

    public function getTotalSpendAttribute(): float
    {
        if ($this->relationLoaded('purchaseOrders')) {
            return (float) $this
                ->purchaseOrders
                ->where(
                    'status',
                    '!=',
                    PurchaseOrder::STATUS_CANCELLED
                )
                ->sum('total_amount');
        }

        return (float) $this
            ->purchaseOrders()
            ->where(
                'status',
                '!=',
                PurchaseOrder::STATUS_CANCELLED
            )
            ->sum('total_amount');
    }

    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('ratings')) {
            return round(
                (float) $this
                    ->ratings
                    ->avg('overall_rating'),
                2
            );
        }

        return round(
            (float) $this
                ->ratings()
                ->avg('overall_rating'),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate slug
    |--------------------------------------------------------------------------
    */

    public static function generateUniqueSlug(
        string $companyName,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug(
            $companyName
        );

        if ($baseSlug === '') {
            $baseSlug = 'supplier';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            self::withTrashed()
                ->when(
                    $ignoreId !== null,
                    fn ($query) =>
                        $query->where(
                            'id',
                            '!=',
                            $ignoreId
                        )
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug =
                $baseSlug
                . '-'
                . $counter;

            $counter++;
        }

        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate supplier code
    |--------------------------------------------------------------------------
    */

    public static function generateSupplierCode(): string
    {
        $prefix = 'SUP-';

        $lastCode = self::withTrashed()
            ->where(
                'supplier_code',
                'like',
                $prefix . '%'
            )
            ->orderByDesc('id')
            ->value('supplier_code');

        $nextNumber = 1;

        if (is_string($lastCode)) {
            $lastNumber = (int) Str::after(
                $lastCode,
                $prefix
            );

            $nextNumber =
                $lastNumber + 1;
        }

        return $prefix
            . str_pad(
                (string) $nextNumber,
                5,
                '0',
                STR_PAD_LEFT
            );
    }
}
