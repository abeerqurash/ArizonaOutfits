<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerEmailIdentity extends Model
{
    use HasFactory;

    public const SOURCE_CUSTOM = 'custom';
    public const SOURCE_GOOGLE = 'google';
    public const SOURCE_FACEBOOK = 'facebook';

    protected $fillable = [
        'user_id',
        'source',
        'email',
        'normalized_email',
        'provider_user_id',
        'provider_verified_at',
        'email_verified_at',
        'email_verification_pending_at',
        'verified_at',
        'verification_pending_at',
        'connected_at',
        'disconnected_at',
        'is_login_enabled',
    ];

    protected function casts(): array
    {
        return [
            'provider_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'email_verification_pending_at' => 'datetime',
            'verified_at' => 'datetime',
            'verification_pending_at' => 'datetime',
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
            'is_login_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConnected(): bool
    {
        return $this->connected_at !== null
            && $this->disconnected_at === null;
    }

    /**
     * ArizonaOutfits email-address verification.
     *
     * This is deliberately separate from provider authentication.
     */
    public function isEmailVerified(): bool
    {
        return $this->isConnected()
            && $this->email_verified_at !== null;
    }

    public function isEmailVerificationPending(): bool
    {
        return $this->isConnected()
            && $this->email_verified_at === null
            && $this->email_verification_pending_at !== null;
    }

    /**
     * Google/Facebook OAuth/provider authentication.
     */
    public function isProviderVerified(): bool
    {
        return $this->isConnected()
            && ! $this->isCustom()
            && $this->provider_verified_at !== null;
    }

    /**
     * Backward-compatible aliases.
     *
     * Existing custom-email readers continue to mean ArizonaOutfits email
     * verification. Existing social readers continue to mean provider proof.
     */
    public function isVerified(): bool
    {
        return $this->isCustom()
            ? $this->isEmailVerified()
            : $this->isProviderVerified();
    }

    public function isPendingVerification(): bool
    {
        return $this->isCustom()
            && $this->isEmailVerificationPending();
    }

    public function isCustom(): bool
    {
        return $this->source === self::SOURCE_CUSTOM;
    }

    public function isGoogle(): bool
    {
        return $this->source === self::SOURCE_GOOGLE;
    }

    public function isFacebook(): bool
    {
        return $this->source === self::SOURCE_FACEBOOK;
    }

    public static function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return $normalized !== '' ? $normalized : null;
    }
}
