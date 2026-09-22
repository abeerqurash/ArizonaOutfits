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
     * Customer has an email address.
     */
    public function hasEmailLogin(): bool
    {
        return filled($this->email);
    }

    /**
     * Customer has a verified email login method.
     */
    public function hasVerifiedEmail(): bool
    {
        return filled($this->email)
            && $this->email_verified_at !== null;
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
     * Phone-created accounts need at least one
     * additional recovery/login method.
     */
    public function hasBackupLoginMethod(): bool
    {
        return $this->hasEmailLogin()
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
