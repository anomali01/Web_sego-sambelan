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

            {{-- Delivery Address --}}
            <div id="delivery-info" class="order-type-detail">
                <div class="address-display">
                    <h4>📍 Alamat Pengiriman</h4>
                    <p>{{ $profile->full_address ?? 'Alamat belum diisi' }}</p>
                    <a href="/profile/complete" class="link-primary">Ubah Alamat</a>
                </div>
            </div>

            {{-- Dine-In Table Number --}}
            <div id="dinein-info" class="order-type-detail" style="display:none;">
                <div class="form-group">
                    <label for="table_number">Nomor Meja</label>
                    <input type="text" id="table_number" name="table_number" value="{{ old('table_number') }}" placeholder="Contoh: 5" class="form-input @error('table_number') input-error @enderror">
                    @error('table_number')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- Order Summary --}}
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

        {{-- Notes --}}
        <div class="checkout-section glass-card">
            <div class="form-group">
                <label for="notes">Catatan Khusus (Opsional)</label>
                <textarea id="notes" name="notes" placeholder="Contoh: Sambalnya extra pedas, tanpa timun..." class="form-input form-textarea">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Total & Pay --}}
        <div class="checkout-section glass-card checkout-total">
            <div class="summary-row total">
                <span>Total Pembayaran</span>
                <span class="total-price">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">
                Bayar Sekarang 💳
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleOrderType() {
    const delivery = document.querySelector('input[value="delivery"]').checked;
    document.getElementById('delivery-info').style.display = delivery ? 'block' : 'none';
    document.getElementById('dinein-info').style.display = delivery ? 'none' : 'block';
}
document.addEventListener('DOMContentLoaded', toggleOrderType);
</script>
@endpush
@endsection
