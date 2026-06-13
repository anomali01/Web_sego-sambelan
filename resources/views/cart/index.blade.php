@extends('layouts.app')
@section('title', 'Keranjang - Sego Sambelan')

@section('content')
<div class="container py-2">
    <h1 class="page-title">🛒 Keranjang Belanja</h1>

    @if(count($cart) > 0)
    <div class="cart-layout">
        <div class="cart-items">
            @foreach($cart as $productId => $item)
            <div class="cart-item glass-card" id="cart-item-{{ $productId }}">
                <div class="cart-item-image">
                    @if($item['image_url'])
                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
                    @else
                    <div class="product-image-placeholder small">🍛</div>
                    @endif
                </div>
                <div class="cart-item-info">
                    <h3>{{ $item['name'] }}</h3>
                    <p class="cart-item-price">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                </div>
                <div class="cart-item-actions">
                    <div class="qty-stepper">
                        <button class="qty-btn" onclick="updateQty({{ $productId }}, {{ $item['quantity'] - 1 }})">−</button>
                        <span class="qty-value" id="qty-{{ $productId }}">{{ $item['quantity'] }}</span>
                        <button class="qty-btn" onclick="updateQty({{ $productId }}, {{ $item['quantity'] + 1 }})">+</button>
                    </div>
                    <p class="cart-item-subtotal" id="subtotal-{{ $productId }}">
                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                    </p>
                    <form action="/cart/remove" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <input type="hidden" name="product_id" value="{{ $productId }}">
                        <button type="submit" class="btn-icon btn-danger-icon" title="Hapus">🗑️</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="cart-summary glass-card">
            <h2>Ringkasan Pesanan</h2>
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="cart-total-display">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <hr>
            <div class="summary-row total">
                <span>Total</span>
                <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <a href="/checkout" class="btn btn-primary btn-full btn-lg">Lanjut ke Checkout</a>
            <a href="/menu" class="btn btn-outline btn-full">← Lanjut Belanja</a>
        </div>
    </div>
    @else
    <div class="empty-state">
        <span class="empty-icon">🛒</span>
        <h2>Keranjang Anda kosong</h2>
        <p>Yuk, mulai pilih menu favoritmu!</p>
        <a href="/menu" class="btn btn-primary">Lihat Menu</a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function updateQty(productId, newQty) {
    if (newQty < 0) return;
    fetch('/cart/update', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ product_id: productId, quantity: newQty })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (newQty <= 0) {
                document.getElementById('cart-item-' + productId)?.remove();
            }
            location.reload();
        }
    });
}
</script>
@endpush
@endsection
