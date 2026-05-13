@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card glass-card">
        <div class="stat-icon">🍽️</div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['total_products'] }}</span>
            <span class="stat-label">Total Menu</span>
        </div>
    </div>
    <div class="stat-card glass-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['active_products'] }}</span>
            <span class="stat-label">Menu Aktif</span>
        </div>
    </div>
    <div class="stat-card glass-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['today_orders'] }}</span>
            <span class="stat-label">Pesanan Hari Ini</span>
        </div>
    </div>
    <div class="stat-card glass-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</span>
            <span class="stat-label">Pendapatan Hari Ini</span>
        </div>
    </div>
</div>

@if($stats['pending_orders'] > 0)
<div class="alert alert-warning">
    ⚠️ Ada <strong>{{ $stats['pending_orders'] }}</strong> pesanan yang menunggu diproses!
    <a href="/admin/orders?status=pending" class="link-primary">Lihat Sekarang →</a>
</div>
@endif

<div class="card glass-card">
    <h2 class="card-title">Pesanan Terbaru</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Tipe</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                <tr>
                    <td><a href="/admin/orders/{{ $order->id }}" class="link-primary">{{ $order->order_number }}</a></td>
                    <td>{{ $order->user->name }}</td>
                    <td><span class="badge {{ $order->isDelivery() ? 'badge-info' : 'badge-warning' }}">{{ $order->isDelivery() ? 'Delivery' : 'Dine-In' }}</span></td>
                    <td>{{ $order->formatted_total }}</td>
                    <td><span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span></td>
                    <td>{{ $order->created_at->format('H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
