@extends('layouts.app')
@section('title', 'Checkout - Sego Sambelan')

@section('content')
<div class="container py-2">
    <h1 class="page-title">Checkout</h1>

    <form method="POST" action="/checkout/place-order" class="checkout-layout">
        @csrf

        {{-- Order Type Selection --}}
        <div class="checkout-section glass-card">
            <h2>Pilih Tipe Pesanan</h2>
            <div class="order-type-cards">
                <label class="order-type-card" id="delivery-card">
                    <input type="radio" name="order_type" value="delivery" {{ old('order_type', 'delivery') === 'delivery' ? 'checked' : '' }} onchange="toggleOrderType()">
                    <div class="order-type-content">
                        <span class="order-type-icon">🚚</span>
                        <h3>Delivery</h3>
                        <p>Kirim ke alamat Anda</p>
                    </div>
                </label>
                <label class="order-type-card" id="dinein-card">
                    <input type="radio" name="order_type" value="dine_in" {{ old('order_type') === 'dine_in' ? 'checked' : '' }} onchange="toggleOrderType()">
                    <div class="order-type-content">
                        <span class="order-type-icon">🍽️</span>
                        <h3>Dine-In</h3>
                        <p>Makan di tempat</p>
                    </div>
                </label>
            </div>
            @error('order_type')<span class="form-error">{{ $message }}</span>@enderror

            <div id="delivery-info" class="order-type-detail">
                <div class="address-display">
                    <h4>📍 Alamat Pengiriman</h4>
                    <p>{{ $profile->full_address ?? 'Alamat belum diisi' }}</p>
                    <a href="/profile/complete" class="link-primary">Ubah Alamat</a>
                </div>
            </div>

            <div id="dinein-info" class="order-type-detail" style="display:none;">
                <div class="form-group">
                    <label for="table_number">Nomor Meja</label>
                    <input type="text" id="table_number" name="table_number" value="{{ old('table_number') }}" placeholder="Contoh: 5" class="form-input @error('table_number') input-error @enderror">
                    @error('table_number')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="checkout-section glass-card">
            <h2>Ringkasan Pesanan</h2>
            <div class="checkout-items">
                @foreach($cart as $productId => $item)
                <div class="checkout-item">
                    <span class="checkout-item-qty">{{ $item['quantity'] }}x</span>
                    <span class="checkout-item-name">{{ $item['name'] }}</span>
                    <span class="checkout-item-price">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            <a href="/cart" class="link-primary">Edit Keranjang</a>
        </div>

        <div class="checkout-section glass-card">
            <h2>Metode Pembayaran</h2>
            <div class="order-type-cards">
                <label class="order-type-card">
                    <input type="radio" name="payment_channel" value="midtrans" {{ old('payment_channel', 'midtrans') === 'midtrans' ? 'checked' : '' }} onchange="updatePayButton()">
                    <div class="order-type-content">
                        <span class="order-type-icon">💳</span>
                        <h3>Midtrans</h3>
                        <p>GoPay, VA, QRIS, kartu (otomatis)</p>
                    </div>
                </label>
                @if($paymentSettings->manual_enabled && $paymentSettings->isConfigured())
                <label class="order-type-card">
                    <input type="radio" name="payment_channel" value="manual" {{ old('payment_channel') === 'manual' ? 'checked' : '' }} onchange="updatePayButton()">
                    <div class="order-type-content">
                        <span class="order-type-icon">🏦</span>
                        <h3>Transfer Manual</h3>
                        <p>Ke rekening / QRIS warung</p>
                    </div>
                </label>
                @endif
            </div>
            @error('payment_channel')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="checkout-section glass-card">
            <div class="form-group">
                <label for="notes">Catatan Khusus (Opsional)</label>
                <textarea id="notes" name="notes" placeholder="Contoh: Sambalnya extra pedas, tanpa timun..." class="form-input form-textarea">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="checkout-section glass-card checkout-total">
            <div class="summary-row total">
                <span>Total Pembayaran</span>
                <span class="total-price">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <button type="submit" id="pay-submit-btn" class="btn btn-primary btn-full btn-lg">
                Bayar Sekarang 💳
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleOrderType() {
    const delivery = document.querySelector('input[name="order_type"][value="delivery"]').checked;
    document.getElementById('delivery-info').style.display = delivery ? 'block' : 'none';
    document.getElementById('dinein-info').style.display = delivery ? 'none' : 'block';
}
function updatePayButton() {
    const manual = document.querySelector('input[name="payment_channel"][value="manual"]');
    const btn = document.getElementById('pay-submit-btn');
    if (manual && manual.checked) {
        btn.textContent = 'Lanjut ke Instruksi Transfer 🏦';
    } else {
        btn.textContent = 'Bayar Sekarang 💳';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    toggleOrderType();
    updatePayButton();
});
</script>
@endpush
@endsection
