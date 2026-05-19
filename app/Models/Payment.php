<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_status',
        'amount',
        'snap_token',
        'proof_path',
        'sender_name',
        'transaction_id',
        'gateway_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ── Helpers ────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isMidtrans(): bool
    {
        return $this->payment_method === 'midtrans';
    }

    public function isManual(): bool
    {
        return $this->payment_method === 'manual';
    }

    public function hasProof(): bool
    {
        return ! empty($this->proof_path);
    }

    public function getProofUrlAttribute(): ?string
    {
        if (! $this->proof_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->proof_path);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'badge-success',
            'pending' => 'badge-warning',
            'failed' => 'badge-danger',
            'refunded' => 'badge-info',
            'expired' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
}
