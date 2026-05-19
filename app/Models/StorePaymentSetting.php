<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StorePaymentSetting extends Model
{
    protected $fillable = [
        'manual_enabled',
        'bank_name',
        'account_number',
        'account_name',
        'qris_image',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'manual_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'manual_enabled' => true,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Sego Sambelan',
            'instructions' => 'Transfer sesuai total pesanan. Cantumkan nomor order di berita transfer.',
        ]);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->bank_name)
            && ! empty($this->account_number)
            && ! empty($this->account_name);
    }

    public function getQrisUrlAttribute(): ?string
    {
        if (! $this->qris_image) {
            return null;
        }

        return Storage::disk('public')->url($this->qris_image);
    }
}
