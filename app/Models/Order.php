<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'order_type',
        'table_number',
        'total_price',
        'status',
        'delivery_address',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // ── Helpers ────────────────────────────────────

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function isDelivery(): bool
    {
        return $this->order_type === 'delivery';
    }

    public function isDineIn(): bool
    {
        return $this->order_type === 'dine_in';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'badge-warning',
            'processed' => 'badge-info',
            'delivered' => 'badge-primary',
            'completed' => 'badge-success',
            'canceled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public static function generateOrderNumber(): string
    {
        return 'SGS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
