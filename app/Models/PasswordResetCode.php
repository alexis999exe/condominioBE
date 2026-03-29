<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Verificar si el código ha expirado
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    /**
     * Marcar código como usado
     */
    public function markAsUsed(): void
    {
        $this->used = true;
        $this->save();
    }

    /**
     * Generar código aleatorio de 6 dígitos
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}