@extends('layouts.admin')
@section('title', 'Pesanan')
@section('page-title', 'Kelola Pesanan')

@section('content')
<div class="tabs-bar">
    <a href="/admin/orders" class="tab {{ !request('type') && !request('status') ? 'active' : '' }}">Semua ({{ $counts['all'] }})</a>
    <a href="/admin/orders?type=delivery" class="tab {{ request('type') === 'delivery' ? 'active' : '' }}">🚚 Delivery ({{ $counts['delivery'] }})</a>
    <a href="/admin/orders?type=dine_in" class="tab {{ request('type') === 'dine_in' ? 'active' : '' }}">🍽️ Dine-In ({{ $counts['dine_in'] }})</a>
    <a href="/admin/orders?status=pending" class="tab {{ request('status') === 'pending' ? 'active' : '' }}">⏳ Pending ({{ $counts['pending'] }})</a>
    <a href="/admin/orders?status=processed" class="tab {{ request('status') === 'processed' ? 'active' : '' }}">👨‍🍳 Proses ({{ $counts['processed'] }})</a>
</div>

<div class="orders-admin-list">
    @forelse($orders as $order)
    <div class="order-admin-card glass-card">
        <div class="order-admin-header">
            <div>
                <a href="/admin/orders/{{ $order->id }}" class="order-number link-primary">{{ $order->order_number }}</a>
                <span class="order-date">{{ $order->created_at->format('d M Y, H:i') }}</span>
            </div>
            <span class="badge {{ $order->isDelivery() ? 'badge-info' : 'badge-warning' }}">
                {{ $order->isDelivery() ? '🚚 Delivery' : '🍽️ Meja ' . $order->table_number }}
            </span>
        </div>
        <div class="order-admin-body">
            <div class="order-admin-customer">
                <strong>{{ $order->user->name }}</strong>
                <span class="text-muted">{{ $order->orderItems->count() }} item</span>
            </div>
            <div class="order-admin-items">
                @foreach($order->orderItems->take(2) as $item)
                <span>{{ $item->quantity }}x {{ $item->product->name ?? '-' }}</span>
                @endforeach
                @if($order->orderItems->count() > 2)
                <span class="text-muted">+{{ $order->orderItems->count() - 2 }} lainnya</span>
                @endif
            </div>
        </div>
        <div class="order-admin-footer">
            <span class="order-total">{{ $order->formatted_total }}</span>
            <div class="order-admin-actions">
                <span class="badge {{ $order->payment?->status_badge_class ?? 'badge-secondary' }}">
                    💳 {{ ucfirst($order->payment?->payment_status ?? 'N/A') }}
                </span>
                <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="form-input form-select-sm">
                        @foreach(['pending','processed','delivered','completed','canceled'] as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <span class="empty-icon">📦</span>
        <p>Tidak ada pesanan ditemukan.</p>
    </div>
    @endforelse
</div>
{{ $orders->appends(request()->query())->links() }}

{{-- Auto refresh --}}
<script>setTimeout(() => location.reload(), 30000);</script>
@endsection
