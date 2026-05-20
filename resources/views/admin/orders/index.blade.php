@extends('layouts.admin')
@section('title', 'Pesanan')
@section('page-title', 'Kelola Pesanan')

@section('content')
<div class="tabs-bar">
    <a href="/admin/orders" class="tab {{ !request('status') ? 'active' : '' }}">Semua ({{ $counts['all'] }})</a>
    <a href="/admin/orders?status=pending" class="tab {{ request('status') === 'pending' ? 'active' : '' }}">⏳ Pending ({{ $counts['pending'] }})</a>
    <a href="/admin/orders?status=processed" class="tab {{ request('status') === 'processed' ? 'active' : '' }}">👨‍🍳 Diproses ({{ $counts['processed'] }})</a>
    <a href="/admin/orders?status=delivered" class="tab {{ request('status') === 'delivered' ? 'active' : '' }}">🚚 Dikirim ({{ $counts['delivered'] }})</a>
    <a href="/admin/orders?status=completed" class="tab {{ request('status') === 'completed' ? 'active' : '' }}">✅ Riwayat Pesanan ({{ $counts['completed'] }})</a>
    <a href="/admin/orders?status=canceled" class="tab {{ request('status') === 'canceled' ? 'active' : '' }}">❌ Riwayat Pembatalan ({{ $counts['canceled'] }})</a>
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
            <div class="order-admin-actions" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <span class="badge {{ $order->payment?->status_badge_class ?? 'badge-secondary' }}">
                    💳 {{ $order->payment?->isManual() ? 'Transfer Manual' : 'Midtrans' }}: {{ ucfirst($order->payment?->payment_status ?? 'N/A') }}
                </span>

                <div class="action-buttons-group" style="display: flex; gap: 0.35rem; align-items: center;">
                    @if($order->status === 'pending')
                        @if($order->payment?->isManual())
                            @if($order->payment->isPending())
                                <form action="/admin/orders/{{ $order->id }}/confirm-payment" method="POST" class="inline" onsubmit="return confirm('Konfirmasi bahwa pembayaran sudah masuk ke rekening?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">① Verifikasi Bayar</button>
                                </form>
                            @else
                                <form action="/admin/orders/{{ $order->id }}/start-processing" method="POST" class="inline" onsubmit="return confirm('Mulai menyiapkan pesanan ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">② Mulai Masak</button>
                                </form>
                            @endif
                        @else
                            @if($order->payment?->isPaid())
                                <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="processed">
                                    <button type="submit" class="btn btn-sm btn-success">👨‍🍳 Mulai Masak</button>
                                </form>
                            @else
                                <span class="text-muted" style="font-size: 0.85rem;">⏳ Menunggu Pembayaran</span>
                            @endif
                        @endif

                        <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Batalkan pesanan ini?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="canceled">
                            <button type="submit" class="btn btn-sm btn-danger">❌ Batalkan</button>
                        </form>

                    @elseif($order->status === 'processed')
                        @if($order->isDelivery())
                            <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Kirim pesanan ini?')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="btn btn-sm btn-info">🚚 Kirim Pesanan</button>
                            </form>
                        @else
                            <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Sajikan pesanan dan selesaikan transaksi?')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-sm btn-success">🍽️ Sajikan & Selesai</button>
                            </form>
                        @endif

                        <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Batalkan pesanan ini?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="canceled">
                            <button type="submit" class="btn btn-sm btn-danger">❌ Batalkan</button>
                        </form>

                    @elseif($order->status === 'delivered')
                        <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Tandai pesanan ini sudah selesai diterima?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-sm btn-success">✅ Selesai Diterima</button>
                        </form>

                        <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="inline" onsubmit="return confirm('Batalkan pesanan ini?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="canceled">
                            <button type="submit" class="btn btn-sm btn-danger">❌ Batalkan</button>
                        </form>

                    @elseif($order->status === 'completed')
                        <span class="badge badge-success">✅ Selesai (Riwayat)</span>
                    @elseif($order->status === 'canceled')
                        <span class="badge badge-danger">❌ Batal (Riwayat)</span>
                    @endif
                </div>
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
