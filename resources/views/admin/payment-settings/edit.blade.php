@extends('layouts.admin')
@section('title', 'Pembayaran Manual')
@section('page-title', 'Pembayaran Transfer Manual')

@section('content')
<div class="card glass-card" style="max-width:640px;">
    <p style="color:var(--text-muted); margin-bottom:1.25rem;">
        Data ini ditampilkan ke pembeli yang memilih <strong>Transfer Manual</strong> saat checkout.
    </p>

    <form method="POST" action="/admin/payment-settings" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="manual_enabled" value="1" {{ old('manual_enabled', $settings->manual_enabled) ? 'checked' : '' }}>
                Aktifkan pembayaran transfer manual
            </label>
        </div>

        <div class="form-group">
            <label for="bank_name">Nama Bank</label>
            <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $settings->bank_name) }}" required class="form-input @error('bank_name') input-error @enderror">
            @error('bank_name')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="account_number">Nomor Rekening</label>
            <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $settings->account_number) }}" required class="form-input @error('account_number') input-error @enderror">
            @error('account_number')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="account_name">Atas Nama</label>
            <input type="text" id="account_name" name="account_name" value="{{ old('account_name', $settings->account_name) }}" required class="form-input @error('account_name') input-error @enderror">
            @error('account_name')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="instructions">Instruksi tambahan (opsional)</label>
            <textarea id="instructions" name="instructions" class="form-input form-textarea" rows="3">{{ old('instructions', $settings->instructions) }}</textarea>
        </div>

        <div class="form-group">
            <label for="qris_image">Gambar QRIS warung (opsional)</label>
            @if($settings->qris_url)
            <div style="margin-bottom:.75rem;">
                <img src="{{ $settings->qris_url }}" alt="QRIS" style="max-width:180px; border-radius:8px;">
            </div>
            @endif
            <input type="file" id="qris_image" name="qris_image" accept="image/*" class="form-input">
            @error('qris_image')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </form>
</div>
@endsection
