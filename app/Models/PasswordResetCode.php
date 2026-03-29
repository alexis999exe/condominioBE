<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'email',
        'code',
        'used',
        'expires_at',
    ];

    protected $casts = [
        'used'       => 'boolean',
        'expires_at' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────────

    /** Códigos aún no expirados y no usados */
    public function scopeValid($query)
    {
        return $query
            ->where('used', false)
            ->where('expires_at', '>', now());
    }

    /** Códigos de un email específico */
    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    // ── Helpers ─────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function isValid(): bool
    {
        return ! $this->isUsed() && ! $this->isExpired();
    }

    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }
}