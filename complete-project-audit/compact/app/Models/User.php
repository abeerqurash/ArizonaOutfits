<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomerResetPassword;
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
public function sendPasswordResetNotification($token): void
{
    $this->notify(new CustomerResetPassword($token));
}
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
    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }
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
    public function hasPassword(): bool
    {
        return filled($this->password)
            && $this->password_set_at !== null;
    }
    public function hasEmailLogin(): bool
    {
        return filled($this->email)
            && $this->email_login_enabled_at !== null
            && $this->email_verified_at !== null;
    }
    public function hasPendingEmailLogin(): bool
    {
        return filled($this->email)
            && $this->email_login_pending_at !== null
            && $this->email_login_enabled_at === null;
    }
    public function hasVerifiedEmail(): bool
    {
        return $this->hasEmailLogin();
    }
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
            $normalizedEmail =
                CustomerEmailIdentity::normalizeEmail($this->email);
            if ($normalizedEmail !== null) {
                CustomerEmailIdentity::query()
                    ->where('user_id', $this->id)
                    ->where('source', CustomerEmailIdentity::SOURCE_CUSTOM)
                    ->where('normalized_email', $normalizedEmail)
                    ->whereNull('disconnected_at')
                    ->update([
                        'provider_verified_at' => null,
                        'email_verified_at' => $this->email_verified_at,
                        'email_verification_pending_at' => null,
                        // Temporary legacy compatibility.
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
    public function hasVerifiedPhone(): bool
    {
        return filled($this->phone)
            && $this->phone_verified_at !== null;
    }
    public function hasGoogleAccount(): bool
    {
        return filled($this->google_id);
    }
    public function hasFacebookAccount(): bool
    {
        return filled($this->facebook_id);
    }
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
            'can_resend_custom_verification' => $customPending,
            'disconnectable_sources' =>
                array_keys($sources),
        ];
    }
    public function connectedEmailIdentitySources(): array
    {
        return $this->resolvedEmailIdentityState()['disconnectable_sources'];
    }
    public function hasBackupLoginMethod(): bool
    {
        return ($this->hasEmailLogin() && $this->hasPassword())
            || $this->hasGoogleAccount()
            || $this->hasFacebookAccount();
    }
    public function needsBackupLoginMethod(): bool
    {
        return $this->hasVerifiedPhone()
            && ! $this->hasBackupLoginMethod();
    }
    public function needsVerifiedPhone(): bool
    {
        return ! $this->hasVerifiedPhone()
            && (
                $this->hasEmailLogin()
                || $this->hasGoogleAccount()
                || $this->hasFacebookAccount()
            );
    }
    public function needsSecuritySetup(): bool
    {
        return $this->needsBackupLoginMethod()
            || $this->needsVerifiedPhone();
    }
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
    public function markSecurityReminderAsShown(): void
    {
        $this->forceFill([
            'security_reminder_shown_at' => now(),
        ])->save();
    }
}