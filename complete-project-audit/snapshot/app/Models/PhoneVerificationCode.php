<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneVerificationCode extends Model
{
    protected $fillable = [
        'phone',
        'purpose',
        'code_hash',
        'attempts',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}