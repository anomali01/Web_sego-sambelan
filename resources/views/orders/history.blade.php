@extends('layouts.app')
@section('title', 'Riwayat Pesanan - Sego Sambelan')

@section('content')
<div class="container py-2">
    <h1 class="page-title">📋 Riwayat Pesanan</h1>

    @if($orders->count() > 0)
    <div class="orders-list">
        @foreach($orders as $order)
        <div class="order-history-card glass-card">
            <div class="order-history-header">
                <div>
                    <span class="order-number">{{ $order->order_number }}</span>
                    <span class="order-date">{{ $order->created_at->format('d M Y, H:i') }}</span>
                </div>
                <span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="order-history-items">
                @foreach($order->orderItems->take(3) as $item)
                <span>{{ $item->quantity }}x {{ $item->product->name ?? 'Menu' }}</span>
                @endforeach
                @if($order->orderItems->count() > 3)
                <span class="text-muted">+{{ $order->orderItems->count() - 3 }} item lainnya</span>
                @endif
            </div>
            <div class="order-history-footer">
                <span class="order-total">{{ $order->formatted_total }}</span>
                <a href="/orders/{{ $order->id }}/tracking" class="btn btn-sm btn-outline">Lihat Detail</a>
            </div>
        </div>
        @endforeach
    </div>
    {{ $orders->links() }}
    @else
    <div class="empty-state">
        <span class="empty-icon">📋</span>
        <h2>Belum ada pesanan</h2>
        <p>Pesan menu favorit Anda sekarang!</p>
        <a href="/menu" class="btn btn-primary">Lihat Menu</a>
    </div>
    @endif
</div>
@endsection
