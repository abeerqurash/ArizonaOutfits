<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SupplierDocument extends Model
{
    public const TYPE_CONTRACT = 'contract';

    public const TYPE_CERTIFICATE = 'certificate';

    public const TYPE_PRICE_LIST = 'price_list';

    public const TYPE_BANK_DETAILS = 'bank_details';

    public const TYPE_TAX_DOCUMENT = 'tax_document';

    public const TYPE_INSURANCE = 'insurance';

    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'supplier_id',
        'title',
        'document_type',
        'file_path',
        'file_name',
        'original_name',
        'mime_type',
        'file_size',
        'expiry_date',
        'expires_at',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'expiry_date' => 'date',
        'expires_at' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    protected function documentTypeLabel(): Attribute
    {
        return Attribute::get(
            fn (): string => Str::headline(
                $this->document_type
            )
        );
    }

    /**
     * Compatibility with the existing file_name database column.
     */
    protected function originalName(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string =>
                $this->file_name,

            set: fn (?string $value): array => [
                'file_name' => $value,
            ]
        );
    }

    /**
     * Compatibility with the existing expiry_date database column.
     */
    protected function expiresAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->expiry_date,

            set: fn ($value): array => [
                'expiry_date' => $value,
            ]
        );
    }

    protected function formattedSize(): Attribute
    {
        return Attribute::get(
            function (): string {
                $bytes = max(
                    0,
                    (int) $this->file_size
                );

                if ($bytes < 1024) {
                    return $bytes . ' B';
                }

                if ($bytes < 1048576) {
                    return number_format(
                        $bytes / 1024,
                        1
                    ) . ' KB';
                }

                return number_format(
                    $bytes / 1048576,
                    1
                ) . ' MB';
            }
        );
    }
}
