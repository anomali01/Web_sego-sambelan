@extends('layouts.app')
@section('title', 'Pembayaran - Sego Sambelan')

@section('content')
<div class="container py-2">
    <div class="payment-page">
        <div class="payment-card glass-card">
            <div class="payment-header">
                <span class="payment-icon">💳</span>
                <h1>Pembayaran</h1>
                <p>Order: <strong>{{ $order->order_number }}</strong></p>
            </div>

            <div class="checkout-items">
                @foreach($order->orderItems as $item)
                <div class="checkout-item">
                    <span class="checkout-item-qty">{{ $item->quantity }}x</span>
                    <span class="checkout-item-name">{{ $item->product->name }}</span>
                    <span class="checkout-item-price">{{ $item->formatted_subtotal }}</span>
                </div>
                @endforeach
            </div>

            <div class="summary-row total">
                <span>Total</span>
                <span class="total-price">{{ $order->formatted_total }}</span>
            </div>

            <button id="pay-button" class="btn btn-primary btn-full btn-lg">
                Bayar Sekarang 🔒
            </button>

            {{-- Pending payment modal --}}
            <div id="payment-pending-modal" class="modal-overlay" style="display:none;">
                <div class="modal-card glass-card">
                    <span class="modal-icon">⏳</span>
                    <h2>Selesaikan Pembayaran</h2>
                    <p>Anda menutup halaman pembayaran. Klik tombol di bawah untuk melanjutkan.</p>
                    <button onclick="payWithSnap()" class="btn btn-primary btn-full">Bayar Ulang</button>
                    <a href="/orders/{{ $order->id }}/tracking" class="btn btn-outline btn-full">Lihat Status Pesanan</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
<script>
function payWithSnap() {
    document.getElementById('payment-pending-modal').style.display = 'none';
    snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) {
            window.location.href = '/orders/{{ $order->id }}/tracking';
        },
        onPending: function(result) {
            window.location.href = '/orders/{{ $order->id }}/tracking';
        },
        onError: function(result) {
            alert('Pembayaran gagal. Silakan coba lagi.');
            window.location.href = '/orders/{{ $order->id }}/tracking';
        },
        onClose: function() {
            document.getElementById('payment-pending-modal').style.display = 'flex';
        }
    });
}
document.getElementById('pay-button').addEventListener('click', payWithSnap);
</script>
@endpush
@endsection
