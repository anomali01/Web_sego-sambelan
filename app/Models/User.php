<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relationships ──────────────────────────────

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ── Helpers ────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSeller(): bool
    {
        return in_array($this->role, ['admin', 'seller']);
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'driver_id');
    }

    public function hasCompleteProfile(): bool
    {
        $profile = $this->profile;

        if (!$profile) {
            return false;
        }

        return !empty($profile->phone)
            && !empty($profile->street_address)
            && !empty($profile->city)
            && !empty($profile->province)
            && !empty($profile->postal_code);
    }
}
