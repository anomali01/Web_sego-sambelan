@extends('layouts.app')
@section('title', 'Dashboard Driver - Sego Sambelan')

@section('content')
<div class="container py-2">
    <h1 class="page-title">🛵 Tugas Pengantaran</h1>

    @if($orders->count() > 0)
    <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
        @foreach($orders as $order)
        <div class="card" style="padding: 1.5rem; border-radius: var(--radius); border-left: 5px solid {{ $order->status === 'delivering' ? '#3B82F6' : ($order->status === 'delivered' ? '#10B981' : '#F59E0B') }};">
            <div class="flex-between" style="margin-bottom: 1rem;">
                <span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                <span style="font-size: 0.875rem; color: var(--text-muted);">{{ $order->created_at->diffForHumans() }}</span>
            </div>
            <h3 style="margin-bottom: 0.25rem;">Pesanan #{{ $order->order_number }}</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
                📍 {{ Str::limit($order->delivery_address, 50) }}
            </p>
            <a href="{{ route('driver.orders.show', $order->id) }}" class="btn btn-primary btn-full">Lihat Detail & Navigasi</a>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <span class="empty-icon">🏖️</span>
        <h2>Tidak ada tugas saat ini</h2>
        <p>Bersantai sejenak, atau tunggu pesanan baru masuk.</p>
        <button onclick="location.reload()" class="btn btn-outline" style="margin-top: 1rem;">🔄 Refresh</button>
    </div>
    @endif
</div>
@endsection
