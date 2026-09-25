<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'password_set_at',
        'email_login_enabled_at',
        'email_login_pending_at',

        'phone_verified_at',
        'security_reminder_shown_at',
        'registration_method',
        'pending_email',
        'pending_email_requested_at',
        'google_id',
        'facebook_id',
        'avatar',

        'status',
        'is_admin',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    private ?array $resolvedAdminPermissions = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'security_reminder_shown_at' => 'datetime',
            'password_set_at' => 'datetime',
            'email_login_enabled_at' => 'datetime',
            'email_login_pending_at' => 'datetime',

            'password' => 'hashed',
            'pending_email_requested_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Historical password hashes retained for password reuse protection.
     */
    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    /**
     * Email identities connected to this customer.
     *
     * One customer can independently own a custom email, Google identity
     * and Facebook identity. Equal email strings do not collapse providers.
     */
    public function emailIdentities(): HasMany
    {
        return $this->hasMany(CustomerEmailIdentity::class);
    }

    public function customEmailIdentity(): ?CustomerEmailIdentity
    {
        return $this->emailIdentities()
            ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
            ->first();
    }

    public function googleEmailIdentity(): ?CustomerEmailIdentity
    {
        return $this->emailIdentities()
            ->where('source', CustomerEmailIdentity::SOURCE_GOOGLE)
            ->first();
    }

    public function facebookEmailIdentity(): ?CustomerEmailIdentity
    {
        return $this->emailIdentities()
            ->where('source', CustomerEmailIdentity::SOURCE_FACEBOOK)
            ->first();
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(InventoryHistory::class);
    }

    public function adminRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRole::class,
            'admin_role_user'
        )->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_admin
            && (bool) $this->is_super_admin
            && $this->status === 'active';
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (
            ! (bool) $this->is_admin
            || $this->status !== 'active'
        ) {
            return false;
        }

        if ($this->resolvedAdminPermissions === null) {
            $this->resolvedAdminPermissions =
                AdminPermission::query()
                ->whereHas(
                    'roles.users',
                    fn($query) =>
                    $query->whereKey($this->id)
                )
                ->pluck('slug')
                ->all();
        }

        return in_array(
            $permission,
            $this->resolvedAdminPermissions,
            true
        );
    }

    public function flushAdminPermissionCache(): void
    {
        $this->resolvedAdminPermissions = null;

        $this->unsetRelation('adminRoles');
    }

    /**
     * Customer has deliberately created/reset a usable password.
     *
     * Both values are required so legacy social accounts that received
     * an unknown system-generated password are not treated as
     * password-capable accounts.
     */
    public function hasPassword(): bool
    {
        return filled($this->password)
            && $this->password_set_at !== null;
    }

    /**
     * Customer has explicitly enabled a verified local email login.
     *
     * A stored contact/social email must never be treated as a login method.
     */
    public function hasEmailLogin(): bool
    {
        return filled($this->email)
            && $this->email_login_enabled_at !== null
            && $this->email_verified_at !== null;
    }

    /**
     * Email login is currently waiting for ownership verification.
     */
    public function hasPendingEmailLogin(): bool
    {
        return filled($this->email)
            && $this->email_login_pending_at !== null
            && $this->email_login_enabled_at === null;
    }

    /**
     * Customer has a verified, explicitly connected email login method.
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->hasEmailLogin();
    }

    /**
     * Called by Laravel's normal email-verification flow.
     *
     * Verification of an explicitly pending email login activates that login
     * method. A social/contact email that was never placed into the pending
     * email-login state is not silently converted into an email login.
     */
    public function markEmailAsVerified(): bool
    {
        if ($this->hasVerifiedEmail()) {
            return false;
        }

        $updates = [
            'email_verified_at' => $this->freshTimestamp(),
        ];

        if ($this->email_login_pending_at !== null) {
            $updates['email_login_enabled_at'] = $this->freshTimestamp();
            $updates['email_login_pending_at'] = null;
        }

        $saved = $this->forceFill($updates)->save();

        if ($saved) {
            /*
             * Laravel's signed email-verification flow verifies only the
             * manual/custom ArizonaOutfits email identity. Provider OAuth
             * identities keep their own independent verification state.
             */
            $normalizedEmail =
                CustomerEmailIdentity::normalizeEmail($this->email);

            if ($normalizedEmail !== null) {
                CustomerEmailIdentity::query()
                    ->where('user_id', $this->id)
                    ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
                    ->where('normalized_email', $normalizedEmail)
                    ->whereNull('disconnected_at')
                    ->update([
                        'verified_at' => $this->email_verified_at,
                        'verification_pending_at' => null,
                        'is_login_enabled' =>
                            $this->email_login_enabled_at !== null,
                        'updated_at' => now(),
                    ]);
            }
        }

        return $saved;
    }

    /**
     * Customer has a verified phone number.
     */
    public function hasVerifiedPhone(): bool
    {
        return filled($this->phone)
            && $this->phone_verified_at !== null;
    }

    /**
     * Google is connected.
     */
    public function hasGoogleAccount(): bool
    {
        return filled($this->google_id);
    }

    /**
     * Facebook is connected.
     */
    public function hasFacebookAccount(): bool
    {
        return filled($this->facebook_id);
    }

    /**
     * Resolve the current multi-source email state for Login & Security.
     *
     * This is a display/security resolver only. It never merges credentials.
     * Custom, Google and Facebook remain independent identities even when
     * their normalized email strings are identical.
     */
    public function resolvedEmailIdentityState(): array
    {
        $identities = $this->emailIdentities()
            ->whereNull('disconnected_at')
            ->whereIn('source', [
                CustomerEmailIdentity::SOURCE_CUSTOM,
                CustomerEmailIdentity::SOURCE_GOOGLE,
                CustomerEmailIdentity::SOURCE_FACEBOOK,
            ])
            ->get()
            ->keyBy('source');

        $custom = $identities->get(CustomerEmailIdentity::SOURCE_CUSTOM);
        $google = $identities->get(CustomerEmailIdentity::SOURCE_GOOGLE);
        $facebook = $identities->get(CustomerEmailIdentity::SOURCE_FACEBOOK);

        $sources = [];

        foreach ([
            CustomerEmailIdentity::SOURCE_CUSTOM => $custom,
            CustomerEmailIdentity::SOURCE_GOOGLE => $google,
            CustomerEmailIdentity::SOURCE_FACEBOOK => $facebook,
        ] as $source => $identity) {
            if (! $identity || ! $identity->isConnected()) {
                continue;
            }

            $sources[$source] = [
                'source' => $source,
                'email' => $identity->email,
                'normalized_email' => $identity->normalized_email,
                /*
                 * Keep the legacy display keys during the transition, but
                 * expose the two verification concepts explicitly.
                 */
                'verified' => $identity->isVerified(),
                'verification_pending' =>
                    $identity->isPendingVerification(),
                'provider_verified' =>
                    $identity->isProviderVerified(),
                'email_verified' =>
                    $identity->isEmailVerified(),
                'email_verification_pending' =>
                    $identity->isEmailVerificationPending(),
                'login_enabled' =>
                    (bool) $identity->is_login_enabled,
            ];
        }

        $emails = collect($sources)
            ->pluck('normalized_email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $primaryEmail =
            $custom?->email
            ?? $google?->email
            ?? $facebook?->email;

        $customConnected =
            $custom?->isConnected() ?? false;

        $customVerified =
            $custom?->isEmailVerified() ?? false;

        $customPending =
            $custom?->isEmailVerificationPending() ?? false;

        return [
            'connected' => $sources !== [],
            'source_count' => count($sources),
            'sources' => $sources,
            'emails' => $emails,
            'same_email_across_sources' =>
                count($sources) > 1
                && count($emails) === 1,
            'has_multiple_email_addresses' =>
                count($emails) > 1,
            'primary_email' => $primaryEmail,

            /*
             * The manual/custom slot is independent from provider identities.
             * It remains available when only Google/Facebook is connected.
             */
            'custom_connected' => $customConnected,
            'custom_verified' => $customVerified,
            'custom_verification_pending' => $customPending,
            'custom_login_enabled' =>
                $customConnected
                && $customVerified
                && (bool) $custom?->is_login_enabled
                && $this->hasPassword(),

            'google_connected' =>
                $google?->isConnected() ?? false,
            'google_provider_verified' =>
                $google?->isProviderVerified() ?? false,
            'google_email_verified' =>
                $google?->isEmailVerified() ?? false,

            'facebook_connected' =>
                $facebook?->isConnected() ?? false,
            'facebook_provider_verified' =>
                $facebook?->isProviderVerified() ?? false,
            'facebook_email_verified' =>
                $facebook?->isEmailVerified() ?? false,

            /*
             * Only a pending custom identity can use ArizonaOutfits'
             * email-verification resend flow. OAuth provider identities do not
             * create a fake custom verification request.
             */
            'can_resend_custom_verification' => $customPending,

            /*
             * Common Disconnect Email UI can list these exact sources.
             */
            'disconnectable_sources' =>
                array_keys($sources),
        ];
    }

    public function connectedEmailIdentitySources(): array
    {
        return $this->resolvedEmailIdentityState()['disconnectable_sources'];
    }

    /**
     * Phone-created accounts need at least one
     * additional recovery/login method.
     */
    public function hasBackupLoginMethod(): bool
    {
        return ($this->hasEmailLogin() && $this->hasPassword())
            || $this->hasGoogleAccount()
            || $this->hasFacebookAccount();
    }

    /**
     * Verified phone exists but no backup method exists.
     */
    public function needsBackupLoginMethod(): bool
    {
        return $this->hasVerifiedPhone()
            && ! $this->hasBackupLoginMethod();
    }

    /**
     * Email/social customer does not yet have
     * a verified phone number.
     */
    public function needsVerifiedPhone(): bool
    {
        return ! $this->hasVerifiedPhone()
            && (
                $this->hasEmailLogin()
                || $this->hasGoogleAccount()
                || $this->hasFacebookAccount()
            );
    }

    /**
     * Account still requires additional security setup.
     */
    public function needsSecuritySetup(): bool
    {
        return $this->needsBackupLoginMethod()
            || $this->needsVerifiedPhone();
    }

    /**
     * Security reminder should appear at most
     * once during a calendar day.
     */
    public function shouldShowSecurityReminder(): bool
    {
        if (! $this->needsSecuritySetup()) {
            return false;
        }

        if ($this->security_reminder_shown_at === null) {
            return true;
        }

        return ! $this
            ->security_reminder_shown_at
            ->isToday();
    }

    /**
     * Record that today's reminder was displayed.
     */
    public function markSecurityReminderAsShown(): void
    {
        $this->forceFill([
            'security_reminder_shown_at' => now(),
        ])->save();
    }
}
