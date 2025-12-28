<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'totp_secret',
        'totp_enabled',
        'totp_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'totp_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'totp_verified_at' => 'datetime',
        'totp_enabled' => 'boolean',
        'password' => 'hashed',
    ];

    public function hasTotpEnabled(): bool
    {
        return $this->totp_enabled && !empty($this->totp_secret);
    }

    public function enableTotp(string $secret): void
    {
        $this->totp_secret = encrypt($secret);
        $this->totp_enabled = true;
        $this->totp_verified_at = now();
        $this->save();
    }

    public function disableTotp(): void
    {
        $this->totp_secret = null;
        $this->totp_enabled = false;
        $this->totp_verified_at = null;
        $this->save();
    }

    public function getTotpSecret(): ?string
    {
        return $this->totp_secret ? decrypt($this->totp_secret) : null;
    }
}
